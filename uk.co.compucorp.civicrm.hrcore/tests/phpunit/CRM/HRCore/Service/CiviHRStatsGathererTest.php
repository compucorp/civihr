<?php

use CRM_HRCore_Service_CiviHRStatsGatherer as CiviHRStatsGatherer;
use CRM_HRCore_CMSData_SiteInformation_SiteInformationInterface as SiteInformationInterface;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_UFMatch as UFMatchFabricator;
use CRM_HRRecruitment_Test_Fabricator_HRVacancy as HRVacancyFabricator;
use CRM_Tasksassignments_Test_Fabricator_TaskType as TaskTypeFabricator;
use CRM_Tasksassignments_Test_Fabricator_Task as TaskFabricator;
use CRM_Tasksassignments_Test_Fabricator_Document as DocumentFabricator;
use CRM_Tasksassignments_Test_Fabricator_Assignment as AssignmentFabricator;
use CRM_HRCore_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_HRCore_Test_Fabricator_ContactType as ContactTypeFabricator;

/**
 * @group headless
 */
class CiviHRStatsGathererTest extends CRM_HRCore_Test_BaseHeadlessTest {

  use CRM_HRCore_Test_Helpers_SessionHelpersTrait;

  /**
   * Used in setup method for leave request fabrication
   *
   * @var int
   */
  private $absenceTypeID;

  public function setUpHeadless() {
    $requiredExtensions = [
      'uk.co.compucorp.civicrm.tasksassignments',
      'org.civicrm.hrrecruitment',
      'uk.co.compucorp.civicrm.hrleaveandabsences',
      'org.civicrm.hrjobcontract', // L&A depends on HRJobContract
    ];

    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->install($requiredExtensions)
      ->apply();
  }

  public function testSiteNameWillMatchNameFromSiteInfoService() {
    $gatherer = $this->getGatherer();
    $stats = $gatherer->gather();
    $this->getGatherer();

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

  public function testEntityCountsWillMatchExpectedCount() {
    $existingContactCount = $this->getCurrentContactCount();

    // 2 : document fabricator creates a fabricated contact on each call
    // 3 : created in this test
    // expect 5 + existing Contacts
    ContactFabricator::fabricate();
    ContactFabricator::fabricate();
    $contactID = ContactFabricator::fabricateWithEmail()['id'];
    $this->registerCurrentLoggedInContactInSession($contactID);

    // expect 1 UFMatch
    UFMatchFabricator::fabricate(['contact_id' => $contactID]);

    // expect 2 Vacancies
    HRVacancyFabricator::fabricate();
    HRVacancyFabricator::fabricate();

    // expect 1 Task
    TaskTypeFabricator::fabricate();
    TaskFabricator::fabricate();

    // expect 1 Assignment
    CaseTypeFabricator::fabricate();
    AssignmentFabricator::fabricate();

    // expect 2 Documents
    DocumentFabricator::fabricate();
    DocumentFabricator::fabricate();

    // expect 2 LeaveRequests
    $this->setUpLeaveRequest($contactID);
    $this->fabricateLeaveRequest($contactID);
    $this->fabricateLeaveRequest($contactID);

    $stats = $this->getGatherer()->gather();

    $expectedContacts = 5 + $existingContactCount;
    $this->assertEquals($expectedContacts, $stats->getEntityCount('contact'));
    $this->assertEquals(1, $stats->getEntityCount('drupalUser'));
    $this->assertEquals(2, $stats->getEntityCount('vacancy'));
    $this->assertEquals(1, $stats->getEntityCount('task'));
    $this->assertEquals(1, $stats->getEntityCount('assignment'));
    $this->assertEquals(2, $stats->getEntityCount('document'));
    $this->assertEquals(2, $stats->getEntityCount('leaveRequest'));
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
    $existingContactCount = $this->getCurrentContactCount();
    $existingOrganizationCount = $this->getCurrentContactCount(
      ['contact_type' => 'Organization']
    );
    $existingIndividialCount = $this->getCurrentContactCount(
      ['contact_type' => 'Individual']
    );

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

    $expectedContacts = $existingContactCount + 5;
    $expectedOrgs = $existingOrganizationCount + 2;
    $expectedIndividuals = $existingIndividialCount + 3;
    $organizationCount = $stats->getContactSubtypeCount('Organization');
    $individualCount = $stats->getContactSubtypeCount('Individual');

    $this->assertEquals(1, $stats->getContactSubtypeCount('DogClub'));
    $this->assertEquals(1, $stats->getContactSubtypeCount('Dog'));
    $this->assertEquals(1, $stats->getContactSubtypeCount('CatClub'));
    $this->assertEquals(2, $stats->getContactSubtypeCount('Cat'));
    $this->assertEquals($expectedOrgs, $organizationCount);
    $this->assertEquals($expectedIndividuals, $individualCount);
    $this->assertEquals($expectedContacts, $stats->getEntityCount('contact'));
  }

  public function testDeletedEntitiesWillNotBeIncluded() {
    $existingContactCount = $this->getCurrentContactCount();
    $deletedContact = ContactFabricator::fabricate();
    $deletedContactID = $deletedContact['id'];
    civicrm_api3('Contact', 'delete', ['id' => $deletedContactID]);
    $params = ['is_deleted' => 1, 'id' => $deletedContactID];
    $stillExists = civicrm_api3('Contact', 'getcount', $params) == 1;

    if (!$stillExists) {
      $this->markTestSkipped('There was an error in soft deletion of contact');
    }

    $stats = $this->getGatherer()->gather();

    $this->assertEquals($existingContactCount, $stats->getEntityCount('contact'));
  }

  /**
   * @return CRM_HRCore_Service_CiviHRStatsGatherer
   */
  private function getGatherer() {
    $siteInformation = $this->prophesize(SiteInformationInterface::class);
    $siteInformation->getSiteName()->willReturn('foo');

    return new CiviHRStatsGatherer($siteInformation->reveal());
  }

  /**
   * @param int $contactID
   * @param string $startDateString
   * @param string $endDateString
   */
  private function fabricateLeaveRequest(
    $contactID,
    $startDateString = 'today',
    $endDateString = 'tomorrow'
  ) {
    LeaveRequestFabricator::fabricateWithoutValidation([
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

  /**
   * Gets the number of active contacts now
   *
   * @param array $params
   *
   * @return int
   */
  private function getCurrentContactCount($params = []) {
    $params['is_deleted'] = 0;

    return (int) civicrm_api3('Contact', 'getcount', $params);
  }
}
