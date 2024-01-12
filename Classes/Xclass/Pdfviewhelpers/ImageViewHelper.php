<?php

namespace OpenOAP\OpenOap\Xclass\Pdfviewhelpers;

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;

class ImageViewHelper extends \Bithost\Pdfviewhelpers\ViewHelpers\ImageViewHelper
{
    protected function processImage(FileInterface $imageFile, array $processingInstructions): FileInterface
    {
        $imageFileCrop = $imageFile->hasProperty('crop') && $imageFile->getProperty('crop') ? json_decode($imageFile->getProperty('crop'), true) : [];

        if (!empty($processingInstructions) || !empty($imageFileCrop)) {
            if (isset($processingInstructions['crop'])) {
                $argumentsCrop = is_array($processingInstructions['crop']) ? $processingInstructions['crop'] : json_decode($processingInstructions['crop'] ?? '', true);
                $argumentsCrop = is_array($argumentsCrop) ? $argumentsCrop : [];
            } else {
                $argumentsCrop = [];
            }

            $crop = array_merge($imageFileCrop, $argumentsCrop);

            $cropVariantCollection = CropVariantCollection::create((string)json_encode($crop));
            $cropVariant = $processingInstructions['cropVariant'] ?? 'default';
            $cropArea = $cropVariantCollection->getCropArea($cropVariant);

            unset($processingInstructions['cropVariant']);

            if ($cropArea->isEmpty()) {
                unset($processingInstructions['crop']);
            } else {
                $processingInstructions['crop'] = $cropArea->makeAbsoluteBasedOnFile($imageFile);
            }

            if (!empty($processingInstructions)) {
                return $this->imageService->applyProcessingInstructions($imageFile, $processingInstructions);
            }
        }

        return $imageFile;
    }
}
