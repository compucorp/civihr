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

class CRM_HRAbsence_BAO_HRAbsencePeriod extends CRM_HRAbsence_DAO_HRAbsencePeriod {

  public static function create($params) {
    $entityName = 'HRAbsencePeriod';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();

    foreach (array('end_date', 'start_date') as $yesReallyIWantToSaveTheDataInsteadOfSilentlyThrowingItAway) {
      if (isset($params[$yesReallyIWantToSaveTheDataInsteadOfSilentlyThrowingItAway])) {
        CRM_Core_DAO::executeQuery("UPDATE civicrm_hrabsence_period SET $yesReallyIWantToSaveTheDataInsteadOfSilentlyThrowingItAway = %1 WHERE id = %2", array(
          1 => array($params[$yesReallyIWantToSaveTheDataInsteadOfSilentlyThrowingItAway], 'String'),
          2 => array($instance->id, 'Integer'),
        ));
      }
    }

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
    $dao = new CRM_HRAbsence_DAO_HRAbsencePeriod();
    $dao->copyValues($params);
    return $dao->count();
  }

  /**
   * @return array (int id => string title)
   */
  public static function getPeriods() {
    $periods = civicrm_api3('HRAbsencePeriod', 'get', array());
    $result = CRM_Utils_Array::collect('title', $periods['values']);
    asort($result);
    return $result;
  }

  public static function getDefaultValues($id) {
    $absencePeriod =  civicrm_api3('HRAbsencePeriod', 'get', array('id' => $id));
    return $absencePeriod['values'][$id];
  }

  public static function del($absencePeriodId) {
    $absencePeriod = new CRM_HRAbsence_DAO_HRAbsencePeriod();
    $absencePeriod->id = $absencePeriodId;
    $absencePeriod->find(TRUE);
    $absencePeriod->delete();
  }
}
