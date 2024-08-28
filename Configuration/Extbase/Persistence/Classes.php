<?php

declare(strict_types=1);

return [
    \OpenOAP\OpenOap\Domain\Model\Applicant::class => [
        'tableName' => 'fe_users',
        'recordType' => 0,
        'properties' => [
            'companyEmail' => [
                'fieldName' => 'tx_openoap_company_email',
            ],
            'preferredLang' => [
                'fieldName' => 'tx_openoap_preferred_lang',
            ],
            'privacypolicy' => [
                'fieldName' => 'tx_openoap_privacypolicy',
            ],
            'salutation' => [
                'fieldName' => 'tx_openoap_salutation',
            ],
            'proposals' => [
                'fieldName' => 'tx_openoap_proposals',
            ],
        ],
    ],

    \OpenOAP\OpenOap\Domain\Model\ApplicantGroup::class => [
        'tableName' => 'fe_groups',
    ],

    \OpenOAP\OpenOap\Domain\Model\Proposal::class => [
        'properties' => [
           'call' => [
                'fieldName' => 'tx_openoap_call',
            ],
        ],
    ],

    \OpenOAP\OpenOap\Domain\Model\Comment::class => [
        'properties' => [
           'created' => [
                'fieldName' => 'crdate',
            ],
            'author' => [
                'fieldName' => 'cruser_id',
            ],
        ],
    ],
];
