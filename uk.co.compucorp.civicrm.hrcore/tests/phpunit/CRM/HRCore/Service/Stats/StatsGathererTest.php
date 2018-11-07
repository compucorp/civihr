<?php

use CRM_HRCore_CMSData_SiteInformation_SiteInformationInterface as SiteInformationInterface;
use CRM_HRCore_CMSData_Role_RoleServiceInterface as RoleServiceInterface;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_UFMatch as UFMatchFabricator;
use CRM_Tasksassignments_Test_Fabricator_Task as TaskFabricator;
use CRM_Tasksassignments_Test_Fabricator_Document as DocumentFabricator;
use CRM_Tasksassignments_Test_Fabricator_Assignment as AssignmentFabricator;
use CRM_HRCore_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_HRCore_Test_Fabricator_ContactType as ContactTypeFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;
use CRM_HRCore_Service_Stats_StatsGatherer as StatsGatherer;
use CRM_HRCore_Test_Helpers_SessionHelper as SessionHelper;
use CRM_Hrjobroles_Test_Fabricator_HrJobRoles as HrJobRolesFabricator;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;

/**
 * @group headless
 */
class CRM_HRCore_Service_Stats_StatsGathererTest extends CRM_HRCore_Test_BaseHeadlessTest {

  use CRM_HRCore_Test_Helpers_TableCleanupTrait;
  use CRM_HRCore_Test_Helpers_DomainConfigurationTrait;

