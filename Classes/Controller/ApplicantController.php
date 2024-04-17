<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Controller;

use OpenOAP\OpenOap\Domain\Model\Answer;
use OpenOAP\OpenOap\Domain\Model\Applicant;
use OpenOAP\OpenOap\Domain\Model\Call;
use OpenOAP\OpenOap\Domain\Model\Proposal;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * This file is part of the "Open Application Platform" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
 *          Ingeborg Hess <ingeborg.hess@cosmoblonde.de>, cosmoblonde gmbh
 */

/**
 * ApplicantController
 */
class ApplicantController extends OapFrontendController
{
    /**
     * @var Applicant
     */
    protected $applicant;

    /**
     * @var array
     */
    protected $countries = [];

    /**
     * action initialize
     */
    public function initializeAction()
    {
        parent::initializeAction();
        $this->frontendAccessControlService = GeneralUtility::makeInstance(\OpenOAP\OpenOap\Service\FrontendAccessControlService::class);
        $frontendUserId = $this->frontendAccessControlService->getFrontendUserId();
        if ($frontendUserId != null) {
            $this->applicant = $this->applicantRepository->findByUid($frontendUserId);
        }

        $countriesItemOption = $this->itemOptionRepository->findByUid($this->settings['countriesItemOptionId']);
        if ($countriesItemOption != null) {
            $countryOptionsArray = GeneralUtility::trimExplode("\r\n", $countriesItemOption->getOptions());
            if (count($countryOptionsArray) > 0) {
                foreach ($countryOptionsArray as $item) {
                    $itemArray = GeneralUtility::trimExplode(';', $item);
                    $this->countries[$itemArray[0]] = $itemArray[1];
                }
            }
        }
    }

    /**
     * action initializeEdit
     */
    public function initializeEditAction()
    {
        // Called applicantform action depends on parent page
        if ($this->settings['masterdataEditPageId'] != $this->configurationManager->getContentObject()->data['pid']) {
            $this->redirect('extend');
        }
    }

    /**
     * action show
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function showAction(): \Psr\Http\Message\ResponseInterface
    {
        $this->view->assignMultiple([
            'applicant' => $this->applicant,
            'countries' => $this->countries,
            'settings' => $this->settings,
        ]);
        return $this->htmlResponse();
    }

    /**
     * action extend
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function extendAction(): \Psr\Http\Message\ResponseInterface
    {
        $this->view->assignMultiple([
            'applicant' => $this->applicant,
            'countries' => $this->countries,
            'settings' => $this->settings,
        ]);
        return $this->htmlResponse();
    }

    /**
     * action edit
     *
     * @param Applicant|null $applicant
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("applicant")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function editAction(Applicant $applicant = null): \Psr\Http\Message\ResponseInterface
    {
        $this->view->assignMultiple([
            'applicant' => $applicant === $this->applicant ? $applicant : $this->applicant,
            'countries' => $this->countries,
            'settings' => $this->settings,
        ]);
        return $this->htmlResponse();
    }

    /**
     * action update
     *
     * @param Applicant $applicant
     */
    public function updateAction(Applicant $applicant)
    {
        // check if anything was changed
        foreach (array_keys($applicant->_getProperties()) as $propertyName) {
            $isDirty = $applicant->_isDirty($propertyName);
            if ($isDirty) {
                break;
            }
        }
        if (!$isDirty) {
            $this->addFlashMessage(LocalizationUtility::translate('tx_openoap_applicant.messages.noChanges', 'open_oap'));
            $this->redirect('edit', 'Applicant', null, ['applicant' => $applicant]);
        }
        $this->applicantRepository->update($applicant);
        $this->redirect('dashboard', 'Applicant', null, ['applicant' => $applicant], $this->settings['dashboardPageId']);
    }

