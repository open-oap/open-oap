<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use In2code\Femanager\Controller\InvitationController as FemanagerInvitationController;

class UserInvitationController extends FemanagerInvitationController
{
    /**
    * action create
    *
    * @param OpenOAP\OpenOap\Domain\Model\User $user
    * @TYPO3\CMS\Extbase\Annotation\Validate("In2code\Femanager\Domain\Validator\ServersideValidator", param="user")
     * @TYPO3\CMS\Extbase\Annotation\Validate("In2code\Femanager\Domain\Validator\PasswordValidator", param="user")
     * @TYPO3\CMS\Extbase\Annotation\Validate("In2code\Femanager\Domain\Validator\CaptchaValidator", param="user")
     */
    public function createAction($user): void
    {
        parent::createAction($user);
    }

    /**
     * action update
     *
     * @param OpenOAP\OpenOap\Domain\Model\User $user
     * @TYPO3\CMS\Extbase\Annotation\Validate("In2code\Femanager\Domain\Validator\ServersideValidator", param="user")
     * @TYPO3\CMS\Extbase\Annotation\Validate("In2code\Femanager\Domain\Validator\PasswordValidator", param="user")
     */
    public function updateAction($user)
    {
        parent::createAction($user);
    }
}
