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

class CRM_Hrjobcontract_BAO_HRJobHour extends CRM_Hrjobcontract_DAO_HRJobHour {
  /**
   * static field for the HRJobHour information that we can potentially import
   *
   * @var array
   * @static
   */
  static $_importableFields = array();

  /**
   * Create a new HRJobHour based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRJob_DAO_HRJobHour|NULL
   *
   */
  public static function create($params) {
    $hook = empty($params['id']) ? 'create' : 'edit';
    $instance = parent::create($params);
    
    $currentInstanceResult = civicrm_api3('HRJobHour', 'get', array(
        'sequential' => 1,
        'id' => $instance->id,
    ));
    
    $currentInstance = CRM_Utils_Array::first($currentInstanceResult['values']);
    $revisionResult = civicrm_api3('HRJobContractRevision', 'get', array(
        'sequential' => 1,
        'id' => $currentInstance['jobcontract_revision_id'],
    ));
    $revision = CRM_Utils_Array::first($revisionResult['values']);
    
    if ($hook == 'create' && empty($params['import'])) {
        $result = civicrm_api3('HRJobRole', 'get', array(
          'sequential' => 1,
          'jobcontract_revision_id' => $revision['role_revision_id'],
          'options' => array('limit' => 1),
        ));
        if (!empty($result['values'])) {
            $role = CRM_Utils_Array::first($result['values']);
            CRM_Hrjobcontract_BAO_HRJobRole::create(array('id' => $role['id'], 'hours' => $instance->hours_amount, 'role_hours_unit' => $instance->hours_unit));
        }
    }
    
    return $instance;
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
  static function importableFields($contactType = 'HRJobHour',
    $status          = FALSE,
    $showAll         = FALSE,
    $isProfile       = FALSE,
    $checkPermission = TRUE,
    $withMultiCustomFields = FALSE
  ) {
    if (empty($contactType)) {
      $contactType = 'HRJobHour';
    }

    $cacheKeyString = "";
    $cacheKeyString .= $status ? '_1' : '_0';
    $cacheKeyString .= $showAll ? '_1' : '_0';
    $cacheKeyString .= $isProfile ? '_1' : '_0';
    $cacheKeyString .= $checkPermission ? '_1' : '_0';

    $fields = CRM_Utils_Array::value($cacheKeyString, self::$_importableFields);

    if (!$fields) {
      $fields = CRM_Hrjobcontract_DAO_HRJobHour::import();

      $fields = array_merge($fields, CRM_Hrjobcontract_DAO_HRJobHour::import());

      //Sorting fields in alphabetical order(CRM-1507)
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