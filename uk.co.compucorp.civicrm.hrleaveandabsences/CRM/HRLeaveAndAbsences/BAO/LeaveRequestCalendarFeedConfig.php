<?php

use CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestCalendarFeedConfigException as InvalidLeaveRequestCalendarFeedConfigException;
use CRM_HRLeaveAndAbsences_Validator_TimeZone as TimeZoneValidator;

class CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfig extends CRM_HRLeaveAndAbsences_DAO_LeaveRequestCalendarFeedConfig {

  /**
   * Create a new LeaveRequestCalendarFeedConfig based on array-data
   *
   * @param array $params key-value pairs
   *
   * @return CRM_HRLeaveAndAbsences_DAO_LeaveRequestCalendarFeedConfig|NULL
   */
  public static function create($params) {
    $entityName = 'LeaveRequestCalendarFeedConfig';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    self::validateParams($params);
    self::setDefaultParameterValues($params);

    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * A method for validating the params passed to the
   * LeaveRequestCalendarFeedConfig create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws InvalidLeaveRequestCalendarFeedConfigException
   */
  public static function validateParams($params) {
    self::validateLeaveRequestCalendarFeedConfigTitle($params);
    self::validateTimezone($params);
  }


  /**
   * Validates that the Title of the Leave calendar feed configuration
   * is unique.
   *
   * @param array $params
   *
   * @throws InvalidLeaveRequestCalendarFeedConfigException
   */
  private static function validateLeaveRequestCalendarFeedConfigTitle($params) {
    $title = CRM_Utils_Array::value('title', $params);

    if (!$title) {
      return;
    }

    $leaveRequestCalendarFeedConfig = new self();
    $leaveRequestCalendarFeedConfig->title = $title;

    if (!empty($params['id'])) {
      $id = (int) $params['id'];
      $leaveRequestCalendarFeedConfig->whereAdd("id <> $id");
    }

    $leaveRequestCalendarFeedConfig->find(true);

    if ($leaveRequestCalendarFeedConfig->id) {
      throw new InvalidLeaveRequestCalendarFeedConfigException(
        'A leave request calendar feed configuration with same title already exists!'
      );
    }
  }

  /**
   * Validates that the timezone supplied for the feed configuration
   * is a valid one.
   *
   * @param array $params
   *
   * @throws InvalidLeaveRequestCalendarFeedConfigException
   */
  private static function validateTimezone($params) {
    $timeZone = CRM_Utils_Array::value('timezone', $params);

    if (!$timeZone) {
      return;
    }

    if (!TimeZoneValidator::isValid($timeZone)) {
      throw new InvalidLeaveRequestCalendarFeedConfigException(
        'Please add a valid timezone for the leave request calendar feed configuration'
      );
    }
  }

  /**
   * Sets the parameter values for the created_date and
   * hash parameter. Once set, these values cannot be
   * modified via an update of the entity.
   *
   * @param array $params
   */
  private static function setDefaultParameterValues(&$params) {
    if (empty($params['id'])) {
      $params['created_date'] = date('YmdHis');
      $params['hash'] = md5(uniqid(rand(), true));
    }
    else {
      unset($params['created_date'], $params['hash']);
    }
  }

  /**
   * Returns an array containing all the fields values for the
   * LeaveRequestCalendarFeedConfig with the given ID.
   *
   * This method is mainly used by the LeaveRequestCalendarFeedConfig form, so it
   * can get the data to fill its fields.
   *
   * An empty array is returned if it is not possible to load
   * the data.
   *
   * @param int $id
   *  The id of the LeaveRequestCalendarFeedConfig to retrieve the values
   *
   * @return array An array containing the values
   */
  public static function getValuesArray($id) {
    try {
      $result = civicrm_api3('LeaveRequestCalendarFeedConfig', 'getsingle', ['id' => $id]);
      return $result;
    } catch (CiviCRM_API3_Exception $ex) {
      return [];
    }
  }
}
