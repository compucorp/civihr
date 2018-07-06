<?php

/**
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Hook_PageRun_AddLeaveAndAbsencesVarTest extends BaseHeadlessTest {

  public function testAddingVarsToLeaveAndAbsencesPages() {
    $resources = new MockCoreResources();
    $hook = new CRM_HRLeaveAndAbsences_Hook_PageRun_AddLeaveAndAbsencesVar($resources);
    $page = new CRM_HRLeaveAndAbsences_Page_LeaveRequestCalendarFeedConfig();
    $expectedVars = [
      'leaveAndAbsences' => [
        'baseURL' => CRM_Core_Resources::singleton()->getUrl('uk.co.compucorp.civicrm.hrleaveandabsences'),
        'contactId' => CRM_Utils_Request::retrieve('cid', 'Integer'),
        'loggedInUserId' => CRM_Core_Session::getLoggedInContactID(),
      ]
    ];

    $hook->handle($page);

    $actualVars = $resources->exposedVars;
    $attachmentToken = $actualVars['leaveAndAbsences']['attachmentToken'];

    unset($actualVars['leaveAndAbsences']['attachmentToken']);

    $this->assertRegexp('/[A-z0-9_;]+/', $attachmentToken);
    $this->assertEquals($expectedVars, $actualVars);
  }

  public function testItWillNotAddVarsToPagesItShouldNotHandle() {
    $resources = new MockCoreResources();
    $hook = new CRM_HRLeaveAndAbsences_Hook_PageRun_AddLeaveAndAbsencesVar($resources);
    $page = new CRM_Core_Page();

    $hook->handle($page);

    $this->assertEquals([], $resources->exposedVars);
  }

}

/**
 * Mocks the Resources class. This method was chosen over Prophesize because
 * static methods can't be mocked and CRM_Core_Page_AJAX_Attachment::createToken
 * creates random data that can't be handled by Prophesize.
 */
class MockCoreResources extends CRM_Core_Resources {

  public $exposedVars = [];

  public function __construct() {}

  /**
   * Adds the key-value pair to an exposed vars that can be used for testing purposes.
   *
   * @param string $key
   * @param array $value
   */
  public function addVars($key, $value) {
    $this->exposedVars[$key] = $value;
  }

}
