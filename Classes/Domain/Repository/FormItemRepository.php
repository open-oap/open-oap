<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Repository;

/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * The repository for FormItems
 */
class FormItemRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
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

    /**
     * Find formPage by pid
     *
     * @param int $pid
     */
    public function findAllByPid(int $pid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(false);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->setOrderings(['question' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING]);
        $query->matching($query->equals('pid', $pid));
        $result = $query->execute();

        return $result;
    }
}
