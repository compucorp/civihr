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
class CRM_HRJob_SelectValues {

  /**CRM/HRJob/SelectValues.php
   * different types of plan
   * @static
   */
  static function &planType() {
    static $planType = NULL;
    if (!$planType) {
      $planType = array(
        'Family' => ts('Family'),
        'Individual' => ts('Individual'),
      );
    }
    return $planType;
  }

  /** different types of life insurance plan
   * @static
   */
  static function &planTypeLifeInsurance() {
    static $planTypeLifeInsurance = NULL;
    if (!$planTypeLifeInsurance) {
      $planTypeLifeInsurance = array(
        'Family' => ts('Family'),
        'Individual' => ts('Individual'),
      );
    }
    return $planTypeLifeInsurance;
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
}
