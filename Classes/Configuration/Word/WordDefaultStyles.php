<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Configuration\Word;

class WordDefaultStyles
{
    public const WORD_STYLES = [
        'DefaultFont' =>
            [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => false,
            ],
        'DefaultParagraph' =>
            [
                'SpaceBefore' => 8,
                'SpaceAfter' => 6,
                'LineHeight' => 1.2,
            ],
        'OutputFormat' =>
            [
                'Shading' => 'dddddd',
            ],
        'TableCellHeader' =>
            [
                'alignment' => 'center',
                'spaceAfter' => 4,
                'spaceBefore' => 4,
            ],
        'TableCellValue' =>
            [
                'alignment' => 'right',
                'spaceAfter' => 4,
                'spaceBefore' => 4,
            ],
        'TableCellLabel' =>
            [
                'alignment' => 'left',
                'spaceAfter' => 4,
                'spaceBefore' => 4,
            ],
        'TableCellWidthLabel' => 5,
        'TableCellWidthValue' => 2.5,
        'TableBorderSize' => 6,
        'IntroTextFont' =>
            [
                'bold' => false,
                'size' => 11,
            ],
        'HelpTextFont' =>
            [
                'bold' => false,
                'size' => 11,
                'italic' => true,
            ],
        'DocumentTitleFontFormat' =>
            [
                'bold' => true,
                'size' => 24,
                'color' => '0476b6',
            ],
        'PageTitleFontFormat' =>
            [
                'bold' => true,
                'size' => 12,
            ],
        'PageTitleFontFormat2' =>
            [
                'bold' => true,
                'size' => 11,
            ],
        'GroupTitleFontFormat' =>
            [
                'bold' => true,
                'size' => 12,
            ],
        'MetaGroupTitleFontFormat' =>
            [
                'bold' => true,
                'size' => 14,
            ],
        'QuestionFont' =>
            [
                'bold' => true,
                'size' => 11,
            ],
        'LineStyle' =>
            [
                'weight' => 1,
                'width' => 100,
                'height' => 0,
                'color' => '333333',
            ],
        'LineStyleEndMetaGroup' =>
            [
                'weight' => 2,
                'width' => 100,
                'height' => 0,
                'color' => '333333',
            ],
        'page' =>
            [
                'margin' =>
                    [
                        'left' => 2,
                        'right' => 2,
                        'top' => 3,
                        'bottom' => 3,
                    ],
            ],
    ];
}
