<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;
use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_ContactWorkPatternTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_ContactWorkPatternTest extends BaseHeadlessTest {

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
  }

  public function testThereCannotBeTwoWorkPatternsForTheSameEmployeeWithTheSameEffectiveDate() {
    $workPattern1 = WorkPatternFabricator::fabricate();
    $workPattern2 = WorkPatternFabricator::fabricate();

    $effectiveDate = CRM_Utils_Date::processDate('2016-01-01');

    ContactWorkPattern::create([
      'contact_id' => 2,
      'pattern_id' => $workPattern1->id,
      'effective_date' => $effectiveDate,
    ]);

    try {
      ContactWorkPattern::create([
        'contact_id' => 2,
        'pattern_id' => $workPattern2->id,
        'effective_date' => $effectiveDate,
      ]);
    } catch(PEAR_Exception $e) {
      $this->assertEquals('DB Error: already exists', $e->getMessage());

      return;
    }

    $this->fail('Expected an DB error, but the contact work patternx was created successfully');
  }

  public function testTheEffectiveEndDateShouldBeAutomaticallyUpdatedWhenANewWorkPatternIsLinkedToAnEmployee() {
    $workPattern1 = WorkPatternFabricator::fabricate();

    $contactWorkPattern1 = ContactWorkPattern::create([
      'contact_id' => 2,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-01-01'),
    ]);

    $contactWorkPattern1 = ContactWorkPattern::findById($contactWorkPattern1->id);
    $this->assertNull($contactWorkPattern1->effective_end_date);

    $contactWorkPattern2 = ContactWorkPattern::create([
      'contact_id' => 2,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-04-02'),
    ]);

    $contactWorkPattern1 = ContactWorkPattern::findById($contactWorkPattern1->id);
    $this->assertEquals('2016-04-01', $contactWorkPattern1->effective_end_date);

    $contactWorkPattern2 = ContactWorkPattern::findById($contactWorkPattern2->id);
    $this->assertNull($contactWorkPattern2->effective_end_date);
  }

  public function testGetForDateReturnsTheActiveWorkPatternForAnEmployeeAtTheGivenDate() {
    $workPattern1 = WorkPatternFabricator::fabricate();
    $workPattern2 = WorkPatternFabricator::fabricate();
    $workPattern3 = WorkPatternFabricator::fabricate();

    $contactID = 2;

    ContactWorkPattern::create([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-01-01'),
    ]);

    ContactWorkPattern::create([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern2->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-07-23'),
    ]);

    ContactWorkPattern::create([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern3->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-12-15'),
    ]);

    $contactWorkPattern = ContactWorkPattern::getForDate($contactID, new DateTime('2016-03-25'));
    $this->assertEquals($workPattern1->id, $contactWorkPattern->pattern_id);

    $contactWorkPattern = ContactWorkPattern::getForDate($contactID, new DateTime('2016-07-23'));
    $this->assertEquals($workPattern2->id, $contactWorkPattern->pattern_id);

    $contactWorkPattern = ContactWorkPattern::getForDate($contactID, new DateTime('2017-07-23'));
    $this->assertEquals($workPattern3->id, $contactWorkPattern->pattern_id);
  }

  public function testGetForDateReturnsNullIfTheEmployeeHasNoWorkPatterns() {
    $contactWorkPattern = ContactWorkPattern::getForDate(2, new DateTime('2016-03-25'));
    $this->assertNull($contactWorkPattern);
  }

  public function testGetForDateReturnsNullIfTheEmployeeHasWorkPatternsButTheGivenDateDoesNotOverlapAny() {
    $workPattern1 = WorkPatternFabricator::fabricate();
    $workPattern2 = WorkPatternFabricator::fabricate();

    $contactID = 2;

    ContactWorkPattern::create([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-01-01'),
    ]);

    ContactWorkPattern::create([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern2->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-07-23'),
    ]);

    // The given date is before the effective date of the first work pattern, so
    // none will be returned
    $contactWorkPattern = ContactWorkPattern::getForDate($contactID, new DateTime('2015-12-31'));
    $this->assertNull($contactWorkPattern);
  }

  public function testGetWorkPatternForContact() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = new DateTime('2016-01-01');

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact['id'] ],
      [ 'period_start_date' => $periodStartDate->format('Y-m-d') ]
    );

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id,
      'effective_date' => $periodStartDate->format('YmdHis')
    ]);

    $date = new DateTime('2016-01-20');
    $returnedWorkPattern = ContactWorkPattern::getWorkPattern($contact['id'], $date);
    $this->assertEquals($workPattern->id, $returnedWorkPattern->id);
  }

  public function testGetWorkPatternForContactReturnsDefaultWorkPatternWhenContactHasNoWorkPatternAssigned() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact['id'] ],
      [ 'period_start_date' => $periodStartDate ]);

    $defaultWorkPattern = WorkPatternFabricator::fabricate(['is_default' => 1]);

    $date = new DateTime('2016-01-20');
    $returnedWorkPattern = ContactWorkPattern::getWorkPattern($contact['id'], $date);
    $this->assertEquals($defaultWorkPattern->id, $returnedWorkPattern->id);
  }

  public function testGetWorkPatternForContactReturnsRightWorkPatternWhenContactHasMultipleWorkPattern() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
    ]);
    $workPattern1 = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    $workPattern2 = WorkPatternFabricator::fabricateWithA40HourWorkWeek();

    $startDateContactPattern1 = new DateTime('2016-01-30');
    $startDateContactPattern2 = new DateTime('2016-04-01');
    $endDateContactPattern1 = new DateTime('2016-03-30');
    $endDateContactPattern2 = new DateTime('2016-11-30');

    //this contact work pattern will expire march ending 2016
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern1->id,
      'effective_date' => $startDateContactPattern1->format('YmdHis'),
      'effective_end_date' => $endDateContactPattern1->format('YmdHis')
    ]);

    //contract valid till 2016-11-30
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern2->id,
      'effective_date' => $startDateContactPattern2->format('YmdHis'),
      'effective_end_date' => $endDateContactPattern2->format('YmdHis')
    ]);

    $date = new DateTime('2016-10-20');

    $returnedWorkPattern = ContactWorkPattern::getWorkPattern($contact['id'], $date);
    $this->assertEquals($workPattern2->id, $returnedWorkPattern->id);
  }

  public function testGetWorkPatternForContactReturnsDefaultWorkPatternWhenDateGivenToWorkPatternIsBeforeEffectiveDate() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
    ]);
    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();

    $startDateContactPattern = new DateTime('2016-03-01');
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id,
      'effective_date' => $startDateContactPattern->format('YmdHis'),
    ]);
    $defaultWorkPattern = WorkPatternFabricator::fabricate(['is_default' => 1]);

    $date = new DateTime('2016-01-20');

    $returnedWorkPattern = ContactWorkPattern::getWorkPattern($contact['id'], $date);
    $this->assertEquals($defaultWorkPattern->id, $returnedWorkPattern->id);
  }

  public function testGetContactWorkPatternStartDateWhenContactHasOneWorkPattern() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = new DateTime('2016-01-01');

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate->format('Y-m-d'),
    ]);

    $startDateContactPattern = new DateTime('2016-04-01');
    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id,
      'effective_date' => $startDateContactPattern->format('YmdHis'),
    ]);
    $date = new DateTime('2016-05-20');

    $returnedStartDate = ContactWorkPattern::getStartDate($contact['id'], $date);
    $this->assertEquals($startDateContactPattern, $returnedStartDate);
  }

  public function testGetContactWorkPatternStartDateReturnsTheRightDateWhenContactHasMultipleWorkPattern() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
    ]);
    $workPattern1 = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    $workPattern2 = WorkPatternFabricator::fabricateWithA40HourWorkWeek();

    $startDateContactPattern1 = new DateTime('2016-01-30');
    $startDateContactPattern2 = new DateTime('2016-04-01');
    $endDateContactPattern1 = new DateTime('2016-03-30');
    $endDateContactPattern2 = new DateTime('2016-11-30');

    //this contact work pattern will expire march ending 2016
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern1->id,
      'effective_date' => $startDateContactPattern1->format('YmdHis'),
      'effective_end_date' => $endDateContactPattern1->format('YmdHis')
    ]);

    //contract valid till 2016-11-30
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern2->id,
      'effective_date' => $startDateContactPattern2->format('YmdHis'),
      'effective_end_date' => $endDateContactPattern2->format('YmdHis')
    ]);

    $date = new DateTime('2016-10-20');

    $startDate = ContactWorkPattern::getStartDate($contact['id'], $date);
    $this->assertEquals($startDateContactPattern2, $startDate);
  }

  public function testGetContactWorkPatternStartDateReturnsStartDateOfOverlappingContractWhenContactHasNoWorkPattern() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = new DateTime('2016-01-01');

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate->format('Y-m-d'),
    ]);

    $date = new DateTime('2016-01-20');

    $startDate = ContactWorkPattern::getStartDate($contact['id'], $date);
    $this->assertEquals($periodStartDate, $startDate);
  }

  public function testGetContactWorkPatternStartDateReturnsNullWhenContactHasNoWorkPatternAssignedAndNoContractOverlappingDate() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = new DateTime('2016-05-01');

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate->format('Y-m-d'),
    ]);

    $date = new DateTime('2016-01-20');

    $startDate = ContactWorkPattern::getStartDate($contact['id'], $date);
    $this->assertNull($startDate);
  }

  public function testGetWorkDayTypeForSingleWeekWorkPattern() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = '2016-01-01';

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
    ]);
    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id
    ]);

    $startDateTime = new DateTime('2016-11-30');
    $dayType = ContactWorkPattern::getWorkDayType($contact['id'], $startDateTime);
    $this->assertEquals(WorkDay::getWorkingDayTypeValue(), $dayType);

    $startDateTime2 = new DateTime('2016-11-27');
    $dayType = ContactWorkPattern::getWorkDayType($contact['id'], $startDateTime2);
    $this->assertEquals(WorkDay::getWeekendTypeValue(), $dayType);
  }

  public function testGetWorkDayTypeForWorkPatternWithMultipleWeeks() {
    $contact = ContactFabricator::fabricate();
    $periodStartDate = new DateTime('2016-01-01');

    HRJobContractFabricator::fabricate(
      [ 'contact_id' => $contact['id'] ],
      [ 'period_start_date' => $periodStartDate->format('Y-m-d') ]);

    // Week 1 weekdays: monday, wednesday and friday
    // Week 2 weekdays: tuesday and thursday
    $workPattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();

    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id,
      'effective_date' => $periodStartDate->format('YmdHis')
    ]);

    //A sunday which is weekend on week 1
    $startDateTime1 = new DateTime('2016-07-31');
    $dayType = ContactWorkPattern::getWorkDayType($contact['id'], $startDateTime1);
    $this->assertEquals(WorkDay::getWeekendTypeValue(), $dayType);

    // Since the start date is a sunday, the end of the week, the following day
    // (2016-08-01) should be on the second week. Monday of the second week is
    // not a working day
    $startDateTime2 = new DateTime('2016-08-01');
    $dayType = ContactWorkPattern::getWorkDayType($contact['id'], $startDateTime2);
    $this->assertEquals(WorkDay::getNonWorkingDayTypeValue(), $dayType);

    // Now, since we hit sunday, the following day will be on the third week
    // since the start date, but the work pattern only has 2 weeks, so we
    // rotate back to use the week 1 from the pattern
    // Monday is a working day on the first week
    $startDateTime3 = new DateTime('2016-08-08');
    $dayType = ContactWorkPattern::getWorkDayType($contact['id'], $startDateTime3);
    $this->assertEquals(WorkDay::getWorkingDayTypeValue(), $dayType);
  }

  public function getAllForPeriodReturnsAnEmptyArrayIfTheresNoContactWorkPatternForTheGivenPeriod() {
    $contact = ContactFabricator::fabricate();
    $workPattern = WorkPatternFabricator::fabricate();

    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id,
      'effective_date' => CRM_Utils_Date::processDate('2015-01-10'),
    ]);

    $this->assertEquals([], ContactWorkPattern::getAllForPeriod(
      $contact['id'],
      new DateTime('2015-01-01'),
      new DateTime('2015-01-09')
    ));
  }

  public function testGetContactsUsingWorkPatternFromDate() {
    $contactID1 = 1;
    $contactID2 = 2;
    $contactID3 = 3;
    $workPattern1 = 1;
    $workPattern2 = 2;

    $contactWorkPattern1 = ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contactID1,
      'pattern_id' => $workPattern1,
      'effective_date' => CRM_Utils_Date::processDate('2015-01-10'),
    ]);

    $contactWorkPattern2 = ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contactID2,
      'pattern_id' => $workPattern1,
      'effective_date' => CRM_Utils_Date::processDate('2015-01-15'),
    ]);

    $contactWorkPattern3 = ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contactID3,
      'pattern_id' => $workPattern2,
      'effective_date' => CRM_Utils_Date::processDate('2015-01-20'),
    ]);

    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contactID2,
      'pattern_id' => $workPattern1,
      'effective_date' => CRM_Utils_Date::processDate('2015-01-30'),
    ]);

    $contacts = ContactWorkPattern::getContactsUsingWorkPatternFromDate(
      new DateTime('2015-01-14'),
      $workPattern1
    );

    // ContactWork Patterns 1,2 and 3 has effective_end date greater than the '2015-01-14'
    // period but ContactWorkPattern3 is attached to workPattern2,
    // so only unique contacts attached to Contact Work Patterns 1 and 2 will be returned.
    $this->assertCount(2, $contacts);
    sort($contacts);
    $this->assertEquals($contacts, [$contactID1, $contactID2]);
  }
}
