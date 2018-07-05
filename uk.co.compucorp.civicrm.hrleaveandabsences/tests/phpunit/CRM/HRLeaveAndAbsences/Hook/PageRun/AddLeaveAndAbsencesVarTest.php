<?php

class CRM_HRLeaveAndAbsences_Hook_PageRun_AddLeaveAndAbsencesVarTest extends BaseHeadlessTest {

  public function testAddingVarsToLeaveAndAbsencesPages() {
    $hook = new CRM_HRLeaveAndAbsences_Hook_PageRun_AddLeaveAndAbsencesVar();
    $page = new CRM_HRLeaveAndAbsences_Page_LeaveRequestCalendarFeedConfig();
    $expectedVars = [
      'leaveAndAbsences' => [
        'baseURL' => CRM_Core_Resources::singleton()->getUrl('uk.co.compucorp.civicrm.hrleaveandabsences'),
        'contactId' => CRM_Utils_Request::retrieve('cid', 'Integer'),
        'loggedInUserId' => CRM_Core_Session::getLoggedInContactID(),
      ]
    ];

    CRM_Core_Resources::singleton()->addVars('leaveAndAbsences', []);
    $hook->handle($page);

    $actualVars = CRM_Core_Resources::singleton()->getSettings()['vars'];

    $this->assertRegexp('/[A-z0-9_;]+/', $actualVars['leaveAndAbsences']['attachmentToken']);

    unset($actualVars['leaveAndAbsences']['attachmentToken']);

    $this->assertEquals($expectedVars, $actualVars);
  }

}
