<?php

use CRM_HRLeaveAndAbsences_Exception_InvalidLeaveBalanceChangeExpiryLogException as InvalidLeaveBalanceChangeExpiryLogException;

class CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChangeExpiryLog extends CRM_HRLeaveAndAbsences_DAO_LeaveBalanceChangeExpiryLog {

  /**
   * Create a new LeaveBalanceChangeExpiryLog based on array-data
   *
   * @param array $params key-value pairs
   *
   * @return CRM_HRLeaveAndAbsences_DAO_LeaveBalanceChangeExpiryLog|NULL
   */
  public static function create($params) {
    $entityName = 'LeaveBalanceChangeExpiryLog';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    self::validateParams($params);
    $params['created_date'] = date('YmdHis');
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }


  /**
   * A method for validating the params passed to the
   * LeaveBalanceChangeExpiryLog create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws InvalidLeaveBalanceChangeExpiryLogException
   */
  public static function validateParams($params) {
    self::validateUpdatesNotAllowed($params);
    self::validateMandatory($params);
  }

  /**
   * A method for validating the mandatory fields in the params
   * passed to the LeaveBalanceChangeExpiryLog create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws InvalidLeaveBalanceChangeExpiryLogException
   */
  private static function validateMandatory($params) {
    $mandatoryFields = [
      'balance_change_id',
      'source_id',
      'source_type',
      'expiry_date',
      'balance_type_id'
    ];

    foreach($mandatoryFields as $field) {
      if (empty($params[$field])) {
        throw new InvalidLeaveBalanceChangeExpiryLogException(
          "The {$field} field should not be empty"
        );
      }
    }

    if(!isset($params['amount'])) {
      throw new InvalidLeaveBalanceChangeExpiryLogException(
        'The amount field should not be empty'
      );
    }
  }

  /**
   * A method for validating that updates are not allowed
   * for the LeaveBalanceChangeExpiryLog Entity. Since it is
   * for logging purposes, we don't want the records to be
   * manipulated or updated
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws InvalidLeaveBalanceChangeExpiryLogException
   */
  private static function validateUpdatesNotAllowed($params) {
    if(!empty($params['id'])) {
      throw new InvalidLeaveBalanceChangeExpiryLogException(
        'Updates not allowed for the LeaveBalanceChange Expiry Log entity'
      );
    }
  }
}
