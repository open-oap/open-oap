<?php

namespace OpenOAP\OpenOap\UserFunctions\FormEngine;

use OpenOAP\OpenOap\Domain\Repository\ItemOptionRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A user function used in select
 */
class TypeSelectItemsProcFunc
{
    /**
     * @var \OpenOAP\OpenOap\Domain\Repository\ItemOptionRepository
     */
    protected $itemOptionRepository;

    /**
     * @param ItemOptionRepository $itemOptionRepository
     */
    public function injectItemOptionRepository(ItemOptionRepository $itemOptionRepository)
    {
        $this->itemOptionRepository = $itemOptionRepository;
    }

    /**
     * Process custom options
     *
     * @param array $params
     */
    public function countryItemsProcFunc(&$params): void
    {
        $itemOptionId = (int)$params['items'][0][1];
        $params['items'] = [];
        $params['items'][] = ['', ''];

        if ($itemOptionId) {
            $countriesItemOption = $this->itemOptionRepository->findByUid($itemOptionId);
            if ($countriesItemOption != null) {
                $countryOptionsArray = GeneralUtility::trimExplode("\r\n", $countriesItemOption->getOptions());
                foreach ($countryOptionsArray as $item) {
                    //$params['items'][] = [$item, $item];
                    $itemArray = GeneralUtility::trimExplode(';', $item);
                    $params['items'][] = [$itemArray[1], $itemArray[0]];
                }
            }
        }
    }
}
