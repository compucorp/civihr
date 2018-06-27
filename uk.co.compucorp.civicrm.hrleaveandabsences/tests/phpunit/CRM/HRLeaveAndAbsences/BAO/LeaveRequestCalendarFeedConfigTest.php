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
    $leaveFeedConfig = LeaveRequestCalendarFeedConfig::create([
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

    $leaveFeedConfig = LeaveRequestCalendarFeedConfig::create([
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

    $leaveFeedConfig1 = LeaveRequestCalendarFeedConfig::create([
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
    LeaveRequestCalendarFeedConfig::create([
      'title' => 'Feed 1',
      'timezone' => 'America/Whatever',
    ]);
  }

  public function testCreateWillThrowAnExceptionWhenTitleAlreadyExists() {
    LeaveRequestCalendarFeedConfig::create([
      'title' => 'Feed 1',
      'timezone' => 'Europe/Stockholm',
    ]);

    $this->setExpectedException(InvalidLeaveRequestCalendarFeedConfigException::class, 'A leave request calendar feed configuration with same title already exists!');
    LeaveRequestCalendarFeedConfig::create([
      'title' => 'Feed 1',
      'timezone' => 'Europe/Tallinn',
    ]);
  }

  public function testCreateWillNotThrowAnExceptionWhenUpdatingCalendarFeedConfigWithoutChangingTheTitle() {
    $leaveFeedConfig = LeaveRequestCalendarFeedConfig::create([
      'title' => 'Feed 1',
      'timezone' => 'Europe/Stockholm',
    ]);

    $leaveFeedConfig2 =  LeaveRequestCalendarFeedConfig::create([
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

    $entity = LeaveRequestCalendarFeedConfig::create($params);
    $values = LeaveRequestCalendarFeedConfig::getValuesArray($entity->id);
    foreach ($params as $field => $value) {
      $this->assertEquals($value, $values[$field]);
    }
  }
}
