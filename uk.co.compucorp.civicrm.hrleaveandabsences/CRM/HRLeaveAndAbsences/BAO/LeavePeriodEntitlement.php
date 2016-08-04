<?php
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_Exception_InvalidLeavePeriodEntitlementException as InvalidPeriodEntitlementException;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement
 */
class CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement extends CRM_HRLeaveAndAbsences_DAO_LeavePeriodEntitlement {

  /**
   * Create a new LeavePeriodEntitlement based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement|NULL
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

  /**
   * Returns the calculated entitlement for a JobContract,
   * AbsencePeriod and AbsenceType with the given IDs
   *
   * @param int $contractId The ID of the JobContract
   * @param int $periodId The ID of the Absence Period
   * @param int $absenceTypeId The ID of the AbsenceType
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement|null
   *    If there's no entitlement for the given arguments, null will be returned
   *
   * @throws \InvalidArgumentException
   */
  public static function getPeriodEntitlementForContract($contractId, $periodId, $absenceTypeId) {
    if(!$contractId) {
      throw new InvalidArgumentException("You must inform the Contract ID");
    }
    if(!$periodId) {
      throw new InvalidArgumentException("You must inform the AbsencePeriod ID");
    }
    if(!$absenceTypeId) {
      throw new InvalidArgumentException("You must inform the AbsenceType ID");
    }

    $entitlement = new self();
    $entitlement->contract_id = (int)$contractId;
    $entitlement->period_id = (int)$periodId;
    $entitlement->type_id = (int)$absenceTypeId;
    $entitlement->find(true);
    if($entitlement->id) {
      return $entitlement;
    }

    return null;
  }

  /**
   * Returns the current balance for this LeavePeriodEntitlement.
   *
   * The balance only includes:
   * - Brought Forward
   * - Public Holidays
   * - Expired Balance Changes
   * - Approved Leave Requests
   *
   * @return float
   */
  public function getBalance() {
    $leaveRequestStatus = array_flip(LeaveRequest::buildOptions('status_id'));
    $filterStatuses = [
      $leaveRequestStatus['Approved'],
      $leaveRequestStatus['Admin Approved'],
    ];
    return LeaveBalanceChange::getBalanceForEntitlement($this->id, $filterStatuses);
  }

  /**
   * Returns the entitlement (number of days) for this LeavePeriodEntitlement.
   *
   * This is basic the sum of the amounts of the LeaveBalanceChanges that are
   * part of the entitlement Breakdown. That is balance changes of the Leave,
   * Brought Forward and Public Holidays types, without a source_id.
   *
   * @see CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange::getBreakdownBalanceChangesForEntitlement()
   *
   * @return float
   */
  public function getEntitlement() {
    return LeaveBalanceChange::getBreakdownBalanceForEntitlement($this->id);
  }

  /**
   * Returns the current LeaveRequest balance for this LeavePeriodEntitlement. That
   * is, a balance that sums up only the balance changes caused by Leave Requests.
   *
   * Since LeaveRequests generates negative balance changes, the returned number
   * will be negative as well.
   *
   * This method only accounts for Approved LeaveRequests.
   *
   * @return float
   */
  public function getLeaveRequestBalance() {
    $leaveRequestStatus = array_flip(LeaveRequest::buildOptions('status_id'));
    $filterStatuses = [
      $leaveRequestStatus['Approved'],
      $leaveRequestStatus['Admin Approved'],
    ];

    return LeaveBalanceChange::getLeaveRequestBalanceForEntitlement($this->id, $filterStatuses);
  }
}
