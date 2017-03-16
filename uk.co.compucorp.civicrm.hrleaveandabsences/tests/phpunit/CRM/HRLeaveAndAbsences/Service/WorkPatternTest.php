<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Service_WorkPattern as WorkPatternService;


/**
 * Class CRM_HRLeaveAndAbsences_Service_WorkPatternTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_WorkPatternTest extends BaseHeadlessTest {

  private $workPatternService;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $this->workPatternService = new WorkPatternService();
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 1;');
  }

  public function testWorkPatternHasEverBeenUsedReturnsTrueWhenWorkPatternIsTheDefaultWorkPattern() {
    $workPattern = WorkPatternFabricator::fabricate(['is_default' => 1]);
    $this->assertTrue($this->workPatternService->workPatternHasEverBeenUsed($workPattern->id));
  }

  public function testWorkPatternHasEverBeenUsedReturnsTrueWhenWorkPatternIsLinkedToAContactWorkPattern() {
    $workPattern = WorkPatternFabricator::fabricate(['is_default' => 0]);

    ContactWorkPatternFabricator::fabricate([
      'pattern_id' => $workPattern->id,
    ]);

    $this->assertTrue($this->workPatternService->workPatternHasEverBeenUsed($workPattern->id));
  }

  public function testWorkPatternHasEverBeenUsedReturnsFalseWhenWorkPatternIsNotTheDefaultAndWorkPatternIsNotLinkedToAContactWorkPattern() {
    $workPattern = WorkPatternFabricator::fabricate(['is_default' => 0]);
    $this->assertFalse($this->workPatternService->workPatternHasEverBeenUsed($workPattern->id));
  }

  /**
   * @expectedException UnexpectedValueException
   * @expectedExceptionMessage Work pattern cannot be deleted because it is used by one or more contacts
   */
  public function testDeleteThrowsAnExceptionWhenAttemptingToDeleteAWorkPatternThatHasBeenUsed() {
    $workPattern = WorkPatternFabricator::fabricate(['is_default' => 1]);
    $this->workPatternService->delete($workPattern->id);
  }

  public function testDeleteCanDeleteAWorkPatternThatIsNotUsed() {
    $workPattern = WorkPatternFabricator::fabricate(['is_default' => 0]);
    $this->workPatternService->delete($workPattern->id);

    try {
      WorkPattern::findById($workPattern->id);
    } catch(Exception $e) {
      return;
    }
    $this->fail("Expected to not find the WorkPattern with {$workPattern->id}, but it was found");
  }
}
