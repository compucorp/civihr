<?php

/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.2                                                 |
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
class CRM_HRReport_Form_Activity_PublicHoliday extends CRM_Report_Form {

  function __construct() {
    $this->_columns = array(
      'civicrm_activity' =>
      array(
        'dao' => 'CRM_Activity_DAO_Activity',
        'fields' =>
        array(
          'id' =>
          array(
            'required' => TRUE,
            'no_display' => TRUE,
          ),
          'activity_subject' =>
          array(
            'title' => ts('Public Holiday'),
            'required' => TRUE,
          ),
          'activity_date_time' =>
          array(
            'title' => ts('Falls on'),
            'required' => TRUE,
          ),
        ),
        'filters' =>
        array(
          'activity_date_time' =>
          array(
            'operatorType' => CRM_Report_Form::OP_DATE,
            'default' => 'this.year'
          ),
          'activity_status_id' =>
          array(
            'name' => 'status_id',
            'title' => ts('Enabled?'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('' => '', '1' => ts('Yes'), '2' => ts('No')),
          ),
        ),
        'order_bys' =>
        array(
          'activity_date_time' =>
          array('title' => ts('Activity Date')),
        ),
        'grouping' => 'activity-fields',
        'alias' => 'activity',
      ),
    );

    parent::__construct();
  }

  function select() {
    $select = array();
    $this->_columnHeaders = array();
    foreach ($this->_columns as $tableName => $table) {

      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) ||
            CRM_Utils_Array::value($fieldName, $this->_params['fields'])
          ) {
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['no_display'] = CRM_Utils_Array::value('no_display', $field);
          }
        }
      }
    }

    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  function from() {
    $this->_from = "
        FROM civicrm_activity {$this->_aliases['civicrm_activity']}
        {$this->_aclFrom}
";

  }

  function where() {
    $params = array(
      'sequential' => 1,
      'option_group_id' => 'activity_type',
      'name' => 'Public Holiday',
      'return' => 'value',
    );

    $publicHolidayID = civicrm_api3('OptionValue', 'getvalue', $params);
    $this->_where = " WHERE {$this->_aliases['civicrm_activity']}.activity_type_id = {$publicHolidayID} AND
                                {$this->_aliases['civicrm_activity']}.is_test = 0 AND
                                {$this->_aliases['civicrm_activity']}.is_deleted = 0 AND
                                {$this->_aliases['civicrm_activity']}.is_current_revision = 1";

    $clauses = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('type', $field) & CRM_Utils_Type::T_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($field['name'], $relative, $from, $to, $field['type']);
          }

          if (array_key_exists("{$fieldName}_value", $this->_params)) {
            if ($field['name'] == 'status_id' && $this->_params["{$fieldName}_value"]) {
              $status =  CRM_Core_PseudoConstant::activityStatus();

              if($this->_params["{$fieldName}_value"] == 1) {
                $this->_params["{$fieldName}_value"] = array_search('Scheduled', $status);
              }
              elseif($this->_params["{$fieldName}_value"] == 0) {
                $this->_params["{$fieldName}_value"] = array_search('Cancelled', $status);
              }
              $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
              $clause = $this->whereClause($field,
                $op,
                CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }
          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }

    if (empty($clauses)) {
      $this->_where .= " ";
    }
    else {
      $this->_where .= " AND " . implode(' AND ', $clauses);
    }

    if ($this->_aclWhere) {
      $this->_where .= " AND {$this->_aclWhere} ";
    }
  }

  function postProcess() {
    parent::postProcess();
  }

}

