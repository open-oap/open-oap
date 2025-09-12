<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Repository;

/**
 * This file is part of the "Open Application Platform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * The repository for FormItems
 */
class FormItemRepository extends OapAbstractRepository
{
    /**
     * Find FormItems enabled for filtering
     *
     * @param int $pid
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findByEnabledFilter(int $pid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectStoragePage(true);
        $query->getQuerySettings()->setStoragePageIds([$pid]);
        $query->matching($query->equals('enabled_filter', 1));
        $result = $query->execute();
        return $result;
    }

}
