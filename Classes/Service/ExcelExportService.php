<?php

namespace OpenOAP\OpenOap\Service;

use OpenOAP\OpenOap\Controller\OapBaseController;
use OpenOAP\OpenOap\Domain\Model\Answer;
use OpenOAP\OpenOap\Domain\Model\FormGroup;
use OpenOAP\OpenOap\Domain\Model\FormItem;
use OpenOAP\OpenOap\Domain\Model\FormPage;
use OpenOAP\OpenOap\Domain\Model\GroupTitle;
use OpenOAP\OpenOap\Domain\Model\MetaInformation;
use OpenOAP\OpenOap\Domain\Model\Proposal;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

//class ExcelExportService implements SingletonInterface
class ExcelExportService extends OapBaseController
{

    /**
     * @var QueryResultInterface
     */
    protected QueryResultInterface $proposals;
    /**
     * @var array<string>
     */
    protected array $states = [];

    private const LEVEL_0_HEADER_ROW = 1;
    private const LEVEL_1_HEADER_ROW = 2;
    private const ITEM_HEADER_ROW = 3;

//    protected array $settings = [];

//    protected ResourceFactory $resourceFactory;


    public function setProposals(QueryResultInterface $proposals):void {
        $this->proposals = $proposals;
    }

    public function setStates(array $states):void {
        $this->states = $states;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function setResourceFactory(ResourceFactory $resourceFactory): void
    {
        $this->resourceFactory = $resourceFactory;
    }

    public function createSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $l0headRow = 1;
        $l1headRow = 2;
        $itemHeadRow = 3;
        $columnNo = 0;

        $head = [];
        $head[$l0headRow] = [];
        $head[$l1headRow] = [];
        $head[$itemHeadRow] = [];

        $head[$itemHeadRow][$columnNo++] = 'ID (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Applicant-E-Mail (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Applicant-ID (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Signature (intern)';
        $head[$itemHeadRow][$columnNo++] = 'State (intern)';
        $head[$itemHeadRow][$columnNo++] = 'Submitted';
        $head[$itemHeadRow][$columnNo++] = 'Last Changed';

        $colType = [];

        // calculate group repeats
        $groupsCounter = [];
        /** @var Proposal $proposal */
        foreach ($this->proposals as $proposal) {
            $metaInfo = new MetaInformation($proposal->getMetaInformation());
            $groupData = $metaInfo->getGroupsCounter();
            $groupsCounter = $metaInfo->countGroups($groupsCounter);
        }

        $columns = [];
        list($head, $colType, $columns) = $this->createDataStructure($groupsCounter, $head, $l0headRow, $columnNo, $l1headRow, $colType, $columns, $itemHeadRow);

        // translations of state const for export
        $states = $this->createStatesArray('selected', $proposal->getCall());
        $export = $this->collectData($columns, $states);

        // ** build Group-Row-Titles
        // Set sheet cols and rows
        // Set first row with group headers
        $this->createGroupTitleRows($l0headRow, $l1headRow, $head, $itemHeadRow, $sheet, $columns);

        // Set the second row with col headers
        $this->createItemTitleRow($head, $sheet, $colType);

        // Add data rows
        $this->createDataRows($itemHeadRow, $export, $head[$itemHeadRow], $sheet);

        return $spreadsheet;
    }

    /**
     * @param FormGroup $group
     * @param int $repeatableMax
     * @param int $index
     * @return string
     */
    public function createGroupTitle(FormGroup $group, int $repeatableMax, int $index): string
    {
        $postfix = '';
        if ($repeatableMax > 1) {
            $countGroupTitles = count($group->getGroupTitle());
            if ($countGroupTitles > 0 and $index <= $countGroupTitles) {
                /** @var GroupTitle $groupTitle */
                $groupTitle = $group->getGroupTitle()[$index];
                $postfix = ' - ' . $groupTitle->getTitle();
            } else {
                $postfix = ' #' . (int)($index + 1);
            }
        }
        return $group->getTitle() . $postfix;
    }

