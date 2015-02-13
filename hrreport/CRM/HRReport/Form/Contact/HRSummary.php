<?php
// $Id$

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
    $this->_customGroupJoin = 'LEFT JOIN';
    $this->_customGroupGroupBy = TRUE;
    $this->_exposeContactID = FALSE;

    $this->_columns = array(
      'civicrm_contact' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          // _exposeContactID already set by default which will expose contact - ID
          'id' =>
          array(
            'title' => ts('People'),
            'default' => TRUE,
            'statistics' => array('count_distinct' => ts('People'),),
            'grouping'   => array('stats-fields' => 'Stats'),
          ),
          'gender_id' => array('title' => ts('Gender'),),
        ),
        'group_bys' => array(
          'gender_id' => array('title' => ts('Gender'),),
        ),
        'filters' =>
        array(
          'id' =>
          array(
            'title' => ts('Contact ID'),
            'no_display' => TRUE,
            'type' => CRM_Utils_Type::T_INT,
          ),
          'gender_id' => array('title' => ts('Gender'),),
        ),
        'grouping' => array('contact-fields' => 'Personal Details'),
      ),

      'civicrm_address' =>
      array(
        'dao' => 'CRM_Core_DAO_Address',
        'fields' =>
        array(
          'city' =>
          array(
            'title' => ts('Work City'),
          ),
          'postal_code' =>
          array(
            'title' => ts('Work Postal Code'),
          ),
          'state_province_id' =>
          array(
            'title' => ts('Work State/Province'),
          ),
          'country_id' =>
          array(
            'title' => ts('Work Country'),
          ),
        ),
        'group_bys' =>
        array(
          'city' =>
          array(
            'title' => ts('Work City'),
          ),
          'postal_code' =>
          array(
            'title' => ts('Work Postal Code'),
          ),
          'state_province_id' =>
          array(
            'title' => ts('Work State/Province'),
          ),
          'country_id' =>
          array(
            'title' => ts('Work Country'),
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
          'hrjob_contract_type' => array(),
          'hrjob_period_type'   => array(),
          'hrjob_location'      => array(),
          'hrjob_is_primary' => array(),
        ),

        'group_bys' =>
        array(
          'hrjob_contract_type' => array(),
          'hrjob_period_type'   => array(),
          'hrjob_location'      => array(),
        ),

        'filters' =>
        array(
          'hrjob_contract_type' => array(),
          'hrjob_location'      => array(),
          'hrjob_position'      => array(),

          //date fields
          'hrjob_period_start_date' => array(),
          'hrjob_period_end_date'   => array(),
          'hrjob_is_primary' => array(
            'title' => ts('Job Is Primary?'),
            'default' => 1,
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('' => ts('Any'), '0' => ts('No'), '1' => ts('Yes')),
          ),
        ),
        'grouping' => array('job-fields' => ts('Job')),
      ),

      'civicrm_hrjob_health' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobHealth',
        'fields' =>
        array(
          'hrjob_health_provider' => array(),
          'hrjob_health_plan_type' => array(),
          'hrjob_health_provider_life_insurance' => array(),
          'hrjob_life_insurance_plan_type' => array(),
        ),
        'filters' =>
        array(
          'hrjob_health_plan_type' => array(),
          'hrjob_life_insurance_plan_type' => array(),
        ),
        'group_bys' =>
        array(
          'hrjob_health_provider' => array(),
          'hrjob_health_plan_type' => array(),
          'hrjob_health_provider_life_insurance' => array(),
          'hrjob_life_insurance_plan_type' => array(),
        ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjob_health_provider' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
        array(
          'organization_name' =>
          array(
            'title' => ts('Job Healthcare Provider'),
            'no_repeat' => TRUE,
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'id' =>
          array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
        'filters' =>
        array(
          'organization_name' =>
          array(
            'title' => ts('Job Healthcare Provider'),
            'operatorType' => CRM_Report_Form::OP_STRING,
          ),
        ),
      ),

      'civicrm_hrjob_health_life_provider' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
        array(
          'display_name' =>
          array('title' => ts('Job life insurance Provider'),
            'no_repeat' => TRUE,
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'id' =>
          array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
        'filters' =>
        array(
          'display_name' =>
          array(
            'title' => ts('Job life insurance Provider'),
            'operatorType' => CRM_Report_Form::OP_STRING,
          ),
        ),
      ),

      'civicrm_hrjob_hour' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobHour',
        'fields' =>
        array(
          'hrjob_hours_type'   => array(),
          'hrjob_hours_unit'   => array(),
        ),
        'filters' =>
        array(
          'hrjob_hours_type'   => array(),
          'hrjob_hours_unit'   => array(),
        ),
        'group_bys' =>
        array(
          'hrjob_hours_type'   => array(),
          'hrjob_hours_unit'   => array(),
        ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjob_pay' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobPay',
        'fields' =>
        array(
          'hrjob_is_paid' => array(),
          'hrjob_pay_currency' => array(),
        ),
        'filters' =>
        array(
          'hrjob_pay_grade' => array(),
        ),
        'group_bys' =>
        array(
          'hrjob_is_paid' => array(),
          'hrjob_pay_currency' => array(),
        ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjob_pension' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobPension',
        'fields' =>
        array(
          'hrjob_is_enrolled' => array(),
        ),
        'filters' =>
        array(
          'hrjob_is_enrolled' => array(),
        ),
        'group_bys' =>
        array(
          'hrjob_is_enrolled' => array(),
        ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjob_role' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobRole',
        'fields' =>
        array(
          'hrjob_role_department' => array(),
          'hrjob_role_level_type' => array(),
        ),
        'filters' =>
        array(
          'hrjob_role_department' => array(),
          'hrjob_role_level_type' => array(),
        ),
        'group_bys' =>
        array(
          'hrjob_role_department' => array(),
          'hrjob_role_level_type' => array(),
        ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjob_leave' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobLeave',
        'fields' =>
        array(
          'hrjob_leave_id' =>
          array(
            'name' => 'id',
            'no_display' => TRUE,
            'type'       => CRM_Utils_Type::T_INT,
          ),
        ),
      ),
    );
    parent::__construct();
    $config = CRM_Core_Config::singleton();
    // stats fields
    $this->_columns['civicrm_hrjob']['fields']['job_positions'] =
      array(
        'name' => 'contact_id',
        'title' => ts('Job Positions'),
        'statistics' => array('count' => ts('Job Positions'),),
        'grouping' => 'stats-fields',
      );
    $this->_columns['civicrm_hrjob_hour']['fields']['full_time_eq'] =
      array(
        'name' => 'hours_fte',
        'title' => ts('Full Time Equivalents'),
        'type' => CRM_Utils_Type::T_FLOAT,
        'statistics' => array('sum' => ts('Full Time Equivalents'),),
        'dbAlias'  => '(hrjob_hour_civireport.fte_num / hrjob_hour_civireport.fte_denom)',
        'grouping' => 'stats-fields',
      );
    $this->_columns['civicrm_hrjob_pay']['fields']['monthly_cost_eq'] = array(
      'name' => 'pay_annualized_est',
      'title' => ts('Monthly Cost Equivalents').' ('.$config->defaultCurrencySymbol.')',
      'type' => CRM_Utils_Type::T_INT,
      'dbAlias'  => '(SUM(hrjob_pay_civireport.pay_annualized_est)/12)',
      'grouping' => 'stats-fields',
    );
    $this->_columns['civicrm_hrjob_pay']['fields']['annual_cost_eq'] = array(
      'name' => 'pay_annualized_est',
      'title' => ts('Annual Cost Equivalents'),
      'type' => CRM_Utils_Type::T_INT,
      'statistics' => array('sum' => ts('Annual Cost Equivalents').' ('.$config->defaultCurrencySymbol.')',),
      'grouping' => 'stats-fields',
    );

    $customGroupName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Constituent Information', 'table_name', 'title');
    unset($this->_columns[$customGroupName]);

    // keep relevant group by fields in custom sets
    $customFieldsToRetain =
      array(
        'Career'         => array('Occupation Type', 'Full-time / Part-time', 'Paid / Unpaid'),
        'Immigration'    => array('Visa Type'),
        'Identification' => array('Type', 'Country', 'State/Province'),
        'Qualifications' => array('Category of Skill', 'Name of Skill', 'Level of Skill', 'Certification Acquired?'),
        'Medical & Disability' => array('Type', 'Special Requirements'),
      );
    foreach ($customFieldsToRetain as $tableTitle => $fieldLabel) {
      $customGroupName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', $tableTitle, 'table_name', 'title');
      if ($customGroupName) {
        foreach (array('fields', 'group_bys') as $flds) {
          foreach ($this->_columns[$customGroupName][$flds] as $fieldKey => $fieldVal) {
            if (!in_array($fieldVal['title'], $fieldLabel)) {
              unset($this->_columns[$customGroupName][$flds][$fieldKey]);
            }
          }
        }
      }
    }
  }

  function from() {
    $this->_from = "
      FROM  civicrm_contact  {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
      LEFT JOIN civicrm_hrjob {$this->_aliases['civicrm_hrjob']}
             ON ({$this->_aliases['civicrm_hrjob']}.contact_id = {$this->_aliases['civicrm_contact']}.id)
      LEFT JOIN civicrm_hrjob_pay {$this->_aliases['civicrm_hrjob_pay']}
             ON ({$this->_aliases['civicrm_hrjob_pay']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_hrjob_health {$this->_aliases['civicrm_hrjob_health']}
             ON ({$this->_aliases['civicrm_hrjob_health']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_hrjob_hour {$this->_aliases['civicrm_hrjob_hour']}
             ON ({$this->_aliases['civicrm_hrjob_hour']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_hrjob_pension {$this->_aliases['civicrm_hrjob_pension']}
             ON ({$this->_aliases['civicrm_hrjob_pension']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_contact {$this->_aliases['civicrm_hrjob_health_provider']}
          ON {$this->_aliases['civicrm_hrjob_health_provider']}.id={$this->_aliases['civicrm_hrjob_health']}.provider
      LEFT JOIN civicrm_contact {$this->_aliases['civicrm_hrjob_health_life_provider']}
          ON {$this->_aliases['civicrm_hrjob_health_life_provider']}.id={$this->_aliases['civicrm_hrjob_health']}.provider_life_insurance
      LEFT JOIN civicrm_hrjob_role {$this->_aliases['civicrm_hrjob_role']}
                ON ({$this->_aliases['civicrm_hrjob_role']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)";

    foreach ($this->_columns as $tableName => $table) {
      if (!empty($table['fields'])) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) || !empty($this->_params['fields'][$fieldName])) {
            if ($tableName == 'civicrm_hrjob_leave') {
              $this->_from .= " LEFT JOIN civicrm_hrjob_leave {$this->_aliases['civicrm_hrjob_leave']}
                  ON ({$this->_aliases['civicrm_hrjob_leave']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)";
            }
          }
        }
      }
    }

    if ($this->_addressField) {
      $locationTypes = CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id', array('flip' => TRUE));
      $workLocTypeId = CRM_Utils_Array::value('Work', $locationTypes);
      $this->_from .= "
                 LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
                           ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id) AND
                               {$this->_aliases['civicrm_address']}.location_type_id = {$workLocTypeId}\n";
    }
  }

  function customDataFrom() {
    parent::customDataFrom();
    $params = array('name'=>'HRJob_Summary');
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomGroup', $params, $cGrp);
    if (!$this->isFieldSelected($this->_columns[$cGrp['table_name']])) {
      $mapper = CRM_Core_BAO_CustomQuery::$extendsMap;
      $extendsTable = $mapper[$cGrp['extends']];
      $baseJoin = CRM_Utils_Array::value($cGrp['extends'], $this->_customGroupExtendsJoin, "{$this->_aliases[$extendsTable]}.id");
      $this->_from .= " LEFT JOIN {$cGrp['table_name']} {$this->_aliases[$cGrp['table_name']]} ON {$this->_aliases[$cGrp['table_name']]}.entity_id = {$baseJoin}";
    }
  }

  function where() {
    parent::where();
    $params = array('name'=>'Final_Termination_Date');
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomField', $params, $cField);
    $params = array('name'=>'HRJob_Summary');
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomGroup', $params, $cGrp);
    $dbAlias = $this->_columns[$cGrp['table_name']]['fields']["custom_{$cField['id']}"]['dbAlias'];
    if (!$this->isFieldSelected($this->_columns[$cGrp['table_name']])) {
      $whereClauses[] = "({$dbAlias} >= CURDATE() OR {$dbAlias} IS NULL)";
    }

    $params = array('name'=>'Initial_Join_Date');
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomField', $params, $cField);
    $dbAlias = $this->_columns[$cGrp['table_name']]['fields']["custom_{$cField['id']}"]['dbAlias'];
    $addWhereClauses = "({$this->_aliases['civicrm_hrjob']}.is_primary IS NULL AND {$dbAlias} IS NOT NULL AND {$dbAlias} <= CURDATE())";
    if (!empty($this->_params['current_employee_value'])) {
      $whereClauses[] = "(({$this->_aliases['civicrm_hrjob']}.is_primary = 1 OR {$this->_aliases['civicrm_hrjob']}.is_primary IS NULL) AND ({$dbAlias} IS NOT NULL AND {$dbAlias} <= CURDATE()))";
      $this->_where = str_replace("AND ( hrjob_civireport.current_employee = 1 )", '', $this->_where);
    }

    $whereClauses[] = "{$this->_aliases['civicrm_contact']}.contact_type = 'Individual'";
    $where = implode(' AND ', $whereClauses);
    if ($this->_where == "WHERE ( 1 )" ) {
      $this->_where = $where;
    }
    else {
      $this->_where .= " AND {$where}";
    }
  }

  function statistics(&$rows) {
    $statistics = parent::statistics($rows);
    if (!empty($this->_statFields)) {
      if ((array_key_exists("monthly_cost_eq",$this->_params["fields"]) || array_key_exists("annual_cost_eq",$this->_params["fields"])) && array_key_exists("hrjob_pay_currency",$this->_params["fields"])) {
      	$this->_select .=", count({$this->_aliases["civicrm_hrjob_pay"]}.pay_annualized_est) as count";
      	$groupByCurrency = "GROUP BY {$this->_aliases["civicrm_hrjob_pay"]}.pay_currency";
      	$sql = "{$this->_select} {$this->_from} {$this->_where} {$groupByCurrency} {$this->_having} {$this->_orderBy} {$this->_limit}";
      	$dao = CRM_Core_DAO::executeQuery($sql);
      	while ($dao->fetch()) {
      	  foreach ($this->_statFields as $title => $alias) {
      	    if ($alias == "civicrm_hrjob_pay_annual_cost_eq_sum") {
              $totalAmount[] = CRM_Utils_Money::format($dao->$alias, $dao->civicrm_hrjob_pay_hrjob_pay_currency)."(".$dao->count.")";
              $statistics['counts'][$alias] = array(
                'title' => $title,
                'value' => implode(',  ', $totalAmount),
                'type' => CRM_Utils_Type::T_STRING,
              );
            }
      	  }
        }
      }
      else {
      	// after removing group-by we have
      	$sql = "{$this->_select} {$this->_from} {$this->_where} {$this->_having} {$this->_orderBy} {$this->_limit}";
      	$dao = CRM_Core_DAO::executeQuery($sql);
      	while ($dao->fetch()) {
      	  foreach ($this->_statFields as $title => $alias) {
      	    $statistics['counts'][$alias] = array(
              'title' => $title,
              'value' => $dao->$alias,
              'type' => CRM_Utils_Type::T_STRING,
            );
          }
      	}
      }
    }
    return $statistics;
  }

  function modifyColumnHeaders() {
    // make sure stats columns always appear on right.
    if (!empty($this->_statFields)) {
      $tempHeaders = array();
      foreach ($this->_statFields as $header) {
        $tempHeaders[$header] = $this->_columnHeaders[$header];
        unset($this->_columnHeaders[$header]);
      }
      $this->_columnHeaders = array_merge($this->_columnHeaders, $tempHeaders);
    }
  }

  function alterDisplay(&$rows) {
    $entryFound = FALSE;
    $gender = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id');
    $job_location = CRM_Core_OptionGroup::values('hrjob_location');
    $contract_type = CRM_Core_OptionGroup::values('hrjob_contract_type');
    $department = CRM_Core_OptionGroup::values('hrjob_department');
    $hours_type = CRM_Core_OptionGroup::values('hrjob_hours_type');
    $level_typel = CRM_Core_OptionGroup::values('hrjob_level_type');
    foreach ($rows as $rowNum => $row) {
      if (array_key_exists('civicrm_contact_gender_id', $row)) {
      	if (!empty($row['civicrm_contact_gender_id'])) {
          $rows[$rowNum]['civicrm_contact_gender_id'] = CRM_Utils_Array::value($row['civicrm_contact_gender_id'], $gender);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_hrjob_hrjob_is_primary', $row)) {
        if (isset($row['civicrm_hrjob_hrjob_is_primary'])) {
          $rows[$rowNum]['civicrm_hrjob_hrjob_is_primary'] = ($row['civicrm_hrjob_hrjob_is_primary'] == 1) ? ts('Yes') : ts('No');
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_hrjob_pay_hrjob_is_paid', $row)) {
        if (isset($row['civicrm_hrjob_pay_hrjob_is_paid'])) {
          $rows[$rowNum]['civicrm_hrjob_pay_hrjob_is_paid'] = ($row['civicrm_hrjob_pay_hrjob_is_paid'] == 1) ? ts('Paid') : ts('Unpaid');
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_hrjob_health_hrjob_health_provider_life_insurance', $row) &&
        array_key_exists('civicrm_hrjob_health_life_provider_id', $row) && array_key_exists('civicrm_hrjob_health_life_provider_display_name', $row)
      ) {
        $url =  CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_hrjob_health_life_provider_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_hrjob_health_hrjob_health_provider_life_insurance'] = $rows[$rowNum]['civicrm_hrjob_health_life_provider_display_name'];
        $rows[$rowNum]['civicrm_hrjob_health_hrjob_health_provider_life_insurance_link'] = $url;
        $entryFound = TRUE;
      }
      if (array_key_exists('civicrm_hrjob_health_hrjob_health_provider', $row) &&
        array_key_exists('civicrm_hrjob_health_provider_id', $row) && array_key_exists('civicrm_hrjob_health_provider_organization_name', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_hrjob_health_provider_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_hrjob_health_hrjob_health_provider'] = $rows[$rowNum]['civicrm_hrjob_health_provider_organization_name'];
        $rows[$rowNum]['civicrm_hrjob_health_hrjob_health_provider_link'] = $url;
        $entryFound = TRUE;
      }
      if (array_key_exists('civicrm_hrjob_hrjob_location', $row) && isset($rows[$rowNum]['civicrm_hrjob_hrjob_location'])) {
        $rows[$rowNum]['civicrm_hrjob_hrjob_location'] = $job_location[$rows[$rowNum]['civicrm_hrjob_hrjob_location']];
        $entryFound = TRUE;
      }
      if (array_key_exists('civicrm_hrjob_hrjob_contract_type', $row) && isset($rows[$rowNum]['civicrm_hrjob_hrjob_contract_type'])) {
        $rows[$rowNum]['civicrm_hrjob_hrjob_contract_type'] = $contract_type[$rows[$rowNum]['civicrm_hrjob_hrjob_contract_type']];
        $entryFound = TRUE;
      }
      if (array_key_exists('civicrm_hrjob_role_hrjob_role_department', $row) && isset($rows[$rowNum]['civicrm_hrjob_role_hrjob_role_department'])) {
        $rows[$rowNum]['civicrm_hrjob_role_hrjob_role_department'] = $department[$rows[$rowNum]['civicrm_hrjob_role_hrjob_role_department']];
        $entryFound = TRUE;
      }
      if (array_key_exists('civicrm_hrjob_hour_hrjob_hours_type', $row) && isset($rows[$rowNum]['civicrm_hrjob_hour_hrjob_hours_type'])) {
        $rows[$rowNum]['civicrm_hrjob_hour_hrjob_hours_type'] = $hours_type[$rows[$rowNum]['civicrm_hrjob_hour_hrjob_hours_type']];
        $entryFound = TRUE;
      }
      if (array_key_exists('civicrm_hrjob_role_hrjob_role_level_type', $row) && isset($rows[$rowNum]['civicrm_hrjob_role_hrjob_role_level_type'])) {
        $rows[$rowNum]['civicrm_hrjob_role_hrjob_role_level_type'] = $level_typel[$rows[$rowNum]['civicrm_hrjob_role_hrjob_role_level_type']];
        $entryFound = TRUE;
      }
      $entryFound =
        $this->alterDisplayAddressFields($row, $rows, $rowNum, 'civihr/detail', 'List all contact(s) for this ') ? TRUE : $entryFound;
    }
  }
}
