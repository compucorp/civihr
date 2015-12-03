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

class CRM_Appraisals_BAO_AppraisalCriteria extends CRM_Appraisals_DAO_AppraisalCriteria {

  public static function create($params) {
    $className = 'CRM_Appraisals_DAO_AppraisalCriteria';
    $entityName = 'AppraisalCriteria';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
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
    $dao = new CRM_Appraisals_DAO_AppraisalCriteria();
    $dao->copyValues($params);
    return $dao->count();
  }
  
  public static function getDefaultValues($id) {
    $appraisalCriteria =  civicrm_api3('AppraisalCriteria', 'get', array('id' => $id));
    return $appraisalCriteria['values'][$id];
  }
  
  public static function del($appraisalCriteriaId) {
    $appraisalCriteria = new CRM_Appraisals_DAO_AppraisalCriteria();
    $appraisalCriteria->id = $appraisalCriteriaId;
    $appraisalCriteria->find(TRUE);
    $appraisalCriteria->delete();
  }
  
  static function setIsActive($id, $is_active) {
    return CRM_Core_DAO::setFieldValue('CRM_Appraisals_DAO_AppraisalCriteria', $id, 'is_active', $is_active);
  }
}
