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

/**
 * One place to store frequently used values in Select Elements. Note that
 * some of the below elements will be dynamic, so we'll probably have a
 * smart caching scheme on a per domain basis
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2014
 * $Id$
 *
 */
class CRM_Hrjobcontract_SelectValues {

  /**
   * Array of insurance plan types.
   * @var array 
   */
  private static $_insurancePlanTypes = NULL;
  
  /**
   * Obtains the different types of insurance plans configured in option values.
   * 
   * @return array
   *   Array of insurance plan types found, of the form [type.value => type.label]
   */
  static function planType() {

    if (!self::$_insurancePlanTypes) {
      $result = civicrm_api3('OptionValue', 'get', [
        'sequential' => 1,
        'option_group_id' => "hrjc_insurance_plantype",
        'options' => ['limit' => 0]
      ]);

      if (!empty($result['values'])) {
        foreach ($result['values'] as $planType) {
          self::$_insurancePlanTypes[$planType['value']] = ts($planType['label']);
        }
      }
    }

    return self::$_insurancePlanTypes;
  }

  /**
   * Obtains the different types of life insurance plans configured in option 
   * values.
   * 
   * @return array
   *   Array of insurance plan types found, of the form [type.value => type.label]
   */
  static function planTypeLifeInsurance() {
    return self::planType();
  }

  /** different types of units of pay
   * @static
   */
  static function &payUnit() {
    static $payUnit = NULL;
    if (!$payUnit) {
      $payUnit = array(
        'Hour' => ts('Hour'),
        'Day' => ts('Day'),
        'Week' => ts('Week'),
        'Month' => ts('Month'),
        'Year' => ts('Year'),
      );
    }
    return $payUnit;
  }

  /** different types of job period type
   * @static
   */
  static function &periodType() {
    static $periodType = NULL;
    if (!$periodType) {
      $periodType = array(
        'Temporary' => ts('Temporary'),
        'Permanent' => ts('Permanent'),
      );
    }
    return $periodType;
  }

  /**different types of job notice unit, hours unit  type
   * @static
   */
  static function &commonUnit() {
    static $commonUnit = NULL;
    if (!$commonUnit) {
      $commonUnit = array(
        'Day' => ts('Day'),
        'Week' => ts('Week'),
        'Month' => ts('Month'),
        'Year' => ts('Year'),
      );
    }
    return $commonUnit;
  }

  /** is Enrolled options list
   * @static
   * @return array
   */
  static function &isEnrolledOptions() {
    static $isEnrolledOptions = NULL;
    if (!$isEnrolledOptions) {
      $isEnrolledOptions = array(
        0 => 'No',
        1 => 'Yes',
        2 => 'Opted Out',
      );
    }
    return $isEnrolledOptions;
  }

  /** (is paid) options
   * @static
   * @return array
   */
  static function &isPaidOptions() {
    static $isPaidOptions = NULL;
    if (!$isPaidOptions) {
      $isPaidOptions = array(
        0 => 'No',
        1 => 'yes',
      );
    }
    return $isPaidOptions;
  }

  /**
   * Get options for a given job roles field along with their database IDs.
   * @param String $fieldName
   * @return array
   */
  public static function buildDbOptions($fieldName) {
    $queryParam = array(1 => array($fieldName, 'String'));
    $query = "SELECT cpv.id, cpv.value, cpv.label from civicrm_option_value cpv
              LEFT JOIN civicrm_option_group cpg on cpv.option_group_id = cpg.id
              WHERE cpg.name = %1";
    $options = array();
    $result = CRM_Core_DAO::executeQuery($query, $queryParam);
    while ($result->fetch()) {
      $options[] =  array( 'id'=>$result->id, 'label'=>$result->label, 'value'=>$result->value );
    }
    return $options;
  }

  /**
   * Get Contract pay scales (grades) list.
   * @return array
   */
  public static function buildPayScales() {
    $query = "SELECT id,pay_scale,currency,amount,periodicity from civicrm_hrpay_scale ".
             " WHERE is_active=1";
    $options = array();
    
    $result = CRM_Core_DAO::executeQuery($query);
    while ($result->fetch()) {
      $label = $result->pay_scale;
      if (!empty($result->currency)) {
        $label .= ' - ' . 
          $result->currency . ' ' . 
          $result->amount . ' per ' . 
          $result->periodicity;
      }      
      $options[] =  array( 'id'=>$result->id, 'label'=> $label);
    }
    
    return $options;
  }

  /**
   * Get enabled currencies
   * @return array
   */
  public static function buildCurrency()
  {
    //currencies_enabled
    $groupData = civicrm_api3('OptionGroup', 'get', array(
      'sequential' => 1,
      'name' => "currencies_enabled",
      'return' => 'id'
    ));
    $currenciesData = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'option_group_id' => $groupData['id'],
      'return' => "value"
    ));
    $currencies = array();
    foreach($currenciesData['values'] as $item)  {
      $value = $item['value'];
      $currencies[] =  array( 'id'=>$value, 'label'=> $value);
    }
    return $currencies;
  }

  /**
   * Get contract hours location
   * @return array
   */
  public static function buildHourLocations()
  {
    $query = "SELECT id,location,standard_hours,periodicity from civicrm_hrhours_location ".
      " WHERE is_active=1";
    $options = array();
    $result = CRM_Core_DAO::executeQuery($query);
    while ($result->fetch()) {
      $label = $result->location." - ".$result->standard_hours." hours per ".$result->periodicity;
      $options[] =  array( 'id'=>$result->id, 'label'=> $label);
    }
    return $options;
  }

  /**
   * Get leave types .
   * @return array
   */
  public static function buildLeaveTypes() {
    $result = civicrm_api3('HRAbsenceType', 'get', array(
      'sequential' => 1,
      'is_active' => 1,
      'options' => array('limit' => 0),
    ));
    $result = $result['values'];
    $options = array();
    foreach ($result as $item) {
      $label = $item['title'];
      $options[] =  array( 'id'=>$item['id'], 'label'=> $label);
    }
    return $options;
  }
}
