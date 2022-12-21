<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

use JsonSerializable;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * Answer
 */
class MetaInformation implements JsonSerializable
{
    /**
     * @var string
     */
    protected string $info = '';

    /**
     * @var array
     */
    protected array $filter = [];

    /**
     * @var array
     */
    protected array $pages = [];

    /**
     * @var array
     */
    protected array $groupsCounter = [];

    /**
     * @var int
     */
    protected int $lastPage = 0;

    /**
     * @var int
     */
    protected int $limitEditableFields = 0;

    public function __construct(?string $json = null)
    {
        if (!$json) {
            return $this;
        }

        // create/fill attributes by JSON data
        /**
         * array:
         * ["filter" => [99 => 'Test 1', 100 => 'Test 2'], "info" => [101 => 'Deutschland']]
         *
         * json:
         * {"filter":{"99":"Test 1","100":"Test 2"},"info":{"101":"Deutschland"}}
         */
        // $json = '{"filter":{"99":"Test 1","100":"Test 2"},"info":{"101":"Deutschland"}}';
        $jsonArray = json_decode($json, true);

        $this->info = $jsonArray['info'];

        $filterArray = $jsonArray['filter'];
        if (is_array($filterArray)) {
            $this->filter = $filterArray;
        }

        $pagesArray = $jsonArray['pages'];
        if (is_array($pagesArray)) {
            $this->pages = $pagesArray;
        }

        $groupsArray = $jsonArray['groupsCounter'];
        if (is_array($groupsArray)) {
            $this->groupsCounter = $groupsArray;
        }

        $this->lastPage = (isset($jsonArray['lastPage'])) ? (integer)$jsonArray['lastPage'] : 1;

        $this->limitEditableFields = (isset($jsonArray['limitEditableFields'])) ? (integer)$jsonArray['limitEditableFields'] : 0;

        return $this;
    }

    /**
     * @param int $page
     */
    public function addPage(int $pageNumber, FormPage $page)
    {
        // cause this is a fix - we have changed the schema of this array to pageNo: pageUid for more flexibility
        if ($this->pages[$pageNumber]) {
            unset($this->pages[$pageNumber]);
        }
        $this->pages[$pageNumber] = $page->getUid();
        asort($this->pages);
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return json_encode([
            'filter' => $this->filter,
            'info'   => $this->info,
            'pages'   => $this->pages,
            'lastPage'   => $this->lastPage,
            'groupsCounter' => $this->groupsCounter,
            'limitEditableFields' => $this->limitEditableFields,
        ]);
    }

    public function jsonEncode()
    {
        return json_encode($this->jsonSerialize(), JSON_FORCE_OBJECT);
    }

    /**
     * @return string
     */
    public function getInfo(): string
    {
        return $this->info;
    }

    /**
     * @param string $info
     */
    public function setInfo(string $info): void
    {
        $this->info = $info;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     */
    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @return array
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * @param array $pages
     */
    public function setPages(array $pages): void
    {
        $this->pages = $pages;
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    /**
     * @param int $lastPage
     */
    public function setLastPage(int $lastPage): void
    {
        $this->lastPage = $lastPage;
    }

    /**
     * @return int
     */
    public function getLimitEditableFields(): int
    {
        return $this->limitEditableFields;
    }

    /**
     * @param int $limitEditableFields
     */
    public function setLimitEditableFields(int $limitEditableFields): void
    {
        $this->limitEditableFields = $limitEditableFields;
    }

    /**
     * @return array
     */
    public function getGroupsCounter(): array
    {
        return $this->groupsCounter;
    }

    /**
     * @param array $groupsCounter
     */
    public function setGroupsCounter(array $groupsCounter): void
    {
        $this->groupsCounter = $groupsCounter;
    }

    /**
     * @return array
     */
    public function countGroups(array $groupCounter): array
    {
        foreach ($this->groupsCounter as $groupUidL0 => $groupDataL0) {
            if (!$groupCounter[$groupUidL0]) {
                $groupCounter[$groupUidL0]['min'] = 1;
                $groupCounter[$groupUidL0]['max'] = $groupDataL0['current'];
            }
            if ($groupCounter[$groupUidL0]['min'] > $groupDataL0['current']) {
                $groupCounter[$groupUidL0]['min'] = $groupDataL0['current'];
            }
            if ($groupCounter[$groupUidL0]['max'] < $groupDataL0['current']) {
                $groupCounter[$groupUidL0]['max'] = $groupDataL0['current'];
            }

//            DebuggerUtility::var_dump($groupDataL0,(string) $groupUidL0);
//            $this->calculateGroupCounter($groupData, $groupUidL0, $groupDataL0['current']);
//            DebuggerUtility::var_dump($groupData,(string) $groupUidL0);

            foreach ($groupDataL0['instances'] as $indexL1 => $nestedGroups) {
                foreach ($nestedGroups as $groupUidL1 => $groupDataL1) {
                    if (!$groupCounter[$groupUidL0]['instances'][$indexL1][$groupUidL1]) {
                        $groupCounter[$groupUidL0]['instances'][$indexL1][$groupUidL1]['min'] = 1;
                        $groupCounter[$groupUidL0]['instances'][$indexL1][$groupUidL1]['max'] = $groupDataL1['current'];
                    }
                    if ($groupCounter[$groupUidL0]['instances'][$indexL1][$groupUidL1]['min'] > $groupDataL1['current']) {
                        $groupCounter[$groupUidL0]['instances'][$indexL1][$groupUidL1]['min'] = $groupDataL1['current'];
                    }
                    if ($groupCounter[$groupUidL0]['instances'][$indexL1][$groupUidL1]['max'] < $groupDataL1['current']) {
                        $groupCounter[$groupUidL0]['instances'][$indexL1][$groupUidL1]['max'] = $groupDataL1['current'];
                    }
//                    $this->calculateGroupCounter($groupData, $groupUidL1, $groupDataL1['current']);
                }
            }
        }
        return $groupCounter;
    }

    /**
     * @param array $groupCounter
     * @param $groupId
     * @param $current
     */
    protected function calculateGroupCounter($groupCounter, $groupId, $current): array
    {
        $groupCounter = [];
        if (!$groupCounter) {
            $groupCounter['min'] = 1;
            $groupCounter['max'] = $current;
        }
        if ($groupCounter['min'] > $current) {
            $groupCounter['min'] = $current;
        }
        if ($groupCounter['max'] < $current) {
            $groupCounter['max'] = $current;
        }
    }
}
