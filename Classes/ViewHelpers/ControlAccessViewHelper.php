<?php
/**
 * User: Thorsten Born
 * Determines the language-dependent label of an option - depending on the key
 */

namespace OpenOAP\OpenOap\ViewHelpers;

use OpenOAP\OpenOap\Domain\Model\Proposal;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ControlAccessViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('proposal', 'object', 'Proposal object', true);
        $this->registerArgument('settings', 'array', 'extension settings', true);
        $this->registerArgument('constants', 'array', 'extension constant', true);
    }

    /**
     * @param $value string value of answer/may be option key
     * @param $options array all options
     * @param $multiple bool value as json?
     *
     * @return string
     */
    public function render()
    {
        $access = 0;
        /**
         * <v:variable.set name="UserGroupAccess" value="0" />
         * <f:for each="{proposal.call.usergroup}" as="callUsergroup">
         *  <f:if condition="{settings.testerFeGroupsId} == {callUsergroup.uid} || 3 == {callUsergroup.uid}">
         *      <f:for each="{proposal.applicant.usergroup}" as="applicantUsergroup">
         *          <f:if condition="{applicantUsergroup.uid} == {callUsergroup.uid}">
         *              <v:variable.set name="UserGroupAccess" value="1" />
         *          </f:if>
         *      </f:for>
         *  </f:if>
         * </f:for>
         *
         * <v:variable.set name="Access" value="0" />
         * <f:if condition="({proposal.state} < {constants.PROPOSAL_SUBMITTED} || {proposal.state} == {constants.PROPOSAL_RE_OPENED}) && ({UserGroupAccess} || ({nowTimestamp} > {proposal.call.callStartTime.timestamp} && {nowTimestamp} < {proposal.call.callEndTime.timestamp}))">
         *  <v:variable.set name="Access" value="1" />
         * </f:if>
         */

        /** @var Proposal $proposal */
        $proposal = $this->arguments['proposal'];
        $call = $proposal->getCall();
        if (!$call) {
            return $access;
        }
        $applicant = $proposal->getApplicant();
        if (!$applicant) {
            return $access;
        }

        $settings = $this->arguments['settings'];
        $constants = $this->arguments['constants'];

//        DebuggerUtility::var_dump($proposal,(string)__LINE__);
//        DebuggerUtility::var_dump($call->getUsergroup(),(string)__LINE__);
//        DebuggerUtility::var_dump($call->getCallStartTime()->getTimestamp(),(string)__LINE__);
//        DebuggerUtility::var_dump($call->getCallEndTime()->getTimestamp(),(string)__LINE__);
//        DebuggerUtility::var_dump($applicant->getUsergroup(),(string)__LINE__);
//        DebuggerUtility::var_dump($settings,(string)__LINE__);
        $generalUserGroups = explode(',', $settings['generalFeGroupsId']);
//        DebuggerUtility::var_dump($generalUserGroups,(string)__LINE__);
        $callGroups = [];
        $groupAccess = false;
        foreach ($call->getUsergroup() as $callUserGroup) {
            if (!in_array($callUserGroup->getUid(), $generalUserGroups)) {
                $callGroups[] = $callUserGroup->getUid();
            }
        }
//        DebuggerUtility::var_dump($callGroups,(string)__LINE__);
//        DebuggerUtility::var_dump($applicant->getUsergroup(),(string)__LINE__);
        foreach ($applicant->getUsergroup() as $applicantUserGroup) {
//            DebuggerUtility::var_dump($applicantUserGroup->getUid(),(string)__LINE__);
            if (in_array($applicantUserGroup->getUid(), $callGroups)) {
                $groupAccess = true;
            }
        }
//        DebuggerUtility::var_dump($groupAccess,(string)__LINE__);
        $stateAccess = ($proposal->getState() < $constants['PROPOSAL_SUBMITTED'] or $proposal->getState() == $constants['PROPOSAL_RE_OPENED']);

        $callStartTS = 0;
        if ($call->getCallStartTime()) {
            $callStartTS = $call->getCallStartTime()->getTimestamp();
        }
        $callEndTS = 0;
        if ($call->getCallEndTime()) {
            $callEndTS = $call->getCallEndTime()->getTimestamp();
        }

        $timeAccess = (time() > $callStartTS and time() < $callEndTS);
//        DebuggerUtility::var_dump(time().' '.$call->getCallStartTime()->getTimestamp().' '.,(string)__LINE__);
//        DebuggerUtility::var_dump($constants,(string)__LINE__);
//        DebuggerUtility::var_dump($stateAccess,(string)__LINE__);
//        DebuggerUtility::var_dump($groupAccess,(string)__LINE__);
//        DebuggerUtility::var_dump($timeAccess,(string)__LINE__);
//        DebuggerUtility::var_dump($stateAccess and ($groupAccess or $timeAccess),(string)__LINE__);
        return ($stateAccess and ($groupAccess or $timeAccess)) ? 1 : 0;
    }
}
