<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use In2code\Femanager\Controller\InvitationController as FemanagerInvitationController;
use In2code\Femanager\Event\InviteUserEditEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\UserUtility;
use OpenOAP\OpenOap\Domain\Model\User;
use OpenOAP\OpenOap\TypeConverter\UserObjectConverter;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Annotation\Validate;

class UserInvitationController extends FemanagerInvitationController
{
    public function initializeUpdateAction()
    {
        // without this lines the user isn't there in UpdateAction!
        $userId = $this->arguments->getArgument('user');
        $user = $this->arguments->getArgument('user')->getPropertyMappingConfiguration()->setTypeConverter($this->objectManager->get(UserObjectConverter::class));
    }

    /**
     * action update
     *
     * @param \In2code\Femanager\Domain\Model\User $user
     * @param string $hash
     *
     * this is checking the quality of the password
     * @Validate("In2code\Femanager\Domain\Validator\ServersideValidator", param="user")
     * this checks whether the two password entries are identical
     * @Validate("In2code\Femanager\Domain\Validator\PasswordValidator", param="user")
     * @throws InvalidPasswordHashException
     */
    public function updateAction(\In2code\Femanager\Domain\Model\User $user, $hash = null)
    {
        UserUtility::hashPassword($user, ConfigurationUtility::getValue('invitation/misc/passwordSave', $this->settings));
        $user->setDisable(false);
        $user = UserUtility::overrideUserGroup($user, $this->settings, 'invitation');

        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();

        $this->addFlashMessage(LocalizationUtility::translate('createAndInvitedFinished'));

        // eventDispatcher - not in this derived controller
        $this->redirectByAction('invitation', 'redirectPasswordChanged');
        $this->redirect('status');
        die();
    }

    /**
     * action edit
     *
     * @param int $user User UID
     * @param string $hash
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("user")
     */
    public function editAction($user, $hash = null): ResponseInterface
    {
        $user = $this->userRepository->findByUid($user);
        // User must exist and hash must be valid
        if ($user === null || !HashUtility::validHash($hash, $user)) {
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', AbstractMessage::ERROR);
            $this->redirect('status');
        }

        // User must not be deleted (deleted = 0) and not be activated (disable = 1)
        if ($user->getDisable() == 0) {
            $this->addFlashMessage(LocalizationUtility::translate('userAlreadyConfirmed'), '', AbstractMessage::ERROR);
            $this->redirect('status');
        }

        // do not set user enabled at this point... you have to set password first
        // $user->setDisable(false);
        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();

        $this->eventDispatcher->dispatch(new InviteUserEditEvent($user, $hash));

        $this->view->assignMultiple(
            [
                'user' => $user,
                'hash' => $hash,
            ]
        );

        $this->assignForAll();
        return $this->htmlResponse();
    }
}
