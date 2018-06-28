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
    self::serializeFilterValues($params);

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
    self::validateComposedOfFilter($params);
    self::validateVisibleToFilter($params);
  }

  /**
   * Validates the composed_of field for the LeaveRequestCalendarFeedConfig Entity.
   *
   * @param array $params
   *
   * @throws InvalidLeaveRequestCalendarFeedConfigException
   */
  private static function validateComposedOfFilter($params) {
    $allowedFields = ['department', 'location', 'leave_type'];
    $requiredFields = ['leave_type'];
    self::validateFilterFields('composed_of', $params, $allowedFields, $requiredFields);
  }

  /**
   * Validates the visible_to field for the LeaveRequestCalendarFeedConfig Entity.
   *
   * @param array $params
   *
   * @throws InvalidLeaveRequestCalendarFeedConfigException
   */
  private static function validateVisibleToFilter($params) {
    $allowedFields = ['department', 'location'];
    self::validateFilterFields('visible_to', $params, $allowedFields);
  }

  /**
   * Validates the Filter fields (visible_to, composed_of) for the entity
   * It ensures that only the allowed fields are present for respective filters
   * and also that required fields should be present.
   *
   * @param string $filterName
   * @param array $params
   * @param array $allowedFields
   * @param array $requiredFields
   *
   * @throws InvalidLeaveRequestCalendarFeedConfigException
   */
  private static function validateFilterFields($filterName, $params, array $allowedFields, array $requiredFields = []) {
    if (!isset($params[$filterName]) && !empty($params['id'])) {
      return;
    }

    $feedConfigFilter = CRM_Utils_Array::value($filterName, $params);

    if (!is_array($feedConfigFilter)) {
      throw new InvalidLeaveRequestCalendarFeedConfigException(
        'The ' . $filterName . ' filter is absent or not passed in the proper format'
      );
    }

    if (!empty($requiredFields)) {
      foreach($requiredFields as $requiredField) {
        if (!array_key_exists($requiredField, $feedConfigFilter)) {
          throw new InvalidLeaveRequestCalendarFeedConfigException(
            'The ' . $requiredField . ' is a required ' . $filterName . ' filter field for the calendar feed configuration'
          );
        }
      }
    }

    foreach ($feedConfigFilter as $filterFieldName => $filterFieldValue) {
      if (in_array($filterFieldName, $allowedFields)) {
        if (!is_array($filterFieldValue) || empty($filterFieldValue)) {
          throw new InvalidLeaveRequestCalendarFeedConfigException(
            'The ' . $filterName  .' '. $filterFieldName . ' filter field value is not passed in the proper format!'
          );
        }
      }
    }

    foreach ($feedConfigFilter as $filterFieldName => $filterFieldValue) {
      if (!in_array($filterFieldName, $allowedFields)) {
        throw new InvalidLeaveRequestCalendarFeedConfigException(
          'The ' . $filterFieldName . ' field is not a valid ' . $filterName . ' filter field for the calendar feed configuration'
        );
      }
    }
  }

  /**
   * Serializes the values of the composed_of and visible_to fields
   *
   * @param array $params
   */
  public static function serializeFilterValues(&$params) {
    $filters = ['visible_to', 'composed_of'];
    foreach($filters as $filter) {
      if (isset($params[$filter])) {
        $params[$filter] = serialize($params[$filter]);
      }
    }
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
