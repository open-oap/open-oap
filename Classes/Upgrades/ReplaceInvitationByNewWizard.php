<?php
declare(strict_types=1);

namespace OpenOAP\OpenOap\Upgrades;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Install\Updates\ConfirmableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use DOMDocument;
use DOMXPath;

class ReplaceInvitationByNewWizard implements UpgradeWizardInterface
{
    const EXT_KEY = 'open_oap';
    const CTYPE_REPLACE_INVITATION = 'femanager_invitation';

    public function getTitle(): string
    {
        return 'Migrate femanager_invitation to femanager_registration';
    }

    public function getDescription(): string
    {
        return 'Replaces the <sheet index="invitation"> block in FlexForm XML fields and '
            . 'changes CType from femanager_invitation to femanager_registration.';
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws DBALException
     */
    public function executeUpdate(): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        // Alle relevanten Datensätze holen
        $queryBuilder = $connection->createQueryBuilder();
        $rows = $queryBuilder
            ->select('uid', 'pi_flexform', 'CType')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter(self::CTYPE_REPLACE_INVITATION, \PDO::PARAM_STR)),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($rows as $row) {
            $updatedXml = $this->replaceInvitationSheet($row['pi_flexform']);

            $updateData = [
                'CType' => 'femanager_registration',
                'pi_flexform' => $updatedXml,
            ];

            $connection->update(
                'tt_content',
                $updateData,
                ['uid' => (int)$row['uid']]
            );
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        $queryBuilder = $connection->createQueryBuilder();
        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter(self::CTYPE_REPLACE_INVITATION, \PDO::PARAM_STR)),
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    protected function replaceInvitationSheet(string $xmlString): string
    {
        $newSheetXml = <<<XML
<sheet index="new">
    <language index="lDEF">
        <field index="settings.new.fields">
            <value index="vDEF">email,password,terms</value>
        </field>
        <field index="settings.new.overrideUserGroup">
            <value index="vDEF">1</value>
        </field>
        <field index="settings.new.confirmByUser">
            <value index="vDEF">1</value>
        </field>
        <field index="settings.new.confirmByAdmin">
            <value index="vDEF"></value>
        </field>
        <field index="settings.new.notifyAdmin">
            <value index="vDEF"></value>
        </field>
    </language>
</sheet>
XML;

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        if (!@$dom->loadXML($xmlString)) {
            return $xmlString; // ungültiges XML -> keine Änderung
        }

        $xpath = new DOMXPath($dom);
        $invitationNodes = $xpath->query('//sheet[@index="invitation"]');
        if ($invitationNodes->length > 0) {
            $invitationNode = $invitationNodes->item(0);
            $newSheetNode = $dom->createDocumentFragment();
            $newSheetNode->appendXML($newSheetXml);
            $invitationNode->parentNode->replaceChild($newSheetNode, $invitationNode);
            return $dom->saveXML();
        }

        return $xmlString;
    }
}
