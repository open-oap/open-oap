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
 * The repository for Calls
 */
class CallRepository extends OapAbstractRepository
{
    /**
     * Find Calls/Forms by pid
     *
     * @param int $pid
     */
    public function findAllByPid(int $pid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->setOrderings(['title' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING]);
        $query->matching($query->equals('pid', $pid));
        $result = $query->execute();
        return $result;
    }

    /**
     * Find active Calls/Forms by pid and applicant
     *
     * @param Applicant|null $applicant
     * @param int $testerFeGroupsId
     * @param int $pid
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findActiveCalls(int $pid, $applicant=null, $testerFeGroupsId=0)
    {
        if ($applicant == null || $applicant->getUsergroup() == null) {
            return [];
        }
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->setOrderings([
            'callEndTime' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
            'title' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING, ]);

        $now = new \DateTime();
        $nowFormatted = $now->format('Y-m-d H:i:s');
        $includeDateSetting = true;

        $constraints = [];
        $groupConstraints = [];
        $usergroupConstraints = [];
        $stdUsergroupConstraints = [];
        $testUsergroupConstraints = [];

        // pid
        $constraints[] = $query->equals('pid', $pid);
        $constraints[] = $query->equals('anonym', 0);

        // usergroup / callStartTime/callEndTime
        foreach ($applicant->getUsergroup() as $applicantGroup) {
            if ($applicantGroup->getUid() == (int)$testerFeGroupsId) {
                $testUsergroupConstraints[] = $query->contains('usergroup', $applicantGroup->getUid());
            } else {
                $stdUsergroupConstraints[] = $query->contains('usergroup', $applicantGroup->getUid());
            }
            if (count($applicantGroup->getSubgroup()) > 0) {
                foreach ($applicantGroup->getSubgroup() as $subgroup) {
                    if ($subgroup->getUid() == (int)$testerFeGroupsId) {
                        $testUsergroupConstraints[] = $query->contains('usergroup', $applicantGroup->getUid());
                    }
                }
            }
        }

        $countTestUsergroupConstraints = count($testUsergroupConstraints);
        if ($countTestUsergroupConstraints === 1) {
            $groupConstraints[] = $testUsergroupConstraints;
        } elseif ($countTestUsergroupConstraints >= 2) {
            $groupConstraints[] = $query->logicalOr(...$testUsergroupConstraints);
        }

        $countStdUsergroupConstraints = count($stdUsergroupConstraints);
        if ($countStdUsergroupConstraints > 0) {
            if ($countStdUsergroupConstraints === 1) {
                $usergroupConstraints[] = $stdUsergroupConstraints;
            } elseif ($countStdUsergroupConstraints >= 2) {
                $usergroupConstraints[] = $query->logicalOr(...$stdUsergroupConstraints);
            }
            $usergroupConstraints[] = $query->logicalOr(
                $query->lessThan('callStartTime', $nowFormatted),
                $query->equals('callStartTime', null),
            );
            $usergroupConstraints[] = $query->logicalOr(
                $query->equals('callEndTime', null),
                $query->greaterThan('callEndTime', $nowFormatted),
            );
            $groupConstraints[] = $query->logicalAnd(...$usergroupConstraints);
        }

        $countGroupConstraints = count($groupConstraints);
        if ($countGroupConstraints === 1) {
            $constraints[] = reset($groupConstraints);
        } elseif ($countGroupConstraints >= 2) {
            $constraints[] = $query->logicalOr(...$groupConstraints);
        } else {
            // no usergroup matched
            return [];
        }

        $query->matching($query->logicalAnd(...$constraints));
        //        $this->sqlDebug($query);
        $result = $query->execute();
        return $result;
    }
}
