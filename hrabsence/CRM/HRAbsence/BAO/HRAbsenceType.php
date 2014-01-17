<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.0                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2013                                |
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

class CRM_HRAbsence_BAO_HRAbsenceType extends CRM_HRAbsence_DAO_HRAbsenceType {

  public static function create($params) {
  	$entityName = 'HRAbsenceType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    if (is_numeric(CRM_Utils_Array::value('is_primary', $params)) || empty($params['id'])) {
      CRM_Core_BAO_Block::handlePrimary($params, get_class());
    }
    if (!array_key_exists('name', $params) && !array_key_exists('id', $params)) {
    	$params['name'] = CRM_Utils_String::munge($params['title']);
    }

    $result = civicrm_api3('activity_type', 'get', array());
    if (!CRM_Utils_Array::value('is_error', $result)) {
      $weight = count($result["values"]);
      $activityTypeId = array_search($params["name"], $result["values"]);
      if(!$activityTypeId) {
        $weight = $weight+1;
        $paramsCreate = array(
          'weight' => $weight,
          'label' => $params["name"],
          'filter' => 0,
          'is_active' => 1,
          'is_optgroup' => 0,
          'is_default' => 0,
        );
        $resultCreateActivityType = civicrm_api3('activity_type', 'create', $paramsCreate);
        $activityTypeId = $resultCreateActivityType["id"];
      }
      if(CRM_Utils_Array::value('allow_debits', $params, TRUE)) {
        $params["debit_activity_type_id"] = $activityTypeId;        
      }
      if(CRM_Utils_Array::value('allow_credits', $params)) {
        $params["credit_activity_type_id"] = $activityTypeId;        
      }
    }
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
    $dao = new CRM_HRAbsence_DAO_HRAbsenceType();
    $dao->copyValues($params);
    return $dao->count();
  }  
}
