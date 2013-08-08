<?php
// $Id$

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
class CRM_HRReport_Form_Contact_HRSummary extends CRM_Report_Form {
  //FIXME: extend should be a subtype suitable for CiviHR applicants
  protected $_customGroupExtends = array('Individual');

  function __construct() {
    $this->_emailField = FALSE;
    $this->_customGroupJoin = 'INNER JOIN';
    $this->_customGroupGroupBy = TRUE;
    $this->_exposeContactID    = FALSE;

    $this->_columns = array(
      'civicrm_contact' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          // _exposeContactID already set by default which will expose contact - ID
          'id' =>
          array('title' => ts('People'),
            'default' => TRUE,
            'statistics' => array('count_distinct' => ts('People'),),
          ),
          'gender_id' =>
          array(
            'title' => ts('Gender'),
          ),
        ),
        'group_bys' => array(
          'gender_id' =>
          array(
            'title' => ts('Gender'),
          ),
        ),
        'filters' =>
        array(
          'id' =>
          array('title' => ts('Contact ID'),
            'no_display' => TRUE,
            'type' => CRM_Utils_Type::T_INT,
          ),
          'gender_id' =>
          array(
            'title' => ts('Gender'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id'),
          ),
        ),
        'grouping' => array('contact-fields' => 'Stats and Work Fields'),
      ),
      'civicrm_address' =>
      array(
        'dao' => 'CRM_Core_DAO_Address',
        'fields' =>
        array(
          'city' =>
          array('title' => ts('Work City'),
          ),
          'postal_code' =>
          array('title' => ts('Work Postal Code'),
          ),
          'state_province_id' =>
          array('title' => ts('Work State/Province'),
          ),
          'country_id' =>
          array('title' => ts('Work Country'),
          ),
        ),
        'group_bys' =>
        array(
          'city' =>
          array('title' => ts('Work City'),
          ),
          'postal_code' =>
          array('title' => ts('Work Postal Code'),
          ),
          'state_province_id' =>
          array('title' => ts('Work State/Province'),
          ),
          'country_id' =>
          array('title' => ts('Work Country'),
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_group' =>
      array(
        'dao' => 'CRM_Contact_DAO_GroupContact',
        'alias' => 'cgroup',
        'filters' =>
        array(
          'gid' =>
          array(
            'name' => 'group_id',
            'title' => ts('Group'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'group' => TRUE,
            'options' => CRM_Core_PseudoConstant::group(),
            'type' => CRM_Utils_Type::T_INT,
          ),
        ),
      ),
      'civicrm_hrjob' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJob',
        'fields' =>
        array(
          'title' =>
          array(
            'title' => ts('Title'),
          ),
          'contract_type' => 
          array(
            'title' => ts('Contract Type'),
          ),
          'level_type' => 
          array(
            'title' => ts('Level'),
          ),
          'period_type' => 
          array(
            'title' => ts('Period Type'),
          ),
          'location' => 
          array(
            'title' => ts('Job Location'),
          ),
        ),
        'filters' =>
        array(
          'position' =>
          array('title' => ts('Position'),
            'operator' => 'like',
            'type' => CRM_Report_Form::OP_STRING,
          ),
          'title' =>
          array('title' => ts('Title'),
            'operator' => 'like',
            'type' => CRM_Report_Form::OP_STRING,
          ),
          'is_tied_to_funding' =>
          array('title' => ts('Is Tied to Funding'),
            'default' => NULL,
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('' => ts('Any'), '0' => ts('No'), '1' => ts('Yes')),
          ),
          'contract_type' =>
          array('title' => ts('Contract Type'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJob', 'contract_type'),
          ),
          'level_type' =>
          array('title' => ts('Level'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJob', 'level_type'),
          ),
          'period_type' =>
          array('title' => ts('Period Type'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJob', 'period_type'),
          ),
          'period_start_date' =>
          array('title' => ts('Job Start Date'),
            'type' => CRM_Utils_Type::T_DATE,
            'operatorType' => CRM_Report_Form::OP_DATE,
          ),
          'period_end_date' =>
          array('title' => ts('Job End Date'),
            'type' => CRM_Utils_Type::T_DATE,
            'operatorType' => CRM_Report_Form::OP_DATE
          ),
          'location' =>
          array('title' => ts('Location'),
            'operator' => 'like',
            'type' => CRM_Report_Form::OP_STRING,
          ),
        ),
         'group_bys' =>
        array(
          'title' =>
          array('title' => ts('Job Title'),
          ),
          'level_type' =>
          array('title' => ts('Level'),
          ),
          'location' => 
          array('title' => ts('Job Location'),
          ),
        ),
        'grouping' => array('contact-fields' => 'Stats and Work Fields'),
      ),
      'civicrm_hrjob_health' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobHealth',
        'fields' =>
        array(
          'provider' => 
          array(
            'title' => ts('Healthcare Provider'),
          ),
          'plan_type' => 
          array(
            'title' => ts('Plan Type'),
          ),
        ),
        'filters' =>
        array(
          'provider' =>
          array('title' => ts('Healthcare Provider'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJobHealth', 'provider'),
          ),
          'plan_type' =>
          array('title' => ts('Plan Type'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJobHealth', 'plan_type'),
          ),
        ),
        'grouping' => array('contact-fields' => 'Stats and Work Fields'),
      ),
      'civicrm_hrjob_hour' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobHour',
        'fields' =>
        array(
          'hours_type' => 
          array(
            'title' => ts('Hours Type'),
          ),
          'hours_unit' => 
          array(
            'title' => ts('Hours Unit'),
          ),
        ),
        'filters' =>
        array(
          'hours_type' =>
          array('title' => ts('Hours Type'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJobHour', 'hours_type'),
          ),
          'hours_amount' =>
          array('title' => ts('Hours Amount'),
            'type' => CRM_Report_Form::OP_INT,
          ),
          'hours_unit' =>
          array('title' => ts('Hours Unit'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJobHour', 'hours_unit'),
          ),
        ),
        'grouping' => array('contact-fields' => 'Stats and Work Fields'),
      ),
      'civicrm_hrjob_pay' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobPay',
        'fields' =>
        array(
          'pay_grade' => 
          array(
            'title' => ts('Pay Grade'),
          ),
          'pay_unit' => 
          array(
            'title' => ts('Pay Unit'),
          ),
        ),
        'filters' =>
        array(
          'pay_grade' =>
          array('title' => ts('Pay Grade'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJobPay', 'pay_grade'),
          ),
          'pay_amount' =>
          array('title' => ts('Pay Amount'),
            'type' => CRM_Report_Form::OP_INT,
          ),
          'pay_unit' =>
          array('title' => ts('Pay Unit'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJobPay', 'pay_unit'),
          ),
        ),
         'group_bys' =>
        array(
          'pay_grade' =>
          array('title' => ts('Job Pay Grade'),
          ),
        ),
        'grouping' => array('contact-fields' => 'Stats and Work Fields'),
      ),
      'civicrm_hrjob_pension' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobPension',
        'fields' =>
        array(
          'contrib_pct' => 
          array(
            'title' => ts('Contribution Percentage'),
          ),
        ),
        'filters' =>
        array(
          'is_enrolled' =>
          array('title' => ts('Is Enrolled'),
            'default' => NULL,
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('' => ts('Any'), '0' => ts('No'), '1' => ts('Yes')),
          ),
          'contrib_pct' =>
          array('title' => ts('Contribution Percentage'),
            'type' => CRM_Report_Form::OP_INT,
          ),
        ),
        'grouping' => array('contact-fields' => 'Stats and Work Fields'),
      ),
      'civicrm_hrjob_role' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobRole',
        'fields' => 
        array(
          'department' =>
          array('title' => ts('Department'),
          ),
          'cost_center' =>
          array('title' => ts('Cost Center'),
          ),
          'region' =>
          array('title' => ts('Region/Country'),
          ),
        ),
        'group_bys' =>
        array(
          'department' =>
          array('title' => ts('Department'),
          ),
          'cost_center' =>
          array('title' => ts('Cost Center'),
          ),
          'region' =>
          array('title' => ts('Region/Country'),
          ),
        ),
        'grouping' => array('contact-fields' => 'Stats and Work Fields'),
      ),
      'civicrm_hrjob_leave' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobLeave',
        'fields' =>
        array(
          'leave_type' =>
          array('title' => ts('Leave Type'),
          ),
        ),
        'grouping' => array('contact-fields' => 'Stats and Work Fields'),
       ),
    );

    parent::__construct();

    $customGroupName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Constituent Information', 'table_name', 'title');
    unset($this->_columns[$customGroupName]);

    $this->_columns['civicrm_hrjob']['fields']['job_positions'] =
      array(
       'name' => 'contact_id',
       'title' => ts('Job Positions'),
       'statistics' => array('count' => ts('Job Positions'),),
       'grouping'   => 'contact-fields',
      );
    $this->_columns['civicrm_hrjob_hour']['fields']['fte'] =
      array(
       'name' => 'hours_amount',
       'title' => ts('Full Time Equivalents'),
       'type' => CRM_Utils_Type::T_INT,
       'statistics' => array('fte' => ts('Full Time Equivalents'),),
       'grouping'   => 'contact-fields',
      );
    $this->_columns['civicrm_hrjob_pay']['fields']['monthly_cost_eq'] = array(
       'name' => 'pay_amount',      
       'title' => ts('Monthly Cost Equivalents'),
       'type' => CRM_Utils_Type::T_INT,
       'statistics' => array('mce' => ts('Monthly Cost Equivalents'),),
       'grouping'   => 'contact-fields',
    );
    $this->_columns['civicrm_hrjob_pay']['fields']['annual_cost_eq'] = array(
       'name' => 'pay_amount',      
       'title' => ts('Annual Cost Equivalents'),
       'type' => CRM_Utils_Type::T_INT,
       'statistics' => array('sum' => ts('Annual Cost Equivalents'),),
       'grouping'   => 'contact-fields',
    );
  }

  function select() {
    parent::select();
    
    $jobTables = array('civicrm_hrjob', 'civicrm_hrjob_hour', 'civicrm_hrjob_pay', 'civicrm_hrjob_role',
      'civicrm_hrjob_health', 'civicrm_hrjob_pension', 'civicrm_hrjob_leave');
    foreach ($this->_columns as $tableName => $table) {
      if (!empty($table['fields'])) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (in_array($tableName, $jobTables)) {
            $this->_jobField = TRUE;
          }
          if (CRM_Utils_Array::value('required', $field) ||
              CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
            if (CRM_Utils_Array::value('statistics', $field)) {
              foreach ($field['statistics'] as $stat => $label) {
                $alias = "{$tableName}_{$fieldName}_{$stat}";
                switch (strtolower($stat)) {
                case 'mce':
                  $select[] = "(SUM({$field['dbAlias']})/12) as {$tableName}_{$fieldName}_{$stat}";
                  $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['title'] = $label;
                  $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['type'] = $field['type'];
                  $this->_statFields[$label] = $alias;
                  break;
                case 'fte':
                  $select[] = "(SUM({$field['dbAlias']})/40) as {$tableName}_{$fieldName}_{$stat}";
                  $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['title'] = $label;
                  $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['type'] = $field['type'];
                  $this->_statFields[$label] = $alias;
                  break;
                }
              }
            }
          }
        }
      }
    }
    if (!empty($select)) {
      $this->_select .= ", ".implode(', ', $select);
    }
  } 

  function from() {
    $this->_from .= "
      FROM  civicrm_contact  {$this->_aliases['civicrm_contact']} {$this->_aclFrom}";

    if ($this->_jobField) {
      $this->_from .= "
      INNER JOIN civicrm_hrjob {$this->_aliases['civicrm_hrjob']}
             ON ({$this->_aliases['civicrm_hrjob']}.contact_id = {$this->_aliases['civicrm_contact']}.id)
      LEFT JOIN civicrm_hrjob_pay {$this->_aliases['civicrm_hrjob_pay']}
             ON ({$this->_aliases['civicrm_hrjob_pay']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_hrjob_health {$this->_aliases['civicrm_hrjob_health']}
             ON ({$this->_aliases['civicrm_hrjob_health']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_hrjob_hour {$this->_aliases['civicrm_hrjob_hour']}
             ON ({$this->_aliases['civicrm_hrjob_hour']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_hrjob_pension {$this->_aliases['civicrm_hrjob_pension']}
             ON ({$this->_aliases['civicrm_hrjob_pension']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)";

      foreach ($this->_columns as $tableName => $table) {
        if (!empty($table['fields'])) {
          foreach ($table['fields'] as $fieldName => $field) {
            if (CRM_Utils_Array::value('required', $field) ||
                CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
              if ($tableName == 'civicrm_hrjob_role') {
                $this->_from .= "LEFT JOIN civicrm_hrjob_role {$this->_aliases['civicrm_hrjob_role']}
                  ON ({$this->_aliases['civicrm_hrjob_role']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)";
              }
              elseif ($tableName == 'civicrm_hrjob_leave') {
                $this->_from .= "LEFT JOIN civicrm_hrjob_leave {$this->_aliases['civicrm_hrjob_leave']}
                  ON ({$this->_aliases['civicrm_hrjob_leave']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)";
              }
            }
          }
        }
      }
    }

    if ($this->_addressField) {
      $locationTypes = CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id', array('flip' => TRUE));
      $workLocTypeId = CRM_Utils_Array::value('Work', $locationTypes);
      $this->_from  .= "
                 LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
                           ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id) AND
                               {$this->_aliases['civicrm_address']}.location_type_id = {$workLocTypeId}\n";
    }
  }

  function statistics(&$rows) {
    $statistics = parent::statistics($rows);

    if (!empty($this->_statFields)) {
      // after removing group-by we have
      $sql = "{$this->_select} {$this->_from} {$this->_where} {$this->_having} {$this->_orderBy} {$this->_limit}";
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ($dao->fetch()) {
        foreach ($this->_statFields as $title => $alias) {
          $statistics['counts'][$alias] = array(
            'title' => $title,
            'value' => $dao->$alias,
            'type' => CRM_Utils_Type::T_INT,
          );
        }
      }
    }
    return $statistics;
  }

  function alterDisplay(&$rows) {
    $entryFound = FALSE;
    $gender     = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id');

    foreach ($rows as $rowNum => $row) {
      $entryFound =
        $this->alterDisplayAddressFields($row, $rows, $rowNum, 'civihr/detail', 'List all contact(s) for this ') ? TRUE : $entryFound;

      if (array_key_exists('civicrm_contact_gender_id', $row)) {
        if (CRM_Utils_Array::value('civicrm_contact_gender_id', $row)) {
          $rows[$rowNum]['civicrm_contact_gender_id'] = CRM_Utils_Array::value($row['civicrm_contact_gender_id'], $gender);
        }
        $entryFound = TRUE;
      }

      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        break;
      }
    }
  }
}

