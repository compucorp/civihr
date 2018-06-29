<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfig as LeaveRequestCalendarFeedConfig;
use CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException as InvalidLeaveRequestCalendarFeedConfigException;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfigTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfigTest extends BaseHeadlessTest {

  public function testDefaultParametersAreSetWhenCreatingACalendarConfig() {
    $leaveFeedConfig = $this->createLeaveCalendarFeedConfig([
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

    $leaveFeedConfig = $this->createLeaveCalendarFeedConfig([
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

    $leaveFeedConfig1 = $this->createLeaveCalendarFeedConfig([
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
    $this->createLeaveCalendarFeedConfig([
      'title' => 'Feed 1',
      'timezone' => 'America/Whatever',
    ]);
  }

  public function testCreateWillThrowAnExceptionWhenTitleAlreadyExists() {
    $this->createLeaveCalendarFeedConfig([
      'title' => 'Feed 1',
      'timezone' => 'Europe/Stockholm',
    ]);

    $this->setExpectedException(InvalidLeaveRequestCalendarFeedConfigException::class, 'A leave request calendar feed configuration with same title already exists!');
    $this->createLeaveCalendarFeedConfig([
      'title' => 'Feed 1',
      'timezone' => 'Europe/Tallinn',
    ]);
  }

  public function testCreateWillNotThrowAnExceptionWhenUpdatingCalendarFeedConfigWithoutChangingTheTitle() {
    $leaveFeedConfig = $this->createLeaveCalendarFeedConfig([
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

    $entity = $this->createLeaveCalendarFeedConfig($params);
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
    $this->createLeaveCalendarFeedConfig([
      'composed_of' => []
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException
   * @expectedExceptionMessage The composed_of leave_type filter field value is not passed in the proper format!
   */
  public function testCreateWillThrowExceptionWhenLeaveTypeFilterFieldHasEmptyValueForComposedOfFilter() {
    $this->createLeaveCalendarFeedConfig([
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
    $this->createLeaveCalendarFeedConfig([
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
    $this->createLeaveCalendarFeedConfig([
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
    $this->createLeaveCalendarFeedConfig([
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
    $this->createLeaveCalendarFeedConfig([
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

    $calendarFeedConfig = $this->createLeaveCalendarFeedConfig([
      'composed_of' => $composedOf,
      'visible_to' => $visibleTo
    ]);

    $this->assertEquals(serialize($visibleTo), $calendarFeedConfig->visible_to);
    $this->assertEquals(serialize($composedOf), $calendarFeedConfig->composed_of);
  }

  private function createLeaveCalendarFeedConfig($params) {
    $defaultParameters = [
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
      'composed_of' => [
        'leave_type' => [1],
        'department' => [3],
        'location' => [3]
      ],
      'visible_to' => [
        'department' => [1,2],
        'location' => [1]
      ]
    ];

    $params = array_merge($defaultParameters, $params);
    return LeaveRequestCalendarFeedConfig::create($params);
  }
}
