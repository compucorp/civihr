<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_HRJob_BAO_Query implements CRM_Contact_BAO_Query_Interface {

  /**
   * static field for all the export/import hrjob fields
   *
   * @var array
   * @static
   */
  static $_hrjobFields = array();

  /**
   * Function get the import/export fields for hrjob
   *
   * @return array self::$_hrjobFields  associative array of hrjob fields
   * @static
   */
  function &getFields() {
    if (!self::$_hrjobFields) {
      self::$_hrjobFields = CRM_HRJob_BAO_HRJob::export();
    }
    return self::$_hrjobFields;
  }

  /**
   * if contributions are involved, add the specific contribute fields
   *
   * @return void
   * @access public
   */
  function select(&$query) {
    if (CRM_Utils_Array::value('position', $query->_returnProperties)) {
      $query->_select['position'] = "civicrm_hrjob.position as hrjob_position";
      $query->_element['position'] = 1;
      $query->_tables['civicrm_hrjob'] = 1;
    }
  }

  function where(&$query) {
    $grouping = NULL;
    foreach (array_keys($query->_params) as $id) {
      if (!CRM_Utils_Array::value(0, $query->_params[$id])) {
        continue;
      }
      $this->whereClauseSingle($query->_params[$id], $query);
    }
  }

  function whereClauseSingle(&$values, &$query) {
    list($name, $op, $value, $grouping, $wildcard) = $values;

    $fields = $this->getFields();

    if (!empty($value) && !is_array($value)) {
      $quoteValue = "\"$value\"";
    }

    $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';

    switch ($name) {
      case 'position':
        $value = $strtolower(CRM_Core_DAO::escapeString($value));
        if ($wildcard) {
          $value = "%$value%";
          $op = 'LIKE';
        }
        $wc = ($op != 'LIKE') ? "LOWER(civicrm_hrjob.position)" : "civicrm_hrjob.position";
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause($wc, $op, $value, "String");
        $query->_qill[$grouping][] = ts('HR job position %1 %2', array(1 => $op, 2 => $quoteValue));
        $query->_tables['civicrm_hrjob'] = $query->_whereTables['civicrm_hrjob'] = 1;
        return;
    }
  }

  function from($name, $mode, $side) {
    $from = NULL;
    switch ($name) {
      case 'civicrm_hrjob':
        $from = " $side JOIN civicrm_hrjob ON civicrm_hrjob.contact_id = contact_a.id ";
        break;
    }
    return $from;
  }
}

