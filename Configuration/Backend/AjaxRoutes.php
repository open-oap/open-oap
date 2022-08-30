<?php

return [
    'openoap_proposal_upload' => [
        'path' => '/open-oap/proposal_upload',
        'target' => \OpenOAP\OpenOap\Controller\ProposalController::class . '::uploadAction',
    ],
];
