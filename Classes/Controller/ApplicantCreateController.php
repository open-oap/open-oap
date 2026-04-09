<?php

namespace OpenOAP\OpenOap\Controller;

use In2code\Femanager\Controller\NewController;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

class ApplicantCreateController extends NewController
{
    protected function statusUserConfirmationRefused(User $user, string $hash): bool
    {
        // Ensure that the 'Delete profile' link in the registration email only works while
        // the user account is disabled.
        // Once the account has been enabled, the 'Delete profile' link will stop working and
        // display the 'userAlreadyConfirmed' message.
        if (!$user->isDisable() && HashUtility::validHash($hash, $user)) {
            $this->addFlashMessage(
                LocalizationUtility::translate('userAlreadyConfirmed'),
                '',
                ContextualFeedbackSeverity::ERROR
            );

            return false;
        }

        return parent::statusUserConfirmationRefused($user, $hash);
    }
}
