<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPatternAttribution as WorkPatternAttribution;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_WorkPatternAttributionTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_WorkPatternAttributionTest extends BaseHeadlessTest {

  public function testThereCannotBeTwoAttributionsForTheSameEmployeeWithTheSameEffectiveDate() {
    $workPattern1 = WorkPatternFabricator::fabricate();
    $workPattern2 = WorkPatternFabricator::fabricate();

    $effectiveDate = CRM_Utils_Date::processDate('2016-01-01');

    WorkPatternAttribution::create([
      'contact_id' => 2,
      'pattern_id' => $workPattern1->id,
      'effective_date' => $effectiveDate,
    ]);

    try {
      WorkPatternAttribution::create([
        'contact_id' => 2,
        'pattern_id' => $workPattern2->id,
        'effective_date' => $effectiveDate,
      ]);
    } catch(PEAR_Exception $e) {
      $this->assertEquals('DB Error: already exists', $e->getMessage());

      return;
    }

    $this->fail('Expected an DB error, but the attribution was created successfully');
  }

  public function testTheEffectiveEndDateShouldBeAutomaticallyUpdatedWhenANewWorkPatternIsAttributedToAnEmployee() {
    $workPattern1 = WorkPatternFabricator::fabricate();

    $attribution1 = WorkPatternAttribution::create([
      'contact_id' => 2,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-01-01'),
    ]);

    $attribution1 = WorkPatternAttribution::findById($attribution1->id);
    $this->assertNull($attribution1->effective_end_date);

    $attribution2 = WorkPatternAttribution::create([
      'contact_id' => 2,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-04-02'),
    ]);

    $attribution1 = WorkPatternAttribution::findById($attribution1->id);
    $this->assertEquals('2016-04-01', $attribution1->effective_end_date);

    $attribution2 = WorkPatternAttribution::findById($attribution2->id);
    $this->assertNull($attribution2->effective_end_date);
  }

  public function testGetWorkPatternForDateReturnsTheActiveWorkPatternForAnEmployeeAtTheGivenDate() {
    $workPattern1 = WorkPatternFabricator::fabricate();
    $workPattern2 = WorkPatternFabricator::fabricate();
    $workPattern3 = WorkPatternFabricator::fabricate();

    $contactID = 2;

    WorkPatternAttribution::create([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-01-01'),
    ]);

    WorkPatternAttribution::create([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern2->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-07-23'),
    ]);

    WorkPatternAttribution::create([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern3->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-12-15'),
    ]);

    $workPattern = WorkPatternAttribution::getWorkPatternForDate($contactID, new DateTime('2016-03-25'));
    $this->assertEquals($workPattern1->id, $workPattern->id);

    $workPattern = WorkPatternAttribution::getWorkPatternForDate($contactID, new DateTime('2016-07-23'));
    $this->assertEquals($workPattern2->id, $workPattern->id);

    $workPattern = WorkPatternAttribution::getWorkPatternForDate($contactID, new DateTime('2017-07-23'));
    $this->assertEquals($workPattern3->id, $workPattern->id);
  }

  public function testGetWorkPatternForDateReturnsTheDefaultWorkPatternIfTheEmployeeHasNoWorkPatterns() {
    $defaultWorkPattern = WorkPatternFabricator::fabricate(['is_default' => 1]);
    WorkPatternFabricator::fabricate();

    $workPattern = WorkPatternAttribution::getWorkPatternForDate(2, new DateTime('2016-03-25'));
    $this->assertEquals($defaultWorkPattern->id, $workPattern->id);

    $workPattern = WorkPatternAttribution::getWorkPatternForDate(2, new DateTime('2017-11-01'));
    $this->assertEquals($defaultWorkPattern->id, $workPattern->id);

    $workPattern = WorkPatternAttribution::getWorkPatternForDate(2, new DateTime('2013-01-01'));
    $this->assertEquals($defaultWorkPattern->id, $workPattern->id);
  }

  public function testGetWorkPatternForDateReturnsTheDefaultWorkPatternIfTheEmployeeHasWorkPatternsButTheGivenDateDoesNotOverlapAny() {
    $defaultWorkPattern = WorkPatternFabricator::fabricate(['is_default' => 1]);
    $workPattern1 = WorkPatternFabricator::fabricate();
    $workPattern2 = WorkPatternFabricator::fabricate();

    $contactID = 2;

    WorkPatternAttribution::create([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-01-01'),
    ]);

    WorkPatternAttribution::create([
      'contact_id' => $contactID,
      'pattern_id' => $workPattern2->id,
      'effective_date' => CRM_Utils_Date::processDate('2016-07-23'),
    ]);

    // The given date is before the effective date of the first work pattern, so
    // the default one will be returned
    $workPattern = WorkPatternAttribution::getWorkPatternForDate($contactID, new DateTime('2015-12-31'));
    $this->assertEquals($defaultWorkPattern->id, $workPattern->id);
  }

}
