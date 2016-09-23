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

class CRM_Hrjobcontract_BAO_HRJobLeave extends CRM_Hrjobcontract_DAO_HRJobLeave {
  /**
   * static field for the HRJobPay information that we can potentially import
   *
   * @var array
   * @static
   */
  static $_importableFields = array();

  /**
   * Create a new HRJobLeave based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Hrjobcontract_DAO_HRJobLeave|NULL
   *
   */
  public static function create($params) {
      //If add_public_holidays has not been set or is null,
      //make sure to set it to false (0)
      if(empty($params['add_public_holidays'])) {
        $params['add_public_holidays'] = 0;
      }
      return parent::create($params);
  }

  /**
   * Recalculating HR Absence Entitlement values for given Job Contract ID.
   *
   * @param int $jobContractId
   * @throws \Exception
   */
  static function recalculateAbsenceEntitlement($jobContractId) {
    try {
      $jobContract = civicrm_api3('HRJobContract', 'getsingle', array(
        'sequential' => 1,
        'id' => $jobContractId,
        'deleted' => 0,
        'return' => "contact_id,period_start_date,period_end_date,deleted",
      ));

      $startDate = isset($jobContract['period_start_date']) ? date('Y-m-d H:i:s', strtotime($jobContract['period_start_date'])) : null;
      $endDate = isset($jobContract['period_end_date']) ? date('Y-m-d H:i:s', strtotime($jobContract['period_end_date'])) : null;
      $periods = CRM_Hrjobcontract_BAO_HRJobLeave::getAbsencePeriods($startDate, $endDate);

      foreach ($periods as $periodId => $periodValue) {
        $leaves = CRM_Hrjobcontract_BAO_HRJobLeave::getLeavesForPeriod($jobContract['contact_id'], $periodValue['start'], $periodValue['end']);
        CRM_Hrjobcontract_BAO_HRJobLeave::overwriteAbsenceEntitlementPeriod($jobContract['contact_id'], $periodId, $leaves);
      }

    } catch (\Exception $e) {
      throw new \Exception($e);
    }
  }

  /**
   * Recalculating HR Absence Entitlement values for given Contact.
   *
   * @param int $contactId
   * @throws \Exception
   */
  static function recalculateAbsenceEntitlementForContact($contactId) {
    try {
      $periods = CRM_Hrjobcontract_BAO_HRJobLeave::getAbsencePeriods();

      foreach ($periods as $periodId => $periodValue) {
        $leaves = CRM_Hrjobcontract_BAO_HRJobLeave::getLeavesForPeriod($contactId, $periodValue['start'], $periodValue['end']);
        CRM_Hrjobcontract_BAO_HRJobLeave::overwriteAbsenceEntitlementPeriod($contactId, $periodId, $leaves);
      }

    } catch (\Exception $e) {
      throw new \Exception($e);
    }
  }

  static function getAllAbsencePeriods() {
    $data = array();
    $result = civicrm_api3('HRAbsencePeriod', 'get', array(
      'sequential' => 1,
    ));
    foreach ($result['values'] as $period) {
      $data[$period['id']] = array(
        'start' => $period['start_date'],
        'end' => $period['end_date'],
      );
    }
    return $data;
  }

  static function getAbsencePeriods($startDate = null, $endDate = null) {
    $data = array();
    $query = "SELECT * FROM civicrm_hrabsence_period ";
    $where = array();
    $params = array();
    $whereQuery = '';
    if ($startDate) {
      $startDate = date('Y-m-d H:i:s', strtotime($startDate));
      $where[] = " end_date >= %1 ";
      $params[1] = array($startDate, 'String');
    }
    if ($endDate) {
      $endDate = date('Y-m-d H:i:s', strtotime($endDate));
      $where[] = " start_date < %2 ";
      $params[2] = array($endDate, 'String');
    }
    if (!empty($where)) {
      $whereQuery = ' WHERE ' . implode(' AND ', $where);
    }
    $periods = CRM_Core_DAO::executeQuery($query . $whereQuery, $params);
    while ($periods->fetch()) {
      $data[$periods->id] = array(
        'start' => $periods->start_date,
        'end' => $periods->end_date,
      );
    }
    return $data;
  }

