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

class CRM_Hrjobcontract_BAO_HRJobPay extends CRM_Hrjobcontract_DAO_HRJobPay {
  /**
   * static field for the HRJobPay information that we can potentially import
   *
   * @var array
   * @static
   */
  static $_importableFields = array();

  /**
   * Create a new HRJobPay based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRJob_DAO_HRJobPay|NULL
   *
   */
  public static function create($params) {
      if (is_array($params['annual_benefits'])) {
          $params['annual_benefits'] = json_encode($params['annual_benefits']);
      }
      if (is_array($params['annual_deductions'])) {
          $params['annual_deductions'] = json_encode($params['annual_deductions']);
      }
      return parent::create($params);
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
  static function importableFields($contactType = 'HRJobPay',
    $status          = FALSE,
    $showAll         = FALSE,
    $isProfile       = FALSE,
    $checkPermission = TRUE,
    $withMultiCustomFields = FALSE
  ) {
    if (empty($contactType)) {
      $contactType = 'HRJobPay';
    }

    $cacheKeyString = "";
    $cacheKeyString .= $status ? '_1' : '_0';
    $cacheKeyString .= $showAll ? '_1' : '_0';
    $cacheKeyString .= $isProfile ? '_1' : '_0';
    $cacheKeyString .= $checkPermission ? '_1' : '_0';

    $fields = CRM_Utils_Array::value($cacheKeyString, self::$_importableFields);

    if (!$fields) {
      $fields = CRM_Hrjobcontract_DAO_HRJobPay::import();

      $fields = array_merge($fields, CRM_Hrjobcontract_DAO_HRJobPay::import());

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