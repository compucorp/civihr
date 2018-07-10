<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfig as LeaveRequestCalendarFeedConfig;
use CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException as InvalidLeaveRequestCalendarFeedConfigException;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequestCalendarFeedConfig as LeaveCalendarFeedConfigFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfigTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfigTest extends BaseHeadlessTest {

  public function testDefaultParametersAreSetWhenCreatingACalendarConfig() {
    $leaveFeedConfig = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
    ]);

    $this->assertNotNull($leaveFeedConfig->hash);
    $dateNow = new DateTime();
    $this->assertEquals($dateNow, new DateTime($leaveFeedConfig->created_date), '', 10);
  }

  public function testDefaultParametersCanNotBeManipulatedWhenCreatingACalendarFeedConfig() {
    $hash = '5aejfkfdjJJU';
    $createdDate = CRM_Utils_Date::processDate('2016-01-01');

    $leaveFeedConfig = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
      'hash' => $hash,
      'created_date' => $createdDate
    ]);

    $this->assertNotEquals($leaveFeedConfig->hash, $hash);
    $this->assertNotEquals($leaveFeedConfig->created_date, $createdDate);
  }

  public function testDefaultParametersCanNotBeManipulatedWhenUpdatingACalendarFeedConfig() {
    $hash = '5aejfkfdjJJU';
    $createdDate = CRM_Utils_Date::processDate('2016-01-01');

    $leaveFeedConfig1 = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
    ]);

    $leaveFeedConfig = LeaveRequestCalendarFeedConfig::create([
      'id' => $leaveFeedConfig1->id,
      'hash' => $hash,
      'created_date' => $createdDate
    ]);

    $this->assertNotEquals($leaveFeedConfig->hash, $hash);
    $this->assertNotEquals($leaveFeedConfig->created_date, $createdDate);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException
   * @expectedExceptionMessage Please add a valid timezone for the leave request calendar feed configuration
   */
  public function testCreateWillThrowAnExceptionWhenTimezoneIsNotValid() {
    LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'timezone' => 'America/Whatever',
    ]);
  }

  public function testCreateWillThrowAnExceptionWhenTitleAlreadyExists() {
    LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'timezone' => 'Europe/Stockholm',
    ]);

    $this->setExpectedException(InvalidLeaveRequestCalendarFeedConfigException::class, 'A leave request calendar feed configuration with same title already exists!');
    LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'timezone' => 'Europe/Tallinn',
    ]);
  }

  public function testCreateWillNotThrowAnExceptionWhenUpdatingCalendarFeedConfigWithoutChangingTheTitle() {
    $leaveFeedConfig = LeaveCalendarFeedConfigFabricator::fabricate([
      'title' => 'Feed 1',
      'timezone' => 'Europe/Stockholm',
    ]);

    $leaveFeedConfig2 = LeaveRequestCalendarFeedConfig::create([
      'id' => $leaveFeedConfig->id,
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
    ]);

    $this->assertNotNull($leaveFeedConfig2->id);
  }

  public function testGetValuesArrayShouldReturnLeaveRequestCalendarFeedConfigValues() {
    $params = [
      'title' => 'Feed 1',
      'is_active' => 1,
      'timezone' => 'America/Monterrey',
    ];

    $entity = LeaveCalendarFeedConfigFabricator::fabricate($params);
    $values = LeaveRequestCalendarFeedConfig::getValuesArray($entity->id);
    foreach ($params as $field => $value) {
      $this->assertEquals($value, $values[$field]);
    }
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException
   * @expectedExceptionMessage The leave_type is a required composed_of filter field for the calendar feed configuration
   */
  public function testCreateWillThrowExceptionWhenLeaveTypeFilterFieldIsAbsentForComposedOfFilter() {
    LeaveCalendarFeedConfigFabricator::fabricate([
      'composed_of' => []
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException
   * @expectedExceptionMessage The composed_of leave_type filter field value is not passed in the proper format!
   */
  public function testCreateWillThrowExceptionWhenLeaveTypeFilterFieldHasEmptyValueForComposedOfFilter() {
    LeaveCalendarFeedConfigFabricator::fabricate([
      'composed_of' => [
        'leave_type' => []
      ]
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException
   * @expectedExceptionMessage The sample field is not a valid composed_of filter field for the calendar feed configuration
   */
  public function testCreateWillThrowExceptionWhenNonAllowedFieldIsPresentForComposedOfFilter() {
    LeaveCalendarFeedConfigFabricator::fabricate([
      'composed_of' => [
        'leave_type' => [1],
        'sample' => [2]
      ]
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException
   * @expectedExceptionMessage The sample field is not a valid visible_to filter field for the calendar feed configuration
   */
  public function testCreateWillThrowExceptionWhenNonAllowedFieldIsPresentForVisibleToFilter() {
    LeaveCalendarFeedConfigFabricator::fabricate([
      'visible_to' => [
        'sample' => [2]
      ]
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException
   * @expectedExceptionMessage The visible_to department filter field value is not passed in the proper format!
   */
  public function testCreateWillThrowExceptionForInvalidFilterFieldValueForVisibleToFilter() {
    LeaveCalendarFeedConfigFabricator::fabricate([
      'visible_to' => [
        'department' => 'Bla Bla'
      ]
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException
   * @expectedExceptionMessage The composed_of department filter field value is not passed in the proper format!
   */
  public function testCreateWillThrowExceptionForNonAllowedFilterFieldValueForComposedOfFilter() {
    LeaveCalendarFeedConfigFabricator::fabricate([
      'composed_of' => [
        'department' => 'Bla Bla',
        'leave_type' => [1]
      ]
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException
   * @expectedExceptionMessage The composed_of filter is absent or not passed in the proper format
   */
  public function testCreateWillThrowExceptionWhenComposedOfFilterIsAbsentOnCreate() {
    LeaveRequestCalendarFeedConfig::create([
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
      'visible_to' => []
    ]);
  }


  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException
   * @expectedExceptionMessage The visible_to filter is absent or not passed in the proper format
   */
  public function testCreateWillThrowExceptionWhenVisibleToFilterIsAbsentOnCreate() {
    LeaveRequestCalendarFeedConfig::create([
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
      'composed_of' => ['leave_type' => [0]]
    ]);
  }

  public function testFilterFieldsAreStoredAsSerializedValues() {
    $visibleTo = [
      'department' => [1,2],
      'location' => [1]
    ];

    $composedOf = [
      'leave_type' => [1],
      'department' => [3],
      'location' => [3]
    ];

    $calendarFeedConfig = LeaveCalendarFeedConfigFabricator::fabricate([
      'composed_of' => $composedOf,
      'visible_to' => $visibleTo
    ]);

    $this->assertEquals(serialize($visibleTo), $calendarFeedConfig->visible_to);
    $this->assertEquals(serialize($composedOf), $calendarFeedConfig->composed_of);
  }
}
