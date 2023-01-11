<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Open Application Plattform',
    'description' => '',
    'category' => 'plugin',
    'author' => 'Thorsten Born, Ingeborg Heß',
    'author_email' => 'thorsten.born@cosmoblonde.de, ingeborg.hess@cosmoblonde.de',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '1.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
