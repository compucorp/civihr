<?php

/**
 * Class CRM_HRLeaveAndAbsences_BAO_Entitlement
 */
class CRM_HRLeaveAndAbsences_BAO_Entitlement extends CRM_HRLeaveAndAbsences_DAO_Entitlement {

  /**
   * Create a new Entitlement based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRLeaveAndAbsences_DAO_Entitlement|NULL
   **/
  public static function create($params) {
    $entityName = 'Entitlement';
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
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException
   */
  private static function validateComment($params) {
    $hasComment = !empty($params['comment']);
    $hasCommentAuthor = !empty($params['comment_author_id']);
    $hasCommentDate = !empty($params['comment_updated_at']);
    if($hasComment) {
      if(!$hasCommentAuthor) {
        throw new CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException(
          ts('The author of the comment cannot be null')
        );
      }

      if(!$hasCommentDate) {
        throw new CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException(
          ts('The date of the comment cannot be null')
        );
      }
    }

    if(!$hasComment && $hasCommentAuthor) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException(
        ts('The author of the comment should be null if the comment is empty')
      );
    }

    if(!$hasComment && $hasCommentDate) {
      throw new CRM_HRLeaveAndAbsences_Exception_InvalidEntitlementException(
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
   * @return \CRM_HRLeaveAndAbsences_BAO_Entitlement|null If there's no entitlement for the given arguments, null will be returned
   *
   * @throws \InvalidArgumentException
   */
  public static function getContractEntitlementForPeriod($contractId, $periodId, $absenceTypeId) {
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
   * Returns the number of days of this entitlement remaining to be taken.
   *
   * This method takes into account the expiration date of the brought forward
   * days. That is, if these days have not been taken and expired, they won't
   * be included in the returned number.
   *
   * @return int
   */
  public function getNumberOfDaysRemaining() {
    $leavesTakenInPeriod = $this->getNumberOfLeavesTakenInPeriod();
    $daysRemaining = $this->proposed_entitlement - $leavesTakenInPeriod;

    $broughtForwardRemaining = $this->brought_forward_days - $leavesTakenInPeriod;
    if($broughtForwardRemaining > 0 && $this->broughtForwardHasExpired()) {
      $daysRemaining -= $broughtForwardRemaining;
    }

    return $daysRemaining;
  }

  /**
   * Returns if the brought forward days for this entitlement expired.
   *
   * @return bool
   */
  private function broughtForwardHasExpired() {
    // No expiration date means it never expires
    if(!$this->brought_forward_expiration_date) {
      return false;
    }

    return strtotime($this->brought_forward_expiration_date) < strtotime('now');
  }

  /**
   * Return the number of Leaves taken during this entitlement period
   *
   * @TODO The Leaves Request feature is not yet implemented, so we're only returning 0 for now
   *
   * @return int
   */
  private function getNumberOfLeavesTakenInPeriod() {
    return 0;
  }

}
