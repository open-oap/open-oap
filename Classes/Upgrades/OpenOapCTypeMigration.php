<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Upgrades;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('openOapCTypeMigration')]
final class OpenOapCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    public function getTitle(): string
    {
        return 'Migrate "open oap" plugins to content elements.';
    }

    public function getDescription(): string
    {
        return 'The "open oap" plugins are now registered as content element. Update migrates existing records and backend user permissions.';
    }

    /**
     * This must return an array containing the "list_type" to "CType" mapping
     *
     *  Example:
     *
     *  [
     *      'pi_plugin1' => 'pi_plugin1',
     *      'pi_plugin2' => 'new_content_element',
     *  ]
     *
     * @return array<string, string>
     */
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'openoap_applicant' => 'openoap_applicant',
            'openoap_applicantform' => 'openoap_applicantform',
            'openoap_form' => 'openoap_form',
            'openoap_notifications' => 'openoap_notifications',
            'openoap_proposals' => 'openoap_proposals',
            'openoap_dashboard' => 'openoap_dashboard',
        ];
    }
}
