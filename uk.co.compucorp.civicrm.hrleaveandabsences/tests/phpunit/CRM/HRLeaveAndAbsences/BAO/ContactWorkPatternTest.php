<?php

use CRM_HRLeaveAndAbsences_BAO_ContactWorkPattern as ContactWorkPattern;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_ContactWorkPatternTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_ContactWorkPatternTest extends BaseHeadlessTest {

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
    $periodStartDate = date('2016-01-01');

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
    $date= new DateTime('2016-01-20');

    $returnedWorkPattern = ContactWorkPattern::getWorkPattern($contact['id'], $date);
    $this->assertEquals($workPattern->id, $returnedWorkPattern->id);
  }

  public function testGetContactWorkPatternStartDate() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = new DateTime('2016-01-01');

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate->format('Y-m-d'),
    ]);
    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id
    ]);
    $date= new DateTime('2016-01-20');

    $returnedStartDate = ContactWorkPattern::getStartDate($contact['id'], $date);
    $this->assertEquals($periodStartDate, $returnedStartDate);
  }
}