  public function setUp() {
    // Delete default absence periods created during the extension installation
    $absencePeriodTable = AbsencePeriod::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$absencePeriodTable}");
  }

  /**
   * Used in setup method for leave request fabrication
   *
   * @var int
   */
  private $absenceTypeID;

  public function testSiteNameWillMatchNameFromSiteInfoService() {
    $gatherer = $this->getGatherer();
    $stats = $gatherer->gather();

    $this->assertEquals('foo', $stats->getSiteName());
  }

  public function testGenerationDateWillBeToday() {
    $gatherer = $this->getGatherer();
    $stats = $gatherer->gather();
    $today = new \DateTime();
    $comparisonFormat = 'Y-m-d';
    $todayFmt = $today->format($comparisonFormat);
    $generatedFmt = $stats->getGenerationDate()->format($comparisonFormat);

    $this->assertEquals($todayFmt, $generatedFmt);
  }

  public function testURLWillMatchCiviCRMConfigBaseURL() {
    $gatherer = $this->getGatherer();
    $stats = $gatherer->gather();
    $expected = CRM_Core_Config::singleton()->userFrameworkBaseURL;

    $this->assertEquals($expected, $stats->getSiteUrl());
  }

  public function testContactCountWillMatchExpectedCount() {
    $existingContactCount = civicrm_api3('Contact', 'getcount');
    ContactFabricator::fabricate();
    ContactFabricator::fabricate();
    ContactFabricator::fabricate();
    $expectedContactCount = $existingContactCount + 3;
    $stats = $this->getGatherer()->gather();

    $this->assertEquals($expectedContactCount, $stats->getEntityCount('contact'));
  }

  public function testCMSUserCountWillMatchExpectedCount() {
    $stats = $this->getGatherer()->gather();
    $this->assertEquals(20, $stats->getEntityCount('cmsUser'));
  }

  public function testTaskCountWillMatchExpectedCounts() {
    $this->setDomainFromAddress('test@test.com', 'Test');
    $contactID = ContactFabricator::fabricateWithEmail()['id'];
    $params = ['component_id' => 'CiviTask', 'option_group_id' => 'activity_type'];
    $taskType = OptionValueFabricator::fabricate($params);
    $params = [
      'source_contact_id' => $contactID,
      'target_contact_id' => $contactID,
      'activity_type_id' => $taskType['value'],
    ];
    TaskFabricator::fabricate($params);

    $stats = $this->getGatherer()->gather();

    $this->assertEquals(1, $stats->getEntityCount('task'));
  }

  public function testCaseTypeCountWillMatchExpectedCount() {
    CaseTypeFabricator::fabricate();
    CaseTypeFabricator::fabricate(['name' => 'test_case_type_2']);

    $stats = $this->getGatherer()->gather();

    $this->assertEquals(2, $stats->getEntityCount('caseType'));
  }

  public function testAssignmentCountsWillMatchExpectedCount() {
    $caseType = CaseTypeFabricator::fabricate();
    AssignmentFabricator::fabricate(['case_type_id' => $caseType['id']]);

    $stats = $this->getGatherer()->gather();

    $this->assertEquals(1, $stats->getEntityCount('assignment'));
  }

  public function testDocumentCountWillMatchExpectedCount() {
    $contactId = ContactFabricator::fabricate()['id'];
    $documentType = OptionValueFabricator::fabricate([
      'option_group_id' => 'activity_type',
      'component_id' => 'CiviDocument',
    ]);
    $params['activity_type_id'] = $documentType['value'];
    $params['target_contact_id'] = $contactId;
    $params['source_contact_id'] = $contactId;
    DocumentFabricator::fabricate($params);
    DocumentFabricator::fabricate($params);

    $stats = $this->getGatherer()->gather();

    $this->assertEquals(2, $stats->getEntityCount('document'));
  }

  public function testLeaveRequestCountsWillMatchExpectedCount() {
    $contactID = ContactFabricator::fabricateWithEmail()['id'];

    $this->setUpLeaveRequest($contactID);
    $this->fabricateLeaveRequest($contactID);
    $this->fabricateLeaveRequest($contactID);

    $stats = $this->getGatherer()->gather();

    $this->assertEquals(2, $stats->getEntityCount('leaveRequest'));
  }

  public function testJobRoleCountWillMatchExpectedCount() {
    $contactID = ContactFabricator::fabricateWithEmail()['id'];

    $params = ['contact_id' => $contactID];
    $contract = HRJobContractFabricator::fabricate($params);
    $params = ['job_contract_id' => $contract['id']];
    HrJobRolesFabricator::fabricate($params);
    HrJobRolesFabricator::fabricate($params);

    $stats = $this->getGatherer()->gather();

    $this->assertEquals(2, $stats->getEntityCount('jobRole'));
  }

  public function testCostCenterCountWillMatchExpectedCount() {
    $existingCostCenterCount = civicrm_api3('OptionValue', 'getcount', [
      'option_group_id' => 'cost_centres'
    ]);
    OptionValueFabricator::fabricate([
      'option_group_id' => 'cost_centres',
      'name' => 'Test Cost Center'
    ]);
    $costCenterCount = $existingCostCenterCount + 1;

    $stats = $this->getGatherer()->gather();

    $this->assertEquals($costCenterCount, $stats->getEntityCount('costCenter'));
  }

  public function testLeaveRequestInLast100DaysCountMatchesExpectedCount() {
    $contactID = ContactFabricator::fabricateWithEmail()['id'];
    $this->setUpLeaveRequest($contactID);

    // in last 100 days
    $this->fabricateLeaveRequest($contactID, 'today', 'tomorrow');
    $this->fabricateLeaveRequest($contactID, '-3 days', '-2 days');
    $this->fabricateLeaveRequest($contactID, '-100 days', '-97 days');

    // in future
    $this->fabricateLeaveRequest($contactID, '+1 days', '+2 days');
    $this->fabricateLeaveRequest($contactID, '+10 days', '+20 days');

    // in distant past
    $this->fabricateLeaveRequest($contactID, '-101 days', '-101 days');
    $this->fabricateLeaveRequest($contactID, '-200 days', '-198 days');
    $this->fabricateLeaveRequest($contactID, '-250 days', '-248 days');
    $this->fabricateLeaveRequest($contactID, '-260 days', '-256 days');

    $stats = $this->getGatherer()->gather();

    $this->assertEquals(9, $stats->getEntityCount('leaveRequest'));
    $this->assertEquals(3, $stats->getEntityCount('leaveRequestInLast100Days'));
  }

  public function testContactSubtypeCountWillMatchExpectedCount() {
    $this->truncateTables(['civicrm_contact']);

    ContactTypeFabricator::fabricate(['name' => 'Cat']);
    ContactTypeFabricator::fabricate(['name' => 'Dog']);

    ContactTypeFabricator::fabricate([
      'name' => 'CatClub',
      'parent_id' => 'Organization'
    ]);

    ContactTypeFabricator::fabricate([
      'name' => 'DogClub',
      'parent_id' => 'Organization'
    ]);

    ContactFabricator::fabricate([
      'contact_sub_type' => 'Cat',
      'first_name' => 'Mittens',
      'last_name' => 'Meow'
    ]);

    ContactFabricator::fabricate([
      'contact_sub_type' => 'Cat',
      'first_name' => 'Felix',
      'last_name' => 'da Housecat'
    ]);

    ContactFabricator::fabricate([
      'contact_sub_type' => 'CatClub',
      'contact_type' => 'Organization',
      'organization_name' => 'Kool Katz Klub',
    ]);

    ContactFabricator::fabricate([
      'contact_sub_type' => 'Dog',
      'first_name' => 'Lassie',
      'last_name' => 'Come Home'
    ]);

    ContactFabricator::fabricate([
      'contact_sub_type' => 'DogClub',
      'contact_type' => 'Organization',
      'organization_name' => 'Dope Dogz',
    ]);

    $stats = $this->getGatherer()->gather();

    $organizationCount = $stats->getContactSubtypeCount('Organization');
    $individualCount = $stats->getContactSubtypeCount('Individual');

    $this->assertEquals(1, $stats->getContactSubtypeCount('DogClub'));
    $this->assertEquals(1, $stats->getContactSubtypeCount('Dog'));
    $this->assertEquals(1, $stats->getContactSubtypeCount('CatClub'));
    $this->assertEquals(2, $stats->getContactSubtypeCount('Cat'));
    $this->assertEquals(2, $organizationCount);
    $this->assertEquals(3, $individualCount);
    $this->assertEquals(5, $stats->getEntityCount('contact'));
  }

  public function testDeletedEntitiesWillNotBeIncluded() {
    $this->truncateTables(['civicrm_contact']);
    $contactID = ContactFabricator::fabricate()['id'];
    $this->setUpLeaveRequest($contactID);
    SessionHelper::registerCurrentLoggedInContactInSession($contactID);

    $ufMatch = UFMatchFabricator::fabricate();
    civicrm_api3('UFMatch', 'delete', ['id' => $ufMatch['id']]);

    $contact = ContactFabricator::fabricate();
    civicrm_api3('Contact', 'delete', ['id' => $contact['id']]);

    $params = ['component_id' => 'CiviTask', 'option_group_id' => 'activity_type'];
    $taskType = OptionValueFabricator::fabricate($params);
    $params = [
      'source_contact_id' => $contactID,
      'target_contact_id' => $contactID,
      'activity_type_id' => $taskType['value'],
    ];
    $task = TaskFabricator::fabricate($params);
    civicrm_api3('Task', 'delete', ['id' => $task['id']]);

    $caseType = CaseTypeFabricator::fabricate();
    $assignmentParams = ['case_type_id' => $caseType['id']];
    $assignment = AssignmentFabricator::fabricate($assignmentParams);
    civicrm_api3('Assignment', 'delete', ['id' => $assignment->id]);

    $document = DocumentFabricator::fabricate($params);
    civicrm_api3('Document', 'delete', ['id' => $document->id]);

    $leaveRequest = $this->fabricateLeaveRequest($contactID);
    civicrm_api3('LeaveRequest', 'delete', ['id' => $leaveRequest->id]);

    $stats = $this->getGatherer()->gather();

    $this->assertEquals(1, $stats->getEntityCount('contact'));
    $this->assertEquals(0, $stats->getEntityCount('task'));
    $this->assertEquals(0, $stats->getEntityCount('assignment'));
    $this->assertEquals(0, $stats->getEntityCount('document'));
    $this->assertEquals(0, $stats->getEntityCount('leaveRequest'));
    $this->assertEquals(0, $stats->getEntityCount('vacancy'));
  }

  public function testLatestLoginWillBeSet() {
    $stats = $this->getGatherer()->gather();
    $login = $stats->getMostRecentLoginByRole('fake_role');
    $comparisonFormat = 'Y-m-d H:i';
    $now = new \DateTime();
    $this->assertEquals(
      $now->format($comparisonFormat),
      $login->format($comparisonFormat)
    );
  }

  public function testInactiveCaseTypesWillNotBeIncluded() {
    CaseTypeFabricator::fabricate(['is_active' => 0]);
    CaseTypeFabricator::fabricate(['name' => 'test_case_type_2']);

    $stats = $this->getGatherer()->gather();

    $this->assertEquals(1, $stats->getEntityCount('caseType'));
  }

  /**
   * @return StatsGatherer
   */
  private function getGatherer() {
    $siteInformation = $this->prophesize(SiteInformationInterface::class);
    $siteInformation->getSiteName()->willReturn('foo');
    $siteInformation->getActiveUserCount()->willReturn(20);

    $roleService = $this->prophesize(RoleServiceInterface::class);
    $roleService->getRoleNames()->willReturn([1 => 'fake_role']);
    $roleService->getLatestLoginByRole()->willReturn([
      'fake_role' => new \DateTime()
    ]);

    return new StatsGatherer(
      $siteInformation->reveal(),
      $roleService->reveal()
    );
  }

  /**
   * @param int $contactID
   * @param string $startDateString
   * @param string $endDateString
   *
   * @return CRM_HRLeaveAndAbsences_BAO_LeaveRequest
   */
  private function fabricateLeaveRequest(
    $contactID,
    $startDateString = 'today',
    $endDateString = 'tomorrow'
  ) {
    return LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contactID,
      'type_id' => $this->absenceTypeID,
      'from_date' => CRM_Utils_Date::processDate($startDateString),
      'to_date' => CRM_Utils_Date::processDate($endDateString),
      'status_id' => 1
    ], TRUE);
  }

  /**
   * @param $contactID
   */
  private function setUpLeaveRequest($contactID) {
    $periodStartDate = CRM_Utils_Date::processDate('-1 year');
    $periodEndDate = CRM_Utils_Date::processDate('+1 year');
    $this->absenceTypeID = AbsenceTypeFabricator::fabricate()->id;
    $absencePeriodID = AbsencePeriodFabricator::fabricate([
      'start_date' => $periodStartDate,
      'end_date' => $periodEndDate
    ])->id;
    HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => $periodStartDate]
    );
    LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactID,
      'period_id' => $absencePeriodID,
      'type_id' => $this->absenceTypeID
    ]);
  }

}
