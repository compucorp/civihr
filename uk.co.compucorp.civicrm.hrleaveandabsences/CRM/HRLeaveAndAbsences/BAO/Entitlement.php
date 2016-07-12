<?php

use CRM_HRLeaveAndAbsences_BAO_BroughtForward as BroughtForward;
use CRM_HRLeaveAndAbsences_EntitlementCalculation as EntitlementCalculation;

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
   * This method saves a new Entitlement based on the given Entitlement Calculation.
   *
   * If there's already an Entitlement for calculation's Absence Period, Absence
   * Type, and Contract, it will be replaced by a new one.
   *
   * If an overridden entitlement is given, the created Entitlement will be marked
   * as overridden and the given value will be stored.
   *
   * If a calculation comment is given, the current logged in user will be stored
   * as the comment's author.
   *
   * @param \CRM_HRLeaveAndAbsences_EntitlementCalculation $calculation
   * @param float|null $overriddenEntitlement
   *  A value to override the calculation's proposed entitlement
   * @param string|null $calculationComment
   *  A comment describing the calculation
   */
  public static function saveFromCalculation(EntitlementCalculation $calculation, $overriddenEntitlement = null, $calculationComment = null) {
    global $user;

    $absenceTypeID = $calculation->getAbsenceType()->id;
    $contractID = $calculation->getContract()['id'];
    $absencePeriodID = $calculation->getAbsencePeriod()->id;

    $params = [
      'type_id' => $absenceTypeID,
      'contract_id' => $contractID,
      'period_id' => $absencePeriodID,
      'pro_rata' => $calculation->getProRata(),
      'proposed_entitlement' => $calculation->getProposedEntitlement(),
      'overridden' => false,
    ];

    if($overriddenEntitlement) {
      $params['overridden'] = true;
      $params['proposed_entitlement'] = (float)$overriddenEntitlement;
    }

    if($calculationComment) {
      $params['comment'] = $calculationComment;
      $params['comment_author_id'] = $user->uid;
      $params['comment_updated_at'] = date('YmdHis');
    }

    $broughtForwardParams = [];
    $broughtForward = $calculation->getBroughtForward();
    if($broughtForward) {
      $broughtForwardExpirationDate = $calculation->getAbsencePeriod()->getExpirationDateForAbsenceType(
        $calculation->getAbsenceType()
      );
      $broughtForwardExpirationDate = CRM_Utils_Date::processDate($broughtForwardExpirationDate);

      $broughtForwardParams = [
        'balance' => $broughtForward,
        'expiration_date' => $broughtForwardExpirationDate
      ];
    }

    $transaction = new CRM_Core_Transaction();
    try {
      self::deleteEntitlement($absencePeriodID, $absenceTypeID, $contractID);
      $entitlement = self::create($params);
      if(!empty($broughtForwardParams)) {
        $broughtForwardParams['entitlement_id'] = $entitlement->id;
        BroughtForward::create($broughtForwardParams);
      }

      $transaction->commit();
    } catch(\Exception $ex) {
      $transaction->rollback();
    }
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
   * Deletes the Entitlement with the given Absence Period ID, Absence Type ID
   * and Contract ID
   *
   * @param int $absencePeriodID
   * @param int $absenceTypeID
   * @param int $contractID
   */
  private static function deleteEntitlement($absencePeriodID, $absenceTypeID, $contractID) {
    $tableName = self::getTableName();
    $query = "
      DELETE FROM {$tableName}
      WHERE period_id = %1 AND type_id = %2 AND contract_id = %3
    ";
    $params = [
      1 => [$absencePeriodID, 'Positive'],
      2 => [$absenceTypeID, 'Positive'],
      3 => [$contractID, 'Positive'],
    ];

    CRM_Core_DAO::executeQuery($query, $params);
  }

  /**
   * Returns the current balance for this entitlement, which is given by:
   *
   * Pro Rata + Brought Forward Balance + Leave Requests Balance
   *
   * @return int
   */
  public function getBalance() {
    $leaveRequestBalance = $this->getLeaveRequestBalance();
    $broughtForwardBalance = $this->getBroughtForwardBalance();

    return $this->pro_rata + $broughtForwardBalance + $leaveRequestBalance;
  }

  /**
   * Returns the Brought Forward balance for this entitlement
   *
   * @return int
   */
  public function getBroughtForwardBalance()
  {
    $query = "
      SELECT SUM(balance) as balance
      FROM civicrm_hrleaveandabsences_brought_forward
      WHERE entitlement_id = %1
    ";

    $params = [
      '1' => [$this->id, 'Integer']
    ];

    $dao = CRM_Core_DAO::executeQuery($query, $params);
    $dao->fetch();
    return (int)$dao->balance;
  }

  /**
   * Returns the Leave Request balance for this entitlement
   *
   * @TODO The Leaves Request feature is not yet implemented, so we're only returning 0 for now
   *
   * @return int
   */
  private function getLeaveRequestBalance() {
    return 0;
  }

}
