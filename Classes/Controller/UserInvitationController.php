<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use In2code\Femanager\Controller\InvitationController as FemanagerInvitationController;
use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Validator\PasswordValidator;
use In2code\Femanager\Domain\Validator\ServersideValidator;
use In2code\Femanager\Event\InviteUserEditEvent;
use In2code\Femanager\Event\InviteUserUpdateEvent;
use In2code\Femanager\Utility\ConfigurationUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use OpenOAP\OpenOap\TypeConverter\UserObjectConverter;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Validate;

class UserInvitationController extends FemanagerInvitationController
{
    public function initializeUpdateAction(): void
    {
        $userObjectConverter = GeneralUtility::makeInstance(UserObjectConverter::class);

        // without this line the user is not found, because it is still disabled=1
        $this->arguments->getArgument('user')->getPropertyMappingConfiguration()->setTypeConverter($userObjectConverter);
    }

    /**
     * action update
     */
    #[Validate(['validator' => ServersideValidator::class, 'param' => 'user'])]
    #[Validate(['validator' => PasswordValidator::class, 'param' => 'user'])]
    public function updateAction(\In2code\Femanager\Domain\Model\User $user, string $hash = null): ResponseInterface
    {
        if (!HashUtility::validHash($hash, $user)) {
            $this->addFlashMessage(LocalizationUtility::translateByState(Log::STATUS_PROFILEUPDATEREFUSEDSECURITY), '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('status');
        }

        $user = UserUtility::overrideUserGroup($user, $this->settings, 'invitation');
        UserUtility::hashPassword(
            $user,
            ConfigurationUtility::getValue('invitation/misc/passwordSave', $this->settings)
        );
        // now we enable the user
        $user->setDisable(false);
        $this->userRepository->update($user);
        $this->persistenceManager->persistAll();
        $this->eventDispatcher->dispatch(new InviteUserUpdateEvent($user));

        $this->addFlashMessage(LocalizationUtility::translate('createAndInvitedFinished'));
        $this->logUtility->log(Log::STATUS_INVITATIONPROFILEENABLED, $user);
        $notifyAdmin = ConfigurationUtility::getValue('invitation/notifyAdmin', $this->settings);
        if ($notifyAdmin) {
            $this->sendMailService->send(
                'invitationNotify',
                StringUtility::makeEmailArray(
                    $notifyAdmin,
                    ConfigurationUtility::getValue(
                        'invitation/email/invitationAdminNotify/receiver/name/value',
                        $this->settings
                    )
                ),
                StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                'Profile creation with invitation - Final',
                [
                    'user' => $user,
                    'settings' => $this->settings,
                ],
                ConfigurationUtility::getValue('invitation./email./invitationAdminNotify.', $this->config)
            );
        }

        return $this->redirectByAction('invitation', 'redirectPasswordChanged', 'status');
    }

    /**
     * action edit
     */
    public function editAction(int $user, string $hash = null): ResponseInterface
    {
        $user = $this->userRepository->findByUid($user);

        // User must exist and hash must be valid
        if ($user === null || !HashUtility::validHash($hash, $user)) {
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('status');
        }

        // User must not be deleted (deleted = 0) and not be activated (disable = 1)
        if ($user->isDisable() === false) {
            $this->addFlashMessage(LocalizationUtility::translate('userAlreadyConfirmed'), '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('status');
        }

        // do not set user enabled at this point... you have to set password first, see updateAction()
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
