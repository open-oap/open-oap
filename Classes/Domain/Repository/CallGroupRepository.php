<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Repository;

use OpenOAP\OpenOap\Domain\Model\CallGroup;

class CallGroupRepository extends OapAbstractRepository
{
    public function findAllCallGroups(): array
    {
        $callGroups = [];
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->setOrderings(['sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING]);
        $rows = $query->execute();

        /** @var CallGroup $row */
        foreach ($rows as $row) {
            $uid = $row->getUid();

            $callGroups[$uid] = [
                'title' => $row->getTitle(),
                'description' => $row->getDescription(),
                'country_giz' => $row->getCountryGiz(),
                'country_deg' => $row->getCountryDeg(),
                'default_giz' => $row->getDefaultGiz(),
                'default_deg' => $row->getDefaultDeg(),
                'blocked_languages' => $row->getBlockedLanguages(),
            ];
        }

        return $callGroups;
    }
}
