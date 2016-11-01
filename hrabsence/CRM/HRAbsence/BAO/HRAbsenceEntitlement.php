<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2014                                |
+--------------------------------------------------------------------+
| This file is a part of CiviCRM.                                    |
|                                                                    |
| CiviCRM is free software; you can copy, modify, and distribute it  |
| under the terms of the GNU Affero General Public License           |
| Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
|                                                                    |
| CiviCRM is distributed in the hope that it will be useful, but     |
| WITHOUT ANY WARRANTY; without even the implied warranty of         |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
| See the GNU Affero General Public License for more details.        |
|                                                                    |
| You should have received a copy of the GNU Affero General Public   |
| License and the CiviCRM Licensing Exception along                  |
| with this program; if not, contact CiviCRM LLC                     |
| at info[AT]civicrm[DOT]org. If you have questions about the        |
| GNU Affero General Public License or the licensing of CiviCRM,     |
| see the CiviCRM license FAQ at http://civicrm.org/licensing        |
+--------------------------------------------------------------------+
*/

use CRM_HRAbsence_BAO_HRAbsencePeriod as AbsencePeriod;
use CRM_HRAbsence_BAO_HRAbsenceEntitlement as AbsenceEntitlement;
use CRM_Hrjobcontract_BAO_HRJobLeave as JobLeave;

class CRM_HRAbsence_BAO_HRAbsenceEntitlement extends CRM_HRAbsence_DAO_HRAbsenceEntitlement {

  public static function create($params) {
    $entityName = 'HRAbsenceEntitlement';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Get a count of records with the given property
   *
   * @param $params
   * @return int
   */
  public static function getRecordCount($params) {
    $dao = new CRM_HRAbsence_DAO_HRAbsenceEntitlement();
    $dao->copyValues($params);
    return $dao->count();
  }

  /**
   * Recalculates the Entitlements for all the contacts during the given
   * absence period.
   *
   * @param int $periodId
   */
  public static function recalculateAbsenceEntitlementsForPeriod($periodId) {
    $absencePeriod = AbsencePeriod::findById($periodId);
    $periods = [
      $periodId => [
        'start' => $absencePeriod->start_date,
        'end'   => $absencePeriod->end_date,
      ]
    ];

    $contacts = self::getAllIndividuals();
    foreach($contacts as $contact) {
      self::overwriteContactEntitlementForPeriods($contact['id'], $periods);
    }
  }

  /**
   * Recalculates HR Absence Entitlement values for given Contact.
   *
   * @param int $contactId
   */
  public static function recalculateAbsenceEntitlementForContact($contactId) {
    $periods = AbsencePeriod::getAbsencePeriods();

    self::overwriteContactEntitlementForPeriods($contactId, $periods);
  }

  /**
   * Recalculates HR Absence Entitlement values for given Job Contract ID.
   *
   * @param int $jobContractId
   */
  public static function recalculateAbsenceEntitlement($jobContractId) {
    $jobContract = civicrm_api3('HRJobContract', 'getsingle', array(
      'sequential' => 1,
      'id'         => $jobContractId,
      'deleted'    => 0,
      'return'     => "contact_id,period_start_date,period_end_date,deleted",
    ));

    $startDate = isset($jobContract['period_start_date']) ? date('Y-m-d H:i:s', strtotime($jobContract['period_start_date'])) : NULL;
    $endDate   = isset($jobContract['period_end_date']) ? date('Y-m-d H:i:s', strtotime($jobContract['period_end_date'])) : NULL;
    $periods   = AbsencePeriod::getAbsencePeriods($startDate, $endDate);

    self::overwriteContactEntitlementForPeriods($jobContract['contact_id'], $periods);
  }

  /**
   * Overwrite the contact entitlement for the given periods
   *
   * @param int $contactId
   * @param array $periods
   */
  private static function overwriteContactEntitlementForPeriods($contactId, $periods) {
    foreach ($periods as $periodId => $periodValue) {
      $leaves = JobLeave::getLeavesForPeriod($contactId, $periodValue['start'], $periodValue['end']);
      self::overwriteAbsenceEntitlementPeriod($contactId, $periodId, $leaves);
    }
  }

  /**
   * Overwrites the absence entitlement for the given contact during the given
   * period.
   *
   * @param int $contactId
   * @param int $periodId
   * @param array $leaves
   */
  private static function overwriteAbsenceEntitlementPeriod($contactId, $periodId, array $leaves) {
    $query = 'DELETE FROM civicrm_hrabsence_entitlement WHERE contact_id = %1 AND period_id = %2';
    $params = [
      1 => [$contactId, 'Integer'],
      2 => [$periodId, 'Integer'],
    ];

    CRM_Core_DAO::executeQuery($query, $params);

    foreach ($leaves as $leaveType => $leaveAmount) {
      self::overwriteAbsenceTypeEntitlementForPeriod($contactId, $periodId, $leaveType, $leaveAmount);
    }
  }

  /**
   * Overwrites the absence entitlement for a single leave type for the given
   * contact during the given period.
   *
   * @param int $contactId
   * @param int $periodId
   * @param int $leaveType
   * @param float $amount
   */
  private static function overwriteAbsenceTypeEntitlementForPeriod($contactId, $periodId, $leaveType, $amount) {
    $query = 'INSERT INTO civicrm_hrabsence_entitlement SET contact_id = %1, period_id = %2, type_id = %3, amount = %4';
    $params = [
      1 => [$contactId, 'Integer'],
      2 => [$periodId, 'Integer'],
      3 => [$leaveType, 'Integer'],
      4 => [$amount, 'Float'],
    ];
    CRM_Core_DAO::executeQuery($query, $params);
  }

  /**
   * Returns all the non-deleted contacts where contact_type is
   * 'Individual'
   *
   * @return array
   */
  private static function getAllIndividuals() {
    $result = civicrm_api3('Contact', 'get', [
      'sequential' => 1,
      'contact_type' => 'Individual',
      'options' => [
        'limit' => 0
      ]
    ]);

    if(!empty($result['is_error'])) {
      return [];
    }

    return $result['values'];
  }

  /**
   * Process all the items on the EntitlementRecalculation Queue
   *
   * @return int
   *  The number of items processed
   */
  public static function processEntitlementRecalculationQueue() {
    $numberOfItemsProcessed = 0;

    $queue = CRM_HRAbsence_Queue_EntitlementRecalculation::getQueue();
    $runner = new CRM_Queue_Runner([
      'title' => ts('Entitlement Recalculation Runner'),
      'queue' => $queue,
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE,
    ]);

    $continue = true;
    while($continue) {
      $result = $runner->runNext(false);
      $numberOfItemsProcessed++;
      if (!$result['is_continue']) {
        $continue = false; //all items in the queue are processed
      }
    }

    return $numberOfItemsProcessed;
  }
}
