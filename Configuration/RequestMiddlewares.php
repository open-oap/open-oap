<?php

return [
    'frontend' => [
        'OpenOAP/OpenOAP/proposal_upload' => [
            'target' => \OpenOAP\OpenOap\Middleware\AjaxUpload::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
        'OpenOAP/OpenOAP/editlock_ajax' => [
            'target' => \OpenOAP\OpenOap\Middleware\EditLockAjax::class,
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
