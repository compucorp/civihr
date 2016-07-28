<?php

use CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementException as InvalidPeriodEntitlementException;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement
 */
class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement extends CRM_HRLeaveAndAbsences_DAO_LeavePeriodEntitlement {

  /**
   * Create a new LeavePeriodEntitlement based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_LeavePeriodEntitlement|NULL
   **/
  public static function create($params) {
    $entityName = 'LeavePeriodEntitlement';
    $hook = empty($params['id']) ? 'create' : 'edit';

    self::validateParams($params);

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Validates the $params passed to the create method
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException
   */
  private static function validateParams($params) {
    self::validateComment($params);
  }

  /**
   * Validates the comment fields on the $params array.
   *
   * If the comment is not empty, then the comment author and date are required.
   * Otherwise, the author and the date should be empty.
   *
   * @param array $params
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementException
   */
  private static function validateComment($params) {
    $hasComment = !empty($params['comment']);
    $hasCommentAuthor = !empty($params['comment_author_id']);
    $hasCommentDate = !empty($params['comment_date']);
    if($hasComment) {
      if(!$hasCommentAuthor) {
        throw new InvalidPeriodEntitlementException(
          ts('The author of the comment cannot be null')
        );
      }

      if(!$hasCommentDate) {
        throw new InvalidPeriodEntitlementException(
          ts('The date of the comment cannot be null')
        );
      }
    }

    if(!$hasComment && $hasCommentAuthor) {
      throw new InvalidPeriodEntitlementException(
        ts('The author of the comment should be null if the comment is empty')
      );
    }

    if(!$hasComment && $hasCommentDate) {
      throw new InvalidPeriodEntitlementException(
        ts('The date of the comment should be null if the comment is empty')
      );
    }
  }
}
