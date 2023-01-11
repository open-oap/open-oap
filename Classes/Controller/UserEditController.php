<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use In2code\Femanager\Controller\EditController as FemanagerEditController;

class UserEditController extends FemanagerEditController
{
    /**
    * action update
    *
    * @param OpenOAP\OpenOap\Domain\Model\User $user
    * @TYPO3\CMS\Extbase\Annotation\Validate("In2code\Femanager\Domain\Validator\ServersideValidator", param="user")
    * @TYPO3\CMS\Extbase\Annotation\Validate("In2code\Femanager\Domain\Validator\PasswordValidator", param="user")
    */
    public function updateAction($user): void
    {
        parent::updateAction($user);
    }
}