    /**
     * @param Proposal $proposal
     * @return string
     */
    protected function buildSignature(Proposal $proposal): string
    {
        // default value for signature is 0
        if (!$proposal->getSignature() or !$proposal->getCall()) {
            return '';
        }
        $signature = '';
        // use DIVIDER only, if there is a shortcut
        if (trim($proposal->getCall()->getShortcut()) !== '') {
            $signature .=  trim($proposal->getCall()->getShortcut());
        }
        $signature .=  sprintf($this->settings['signatureFormat'], (int)$proposal->getSignature());
        return $signature;
    }

    /**
     * @param int $l0headRow
     * @param int $l1headRow
     * @param array $head
     * @param int $itemHeadRow
     * @param Worksheet $sheet
     * @param array $columns
     */
    protected function createGroupTitleRows(int $l0headRow, int $l1headRow, array $head, int $itemHeadRow, Worksheet $sheet, array $columns): void
    {
        foreach ([$l0headRow, $l1headRow] as $headRowForGroup) {
            $lineData = [];
            foreach ($head[$itemHeadRow] as $column => $item) {
                if (!isset($head[$headRowForGroup][$column])) {
                    $lineData[$column] = '';
                } else {
                    $lineData[$column] = $head[$headRowForGroup][$column];
                }

                if (str_starts_with((string)$lineData[$column], chr(39))) {
                    $lineData[$column] = substr($lineData[$column], 1);
                    $sheet->setCellValueExplicit([$columns, 0], $lineData[$column], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValueByColumnAndRow($column, 0, $lineData[$column]);
                }
            }
        }
    }

    /**
     * @param array $head
     * @param Worksheet $sheet
     * @param array $colType
     */
    protected function createItemTitleRow(array $head, Worksheet $sheet, array $colType): void
    {
        foreach ($head as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {

                if (str_starts_with((string)$value, chr(39))) {
                    $value = substr($value, 1);
                    $sheet->setCellValueExplicit([$colIndex + 1, $rowIndex], $value, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex, $value);
                }

                // set width of text or special columns depended on type
                // autoSize will be too large!
                if (1 == 0) {
                    if (isset($colType[$colIndex + 1]) and $colType[$colIndex + 1] == self::TYPE_TEXT) {
                        $sheet->getColumnDimensionByColumn($colIndex + 1)->setWidth(40);
                    } else {
//                        $sheet->getColumnDimensionByColumn($colIndex + 1)->setAutoSize(true);
                    }
                }
            }
        }
    }

    /**
     * @param int $itemHeadRow
     * @param array $export
     * @param $head
     * @param Worksheet $sheet
     * @return void
     */
    protected function createDataRows(int $itemHeadRow, array $export, $head, Worksheet $sheet): void
    {
        $rowIndex = $itemHeadRow + 1;
        foreach ($export as $proposalData) {
            $lineData = [];
            foreach ($head as $column => $item) {
                if (!isset($proposalData[$column])) {
                    $lineData[$column] = '';
                } else {
                    $lineData[$column] = $proposalData[$column];
                }

                if (str_starts_with((string)$lineData[$column], chr(39))) {
                    $lineData[$column] = substr($lineData[$column], 1);
                    $sheet->setCellValueExplicit([$column + 1, $rowIndex], $lineData[$column], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValueByColumnAndRow($column + 1, $rowIndex, $lineData[$column]);
                }


            }
            $rowIndex++;
        }
    }

    /**
     * @param array $columns
     * @return array
     */
    protected function collectData(array $columns, array $states): array
    {
        $export = [];

        foreach ($this->proposals as $proposal) {
            $proposalUid = $proposal->getUid();
            $export[$proposalUid] = [];
            $colI = 0;
            $export[$proposalUid][$colI++] = $proposalUid;
            $export[$proposalUid][$colI++] = $proposal->getApplicant()?->getEmail() ?? '[Deleted Applicant]';
            $export[$proposalUid][$colI++] = $proposal->getApplicant()?->getUid() ?? 0;
            $export[$proposalUid][$colI++] = $this->buildSignature($proposal);
            $export[$proposalUid][$colI++] = $states[$proposal->getState()];
            $export[$proposalUid][$colI++] = date("d.m.Y", $proposal->getSubmitTstamp());
            $export[$proposalUid][$colI++] = date("d.m.Y", $proposal->getEditTstamp());

            /** @var Answer $answer */
            foreach ($proposal->getAnswers() as $answer) {
                $value = $answer->getValue();
                if (!$answer->getItem()) {
                    continue;
                }
                switch ($answer->getItem()->getType()) {
                    case self::TYPE_SELECT_SINGLE:
                    case self::TYPE_SELECT_MULTIPLE:
                    case self::TYPE_CHECKBOX:
                    case self::TYPE_RADIOBUTTON:
                        if ($answer->getValue() !== '') {
                            if (is_array(json_decode($answer->getValue(), true))) {
                                $selectedOptions = json_decode($answer->getValue(), true);
                            } else {
                                $selectedOptions = [$answer->getValue()];
                            }

                            $itemsMap = [];
                            $values = [];
                            $this->getOptionsToItemsMap($answer->getItem(), $itemsMap, '');
                            foreach ($itemsMap as $option) {
                                foreach ($selectedOptions as $selectedOption) {
                                    if ($option['key'] == $selectedOption) {
                                        $valueTmp = $option['label'];
                                        if ($option['key'] !== $option['label']) {
                                            $valueTmp .= ' (' . $option['key'] . ')';
                                        }
                                        $values[] = $valueTmp;
                                    }
                                }
                            }
                            $value = implode(', ', $values);
                        }
                        break;
                    case self::TYPE_UPLOAD:
                        if ($answer->getValue() !== '') {
                            $files = explode(',', $answer->getValue());
                            $values = [];
                            foreach ($files as $file) {
                                try {
                                    $fileObj = $this->resourceFactory->getFileObject($file);
                                } catch (\TYPO3\CMS\Core\Resource\Exception $e) {
                                    //                                    echo 'Exception abgefangen: ', $e->getMessage();
                                    //                                    die();
                                    $fileObj = null;
                                }
                                if ($fileObj) {
                                    $values[] = $fileObj->getName();
                                } else {
                                    $values[] = '****missing FILE? ' . $file;
                                }
                            }
                            $value = implode(', ', $values);
                        }
                    case self::TYPE_STRING:
//                    case self::TYPE_SUM:
                        if ($this->isItemNumeric($answer->getItem())) {
//                            DebuggerUtility::var_dump($answer->getValue(),basename(__FILE__). ' #'. (string) __LINE__ );
                            $value = $this->cleanupByNumberStyle($answer->getValue());
//                            DebuggerUtility::var_dump($value,basename(__FILE__). ' #'. (string) __LINE__ );die();
                        }
                        break;
                }

                $key = $answer->getModel()->getUid() . '--' . $answer->getGroupCounter0() . '--' . $answer->getGroupCounter1() . '--' . $answer->getItem()->getUid();
                $valuePrefix = '';
                if (str_starts_with($value, '=') or str_starts_with($value, '+') or str_starts_with($value, '-')) {
                    $valuePrefix = chr(39);
                }
                $export[$proposalUid][$columns[$key]] = $valuePrefix . $value;
                if ($answer->getItem()->isAdditionalValue()) {
                    $export[$proposalUid][$columns[$key] + 1] = $answer->getAdditionalValue();
                }
                if ($answer->getItem()->getType() == self::TYPE_DATE2) {
                    $export[$proposalUid][$columns[$key] + 1] = $answer->getAdditionalValue();
                }
            }
        }
        return $export;
    }

    /**
     * @param array $groupsCounter
     * @param array $head
     * @param int $l0headRow
     * @param int $columnNo
     * @param int $l1headRow
     * @param array $colType
     * @param array $columns
     * @param int $itemHeadRow
     * @return array
     */
    protected function createDataStructure(array $groupsCounter, array $head, int $l0headRow, int $columnNo, int $l1headRow, array $colType, array $columns, int $itemHeadRow): array
    {
        /** @var FormPage $page */
        foreach ($this->proposals[0]->getCall()->getFormPages() as $page) {
            /** @var FormGroup $groupL0 */
            foreach ($page->getItemGroups() as $groupL0) {
                // todo build groups row - title of groups L0
                if (!isset($groupsCounter[$groupL0->getUid()])) {
                    continue;
                }

                $maxL0 = $groupsCounter[$groupL0->getUid()]['max'];
                for ($indexL0 = 0; $indexL0 < $maxL0; $indexL0++) {
                    $head[$l0headRow][$columnNo] = $this->createGroupTitle($groupL0, $maxL0, $indexL0);

                    // todo build groups row - title of groups L0
                    // $head[$L0headRow][$columnNo] = $group->getTitle()

                    // no item in meta groups
                    if ($groupL0->getType() == self::GROUPTYPE_META) {
                        foreach ($groupL0->getItemGroups() as $groupIndexL1 => $groupL1) {
                            $maxL1 = $groupsCounter[$groupL0->getUid()]['instances'][$indexL0][$groupL1->getUid()]['max'];
                            for ($indexL1 = 0; $indexL1 < $maxL1; $indexL1++) {
                                $head[$l1headRow][$columnNo] = $this->createGroupTitle($groupL1, $maxL1, $indexL1);

                                /** @var FormItem $item */
                                foreach ($groupL1->getItems() as $item) {
                                    $colType[$columnNo] = $item->getType();
                                    $key = $groupL1->getUid() . '--' . $indexL0 . '--' . $indexL1 . '--' . $item->getUid();
                                    list($columns, $head, $columnNo) = $this->createItemStructure($columnNo, $columns, $key, $item, $head, $itemHeadRow, $colType);
                                }
                            }
                        }
                    } else {
                        // $indexL0 = 0;
                        /** @var FormItem $item */
                        foreach ($groupL0->getItems() as $item) {
                            $key = $groupL0->getUid() . '--' . 0 . '--' . $indexL0 . '--' . $item->getUid();
                            list($columns, $head, $columnNo) = $this->createItemStructure($columnNo, $columns, $key, $item, $head, $itemHeadRow, $colType);
                        }
                    }
                }
            }
        }
        return array($head, $colType, $columns);
    }

    /**
     * @param int $columnNo
     * @param array $columns
     * @param string $key
     * @param FormItem $item
     * @param array $head
     * @param int $itemHeadRow
     * @param array $colType
     * @return array
     */
    protected function createItemStructure(int $columnNo, array $columns, string $key, FormItem $item, array $head, int $itemHeadRow, array $colType): array
    {
        $columns[$key] = $columnNo;
        if (str_starts_with($item->getQuestion(), '=') or str_starts_with($item->getQuestion(), '+') or str_starts_with($item->getQuestion(), '-')) {
            $itemLabelPrefix = chr(39);
        } else {
            $itemLabelPrefix = '';
        }
        $head[$itemHeadRow][$columnNo] = $itemLabelPrefix . $item->getQuestion();
        $columnNo++;
        if ($item->isAdditionalValue()) {
            $head[$itemHeadRow][$columnNo] = 'additionalAnswer';
            $colType[$columnNo] = self::TYPE_TEXT;
            $columnNo++;
        }
        if ($item->getType() == self::TYPE_DATE2) {
            $head[$itemHeadRow][$columnNo] = 'Date until';
            $colType[$columnNo] = self::TYPE_STRING;
            $columnNo++;
        }
        return array($columns, $head, $columnNo);
    }

}
