<?php
use CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementLogException as InvalidLeavePeriodEntitlementLogException;

class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlementLog extends CRM_HRLeaveAndAbsences_DAO_LeavePeriodEntitlementLog {

  /**
   * Create a new LeavePeriodEntitlementLog based on array-data
   *
   * @param array $params
   *
   * @return CRM_HRLeaveAndAbsences_DAO_LeavePeriodEntitlementLog|NULL
   */
  public static function create($params) {
    $entityName = 'LeavePeriodEntitlementLog';
    $hook = empty($params['id']) ? 'create' : 'edit';
    self::validateUpdatesNotAllowed($params);

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    self::validateParams($params);
    $instance = new self();
    $instance->copyValues($params);

    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * A method for validating the params passed to the
   * Leave Period Entitlement Log create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementLogException
   */
  public static function validateParams($params) {
    self::validateMandatory($params);
  }

  /**
   * A method for validating the mandatory fields in the params
   * passed to the LeavePeriodEntitlement Log create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementLogException
   */
  private static function validateMandatory($params) {
    $mandatoryFields = [
      'entitlement_id',
      'editor_id',
    ];

    foreach($mandatoryFields as $field) {
      if (empty($params[$field])) {
        throw new InvalidLeavePeriodEntitlementLogException(
          "The {$field} field should not be empty"
        );
      }
    }

    if(!isset($params['entitlement_amount'])) {
      throw new InvalidLeavePeriodEntitlementLogException(
        'The entitlement_amount field should not be empty'
      );
    }
  }

  /**
   * A method for validating that updates are not allowed
   * for the LeavePeriodEntitlementLog Entity. Since it is
   * for logging purposes, we don't want the records to be
   * manipulated or updated
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementLogException
   */
  private static function validateUpdatesNotAllowed($params) {
    if(!empty($params['id'])){
      throw new InvalidLeavePeriodEntitlementLogException(
        'Updates not allowed for Leave Period Entitlement Log entity'
      );
    }
  }
}