    /**
     * action dashboard
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dashboardAction(): \Psr\Http\Message\ResponseInterface
    {
        if ($this->applicant != null) {
            $status = $this->status();

            $proposalsActive = $this->proposalRepository->findProposalsByApplicant(
                $this->applicant,
                0,
                (int)($this->settings['proposalsActiveLimit'])
            );
            $proposalsActiveCommentsCount = $this->countProposalComments($proposalsActive);
            $proposalsActiveAttachmentsCount = $this->countProposalAttachments($proposalsActive);

            $proposalsArchived = $this->proposalRepository->findProposalsByApplicant(
                $this->applicant,
                1,
                (int)($this->settings['proposalsArchivedLimit'])
            );
            $proposalsArchivedCommentsCount = $this->countProposalComments($proposalsArchived, 1);
            $proposalsArchivedAttachmentsCount = $this->countProposalAttachments($proposalsArchived);

            $calls = $this->callRepository->findAllByPid((int)$this->settings['callPid']);

            $countAllProposalsActive = $this->proposalRepository->countProposalsByApplicant(
                $this->applicant,
                0
            );

            $countAllProposalsArchived = $this->proposalRepository->countProposalsByApplicant(
                $this->applicant,
                1
            );

            $activeCalls = $this->callRepository->findActiveCalls((int)$this->settings['callPid'], $this->applicant, $this->settings['testerFeGroupsId']);
            $callGroups = $this->callGroupRepository->findAllCallGroups();

            /** @var Call $activeCall */
            foreach ($activeCalls as $activeCall) {
                if ($activeCall->getBlockedLanguages() !== '') {
                    $blockedLanguages = explode(',', $activeCall->getBlockedLanguages());

                    if (in_array($this->language->getLanguageId(), $blockedLanguages)) {
                        $activeCall->setHidden(1);
                    }
                }

                $callGroupId = $activeCall->getCallGroup();
                if (isset($callGroups[$callGroupId])) {
                    $blockedGroupLanguages = explode(',', $callGroups[$callGroupId]['blocked_languages']);

                    if (in_array($this->language->getLanguageId(), $blockedGroupLanguages)) {
                        $activeCall->setHidden(1);
                    }
                    $supporter = $activeCall->getSupporter() ? $activeCall->getSupporter()->getUid() : 0;
                    $callGroups[$callGroupId]['calls'][$supporter][] = $activeCall;
                }
            }
        }

        $jsMessages = $this->getJsMessages();

        $this->view->assignMultiple([
            'applicant' => $this->applicant,
            'countries' => $this->countries,
            'constants' => $this->getConstants()['PROPOSAL'],
            'proposalsActive' => $proposalsActive,
            'proposalsArchived' => $proposalsArchived,
            'proposalsActiveCommentsCount' => $proposalsActiveCommentsCount,
            'proposalsArchivedCommentsCount' => $proposalsArchivedCommentsCount,
            'proposalsActiveFilesCount' => $proposalsActiveAttachmentsCount,
            'proposalsArchivedFilesCount' => $proposalsArchivedAttachmentsCount,
            'countAll' => ['active' => $countAllProposalsActive, 'archived' => $countAllProposalsArchived],
            'settings' => $this->settings,
            'callGroups' => $callGroups,
            'activeCalls' => $activeCalls,
            'jsMessages' => $jsMessages,
            'nowTimestamp' => time(),
            'language' => $this->language->getLanguageId(),
        ]);
        return $this->htmlResponse();
    }

    /**
     * action proposals
     *
     * @param int|null $archive
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function proposalsAction(int $archive = null): \Psr\Http\Message\ResponseInterface
    {
        if ($this->applicant != null) {
            $status = $this->status();
            $archived = $archive == null ? 0 : 1;

            $proposals = $this->proposalRepository->findProposalsByApplicant(
                $this->applicant,
                $archived
            );
            $proposalsCommentsCount = $this->countProposalComments($proposals, $archived);
        }

        $this->view->assignMultiple([
            'applicant' => $this->applicant,
            'archived' => $archived,
            'constants' => $this->getConstants()['PROPOSAL'],
            'proposals' => $proposals,
            'proposalsCommentsCount' => $proposalsCommentsCount,
            'settings' => $this->settings,
        ]);
        return $this->htmlResponse();
    }

    /**
     * action mail
     *
     * @param \OpenOAP\OpenOap\Domain\Model\Proposal $proposal
     * @param string $mailtextSetting
     * @param string $mailTemplate
     */
    public function mailAction(\OpenOAP\OpenOap\Domain\Model\Proposal $proposal, $mailtextSetting, $mailTemplate)
    {
        $getMailTextFunc = 'get'.  ucfirst($mailtextSetting);
        $mailTemplatePaths = $this->getMailTemplatePaths();
        $mailText = $this->parseMailtext($proposal, $proposal->getCall()->getSupporter()->$getMailTextFunc());

        $this->sendEmail($proposal, $mailTemplatePaths, $mailTemplate, $mailText);

        $uri = $this->uriBuilder
            ->reset()
            ->setTargetPageUid((int)$this->settings['dashboardPageId'])
            ->build();
        $this->redirectToURI($uri, 0, 200);
    }

    /**
     * @return array status
     */
    protected function status()
    {
        return $this->getConstants()['PROPOSAL'];
    }

    /**
     * Count edited and new proposal comments
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $proposals
     * @param int $archive
     * @return array number of edited and new comments
     */
    protected function countProposalComments(\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $proposals, $archive = 0): array
    {
        $counted = [];
        foreach ($proposals as $proposal) {
            $editedCount = 0;
            $newCount = 0;
            foreach ($proposal->getComments() as $comment) {
                switch ($comment->getSource()) {
                    case self::COMMENT_SOURCE_EDIT:
                        $editedCount++;
                        if ($comment->getState() == self::COMMENT_STATE_NEW) {
                            $newCount++;
                        }
                        break;
                    case self::COMMENT_SOURCE_EDIT_ANSWER:
                        if ($comment->getState() == self::COMMENT_STATE_ACCEPTED) {
                            $editedCount++;
                        } elseif ($comment->getState() == self::COMMENT_STATE_NEW && $proposal->getState() == self::PROPOSAL_RE_OPENED) {
                            $editedCount++;
                            $newCount++;
                        }
                        break;
                }
            }
            $counted[$proposal->getUid()]['edited'] = $editedCount;

            if ($archive == 0) {
                $counted[$proposal->getUid()]['new'] = $newCount;
            }
        }
        return $counted;
    }

    /**
     * Count edited and new proposal comments
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $proposals
     * @param int $archive
     * @return array number of edited and new comments
     */
    protected function countProposalAttachments(\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $proposals): array
    {
        $counted = [];
        /** @var Proposal $proposal */
        foreach ($proposals as $proposal) {
            $countFiles = 0;
            /** @var Answer $answer */
            foreach ($proposal->getAnswers() as $answer) {
                if ($answer->getItem()) {
                    if ($answer->getItem()->getType() == self::TYPE_UPLOAD and $answer->getValue() !== '') {
                        $files = array_unique(
                            \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $answer->getValue(), 1)
                        );
                        $countFiles += count($files);
                    }
                }
            }
            $counted[$proposal->getUid()] = $countFiles;
        }
        return $counted;
    }
}
