<?php

return [
    'frontend' => [
        'OpenOAP/OpenOAP/proposal_upload' => [
            'target' => \OpenOAP\OpenOap\Middleware\AjaxUpload::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