  static function getLeavesForPeriod($contactId, $startDate = null, $endDate = null) {
    $data = CRM_Hrjobcontract_BAO_HRJobLeave::createAbsenceArray();
    $jobContracts = civicrm_api3('HRJobContract', 'get', array(
      'sequential' => 1,
      'contact_id' => $contactId,
      'deleted' => 0,
    ));
    foreach ($jobContracts['values'] as $jobContract) {
      $jobContractDetails = civicrm_api3('HRJobDetails', 'get', array(
        'sequential' => 1,
        'jobcontract_id' => $jobContract['id'],
      ));
      if (empty($jobContractDetails['values'])) {
        continue;
      }
      $details = CRM_Utils_Array::first($jobContractDetails['values']);
      $details['period_start_date'] = $details['period_start_date'] ? date('Y-m-d H:i:s', strtotime($details['period_start_date'])) : null;
      $details['period_end_date'] = $details['period_end_date'] ? date('Y-m-d H:i:s', strtotime($details['period_end_date'])) : null;
      if (CRM_Hrjobcontract_BAO_HRJobLeave::isJobDetailsInPeriod($details, $startDate, $endDate)) {
        $leaves = civicrm_api3('HRJobLeave', 'get', array(
          'sequential' => 1,
          'jobcontract_id' => $jobContract['id'],
        ));
        foreach ($leaves['values'] as $leave) {
          $data[$leave['leave_type']] += $leave['leave_amount'];
        }
      }
    }
    return $data;
  }

  static function isJobDetailsInPeriod($jobDetails, $startDate = null, $endDate = null) {
    $result = true;
    if ($startDate && !empty($jobDetails['period_end_date'])) {
      if ($jobDetails['period_end_date'] < $startDate) {
        $result = false;
      }
    }
    if ($endDate && !empty($jobDetails['period_start_date'])) {
      if ($jobDetails['period_start_date'] > $endDate) {
        $result = false;
      }
    }
    return $result;
  }

  static function createAbsenceArray() {
    $data = array();
    $absenceTypes = civicrm_api3('HRAbsenceType', 'get', array(
      'sequential' => 1,
    ));
    foreach ($absenceTypes['values'] as $absenceType) {
      $data[$absenceType['id']] = 0;
    }
    return $data;
  }

  static function overwriteAbsenceEntitlementPeriod($contactId, $periodId, array $leaves) {
    CRM_Core_DAO::executeQuery('DELETE FROM civicrm_hrabsence_entitlement WHERE contact_id = %1 AND period_id = %2',
      array(
        1 => array($contactId, 'Integer'),
        2 => array($periodId, 'Integer'),
      )
    );
    foreach ($leaves as $leaveType => $leaveAmount) {
      CRM_Core_DAO::executeQuery('INSERT INTO civicrm_hrabsence_entitlement SET contact_id = %1, period_id = %2, type_id = %3, amount = %4',
      array(
        1 => array($contactId, 'Integer'),
        2 => array($periodId, 'Integer'),
        3 => array($leaveType, 'Integer'),
        4 => array($leaveAmount, 'Float'),
      )
    );
    }
  }

  /**
   * combine all the importable fields from the lower levels object
   *
   * The ordering is important, since currently we do not have a weight
   * scheme. Adding weight is super important
   *
   * @param int     $contactType     contact Type
   * @param boolean $status          status is used to manipulate first title
   * @param boolean $showAll         if true returns all fields (includes disabled fields)
   * @param boolean $isProfile       if its profile mode
   * @param boolean $checkPermission if false, do not include permissioning clause (for custom data)
   *
   * @return array array of importable Fields
   * @access public
   * @static
   */
  static function importableFields($contactType = 'HRJobLeave',
    $status          = FALSE,
    $showAll         = FALSE,
    $isProfile       = FALSE,
    $checkPermission = TRUE,
    $withMultiCustomFields = FALSE
  ) {
    if (empty($contactType)) {
      $contactType = 'HRJobLeave';
    }

    $cacheKeyString = "";
    $cacheKeyString .= $status ? '_1' : '_0';
    $cacheKeyString .= $showAll ? '_1' : '_0';
    $cacheKeyString .= $isProfile ? '_1' : '_0';
    $cacheKeyString .= $checkPermission ? '_1' : '_0';

    $fields = CRM_Utils_Array::value($cacheKeyString, self::$_importableFields);

    if (!$fields) {
      $fields = CRM_Hrjobcontract_DAO_HRJobLeave::import();
      $fields = array_merge($fields, CRM_Hrjobcontract_DAO_HRJobLeave::import());
      foreach ($fields as $key => $v) {
        $fields[$key]['hasLocationType'] = TRUE;
      }

      //Sorting fields in alphabetical order
      $fields = CRM_Utils_Array::crmArraySortByField($fields, 'title');
      $fields = CRM_Utils_Array::index(array('name'), $fields);
      CRM_Core_BAO_Cache::setItem($fields, 'contact fields', $cacheKeyString);
     }
    self::$_importableFields[$cacheKeyString] = $fields;
    if (!$isProfile) {
      $fields = array_merge(array('do_not_import' => array('title' => ts('- do not import -'))),
        self::$_importableFields[$cacheKeyString]
      );
    }
    return $fields;
  }
}
