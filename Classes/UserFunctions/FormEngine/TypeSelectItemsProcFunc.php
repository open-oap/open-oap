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
     * @var ItemOptionRepository
     */
    protected ItemOptionRepository $itemOptionRepository;

    /**
     * Injects the ItemOptionRepository
     *
     * @param ItemOptionRepository $itemOptionRepository
     */
    public function injectItemOptionRepository(ItemOptionRepository $itemOptionRepository): void
    {
        $this->itemOptionRepository = $itemOptionRepository;
    }

    /**
     * Process custom options
     *
     * @param array $params Reference to the parameter array used by TYPO3
     */
    public function countryItemsProcFunc(array &$params): void
    {
        $itemOptionId = (int)($params['items'][0][1] ?? 0);
        $params['items'] = [['', '']]; // Reset items with an empty default value

        if ($itemOptionId > 0) {
            $countriesItemOption = $this->itemOptionRepository->findByUid($itemOptionId);

            if ($countriesItemOption !== null) {
                $countryOptionsArray = GeneralUtility::trimExplode("\r\n", $countriesItemOption->getOptions(), true);

                foreach ($countryOptionsArray as $item) {
                    $itemArray = GeneralUtility::trimExplode(';', $item, true);

                    // Ensure valid format before adding
                    if (count($itemArray) === 2) {
                        $params['items'][] = [$itemArray[1], $itemArray[0]];
                    }
                }
            }
        }
    }
}
