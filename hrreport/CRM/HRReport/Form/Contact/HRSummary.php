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

  public function __construct() {
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

      'civicrm_hrjobcontract' =>
      array(
        'dao' => 'CRM_Hrjobcontract_DAO_HRJobContract',
        'fields' =>
        array(
          'is_primary' => array(
                        'title' => ts('Job Is Primary?'),
                        'no_repeat' => TRUE,
              'dbAlias' => 'hrjobcontract_civireport.is_primary',
          ),
        ),
        'filters' =>
        array(
          'is_primary' => array(
            'title' => ts('Job Is Primary?'),
            'default' => 1,
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('' => ts('Any'), '0' => ts('No'), '1' => ts('Yes')),
          ),
          'current_employee' =>
          array(
            'default' => NULL,
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array(''=> ts('ANY'),'0' => ts('No'), '1' => ts('Yes')),
            'no_display' => TRUE,
          ),
        ),
        'grouping' => array('job-fields' => 'Job'),
      ),

    'civicrm_hrjobcontract_revision' =>
    array(
      'dao' => 'CRM_Hrjobcontract_DAO_HRJobContractRevision',
      'fields' => array(
        'jobcontract_revision_id' => array(
            'title' => ts('Revision ID'),
            'no_repeat' => TRUE,
            'name' => 'id',
            'no_display' => TRUE,
        ),
        'editor_uid' => array(
            'title' => ts('Editor UID'),
            'no_repeat' => TRUE,
            'name' => 'editor_uid',
            'no_display' => TRUE,
        ),
        'created_date' => array(
            'title' => ts('Created date'),
            'no_repeat' => TRUE,
            'name' => 'created_date',
            'no_display' => TRUE,
        ),
        'modified_date' => array(
            'title' => ts('Modified date'),
            'no_repeat' => TRUE,
            'name' => 'modified_date',
            'no_display' => TRUE,
        ),
        'effective_date' => array(
            'title' => ts('Effective date'),
            'no_repeat' => TRUE,
            'name' => 'effective_date',
            'no_display' => TRUE,
        ),
        'change_reason' => array(
            'title' => ts('Change reason'),
            'no_repeat' => TRUE,
            'name' => 'change_reason',
            'no_display' => TRUE,
        ),
        'status' => array(
            'title' => ts('Revision status'),
            'no_repeat' => TRUE,
            'name' => 'status',
            'no_display' => TRUE,
        ),
      ),
      'grouping' => 'job-fields',
      'order_bys' => array(
            'civicrm_hrjobcontract_revision_revision_id' => array(
                'title' => ts('Revision Id'),
                'dbAlias' => 'hrjobcontract_revision_civireport.id',
            ),
      ),
      'group_bys' => array(
            'civicrm_hrjobcontract_revision_revision_id' => array(
                'title' => ts('Revision Id'),
                'dbAlias' => 'hrjobcontract_revision_civireport.id',
            ),
      ),
    ),

      'civicrm_hrjobcontract_details' =>
      array(
        'dao' => 'CRM_Hrjobcontract_DAO_HRJobDetails',
        'fields' =>
        array(
          'hrjobcontract_details_title'         => array(),
          'hrjobcontract_details_contract_type' => array(),
          //'hrjobcontract_details_period_type'   => array(),
          'hrjobcontract_details_location'      => array(),
          'hrjobcontract_details_position'      => array(),
          'hrjobcontract_details_period_start_date' => array(),
          'hrjobcontract_details_period_end_date'   => array(),
          //'hrjob_is_primary' => array(),
        ),
        'filters' =>
        array(
          'hrjobcontract_details_title'         => array(),
          'hrjobcontract_details_contract_type' => array(),
          'hrjobcontract_details_location'      => array(),
          'hrjobcontract_details_position'      => array(),
          'hrjobcontract_details_period_start_date' => array(),
          'hrjobcontract_details_period_end_date'   => array(),
        ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjobcontract_health' =>
      array(
        'dao' => 'CRM_Hrjobcontract_DAO_HRJobHealth',
        'fields' =>
        array(
          'hrjobcontract_health_health_provider_life_insurance' => array(),
          'hrjobcontract_health_life_insurance_plan_type' => array(),
          'hrjobcontract_health_health_provider' => array(),
          'hrjobcontract_health_health_plan_type' => array(),
        ),
        'filters' =>
        array(
          'hrjobcontract_health_life_insurance_plan_type' => array(),
          'hrjobcontract_health_health_plan_type' => array(),
        ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjobcontract_health_provider' =>
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

      'civicrm_hrjobcontract_health_life_provider' =>
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

      'civicrm_hrjobcontract_hour' =>
      array(
        'dao' => 'CRM_Hrjobcontract_DAO_HRJobHour',
        'fields' =>
        array(
          'hrjobcontract_hour_hours_type'   => array(),
          'hrjobcontract_hour_hours_amount' => array(),
          'hrjobcontract_hour_hours_unit'   => array(),
          'hrjobcontract_hour_hours_fte'    => array(),
        ),
        'filters' =>
        array(
          'hrjobcontract_hour_hours_type'   => array(),
          'hrjobcontract_hour_hours_amount' => array(),
          'hrjobcontract_hour_hours_unit'   => array(),
          'hrjobcontract_hour_hours_fte'    => array(),
        ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjobcontract_pay' =>
      array(
        'dao' => 'CRM_Hrjobcontract_DAO_HRJobPay',
        'fields' =>
        array(
          'hrjobcontract_pay_is_paid'    => array(),
          'hrjobcontract_pay_pay_amount'   => array(),
          'hrjobcontract_pay_pay_unit'     => array(),
          'hrjobcontract_pay_pay_currency' => array(),
          'hrjobcontract_pay_pay_annualized_est' => array(),
        ),
        'filters' =>
        array(
          'hrjobcontract_pay_is_paid'  => array(),
          'hrjobcontract_pay_pay_amount' => array(),
          'hrjobcontract_pay_pay_unit'   => array(),
          'hrjobcontract_pay_pay_annualized_est' => array(),
        ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjobcontract_pension' =>
      array(
        'dao' => 'CRM_Hrjobcontract_DAO_HRJobPension',
        'fields' =>
        array(
          'hrjobcontract_pension_is_enrolled' => array(),
        ),
        'filters' =>
        array(
          'hrjobcontract_pension_is_enrolled' => array(),
          ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjobcontract_role' =>
      array(
        'dao' => 'CRM_Hrjobcontract_DAO_HRJobRole',
        'fields' =>
        array(
          'hrjobcontract_role_role_department' => array(),
          'hrjobcontract_role_role_level_type' => array(
            'name' => 'level_type',
            'title' => ts('Role Level Types'),
            'type' => CRM_Utils_Type::T_INT,
            'grouping' => 'job-fields',
          ),
          /*'manager' =>
          array(
            'name' => 'manager_sort_name',
            'title' => ts('Role Managers'),
            'dbAlias' => 'manager.sort_name'
          ),*/
          'hrjob_role_manager_contact_id' => array(
            'no_display' => TRUE,
            'name' => 'manager_contact_id',
            'title' => ts('Role Managers'),
            'type' => CRM_Utils_Type::T_INT,
            'grouping' => 'job-fields',
          )
        ),
        'filters' =>
        array(
          'hrjobcontract_role_role_department' => array(),
          'hrjobcontract_role_role_level_type' => array(),
        ),
        'grouping' => 'job-fields',
      ),

      'civicrm_hrjobcontract_leave' =>
      array(
        'dao' => 'CRM_Hrjobcontract_DAO_HRJobLeave',
        'fields' =>
        array(
          'hrjobcontract_leave_leave_type' =>
          array(
            'name' => 'id',
            'no_display' => TRUE,
            'type'       => CRM_Utils_Type::T_INT,
          ),
        ),
      ),
    ) + $this->addAddressFields(FALSE, TRUE);
    parent::__construct();
    $config = CRM_Core_Config::singleton();
    // stats fields
    $this->_columns['civicrm_hrjobcontract']['fields']['job_positions'] =
      array(
        'name' => 'contact_id',
        'title' => ts('Job Positions'),
        'statistics' => array('count' => ts('Job Positions'),),
        'grouping' => 'stats-fields',
      );
    $this->_columns['civicrm_hrjobcontract_hour']['fields']['full_time_eq'] =
      array(
        'name' => 'hours_fte',
        'title' => ts('Full Time Equivalents'),
        'type' => CRM_Utils_Type::T_FLOAT,
        'statistics' => array('sum' => ts('Full Time Equivalents'),),
        'dbAlias'  => '(hrjobcontract_hour_civireport.fte_num / hrjobcontract_hour_civireport.fte_denom)',
        'grouping' => 'stats-fields',
      );
    $this->_columns['civicrm_hrjobcontract_pay']['fields']['monthly_cost_eq'] = array(
      'name' => 'pay_annualized_est',
      'title' => ts('Monthly Cost Equivalents').' ('.$config->defaultCurrencySymbol.')',
      'type' => CRM_Utils_Type::T_INT,
      'dbAlias'  => '(SUM(hrjobcontract_pay_civireport.pay_annualized_est)/12)',
      'grouping' => 'stats-fields',
    );
    $this->_columns['civicrm_hrjobcontract_pay']['fields']['annual_cost_eq'] = array(
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

  public function from() {
    $this->_from = "
      FROM  civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
      LEFT JOIN civicrm_hrjobcontract {$this->_aliases['civicrm_hrjobcontract']}
             ON ({$this->_aliases['civicrm_hrjobcontract']}.contact_id = {$this->_aliases['civicrm_contact']}.id)
      LEFT JOIN civicrm_hrjobcontract_revision {$this->_aliases['civicrm_hrjobcontract_revision']}
             ON {$this->_aliases['civicrm_hrjobcontract']}.id = {$this->_aliases['civicrm_hrjobcontract_revision']}.jobcontract_id
      LEFT JOIN civicrm_hrjobcontract_details {$this->_aliases['civicrm_hrjobcontract_details']}
             ON {$this->_aliases['civicrm_hrjobcontract_revision']}.details_revision_id = {$this->_aliases['civicrm_hrjobcontract_details']}.jobcontract_revision_id
      LEFT JOIN civicrm_hrjobcontract_health {$this->_aliases['civicrm_hrjobcontract_health']}
             ON {$this->_aliases['civicrm_hrjobcontract_revision']}.health_revision_id = {$this->_aliases['civicrm_hrjobcontract_health']}.jobcontract_revision_id
      LEFT JOIN civicrm_hrjobcontract_hour {$this->_aliases['civicrm_hrjobcontract_hour']}
             ON {$this->_aliases['civicrm_hrjobcontract_revision']}.hour_revision_id = {$this->_aliases['civicrm_hrjobcontract_hour']}.jobcontract_revision_id
      LEFT JOIN civicrm_hrjobcontract_pay {$this->_aliases['civicrm_hrjobcontract_pay']}
             ON {$this->_aliases['civicrm_hrjobcontract_revision']}.pay_revision_id = {$this->_aliases['civicrm_hrjobcontract_pay']}.jobcontract_revision_id
      LEFT JOIN civicrm_hrjobcontract_pension {$this->_aliases['civicrm_hrjobcontract_pension']}
             ON {$this->_aliases['civicrm_hrjobcontract_revision']}.pension_revision_id = {$this->_aliases['civicrm_hrjobcontract_pension']}.jobcontract_revision_id
      LEFT JOIN civicrm_contact {$this->_aliases['civicrm_hrjobcontract_health_provider']}
             ON {$this->_aliases['civicrm_hrjobcontract_health_provider']}.id={$this->_aliases['civicrm_hrjobcontract_health']}.provider
      LEFT JOIN civicrm_contact {$this->_aliases['civicrm_hrjobcontract_health_life_provider']}
             ON {$this->_aliases['civicrm_hrjobcontract_health_life_provider']}.id={$this->_aliases['civicrm_hrjobcontract_health']}.provider_life_insurance
      LEFT JOIN civicrm_hrjobcontract_role {$this->_aliases['civicrm_hrjobcontract_role']}
               ON {$this->_aliases['civicrm_hrjobcontract_revision']}.role_revision_id = {$this->_aliases['civicrm_hrjobcontract_role']}.jobcontract_revision_id
      ";

    foreach ($this->_columns as $tableName => $table) {
      if (!empty($table['fields'])) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) || !empty($this->_params['fields'][$fieldName])) {
            if ($tableName == 'civicrm_hrjobcontract_leave') {
              $this->_from .= " LEFT JOIN civicrm_hrjobcontract_leave {$this->_aliases['civicrm_hrjobcontract_leave']}
               ON ({$this->_aliases['civicrm_hrjobcontract_revision']}.leave_revision_id = {$this->_aliases['civicrm_hrjobcontract_leave']}.jobcontract_revision_id)";
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

  public function customDataFrom($joinsForFiltersOnly = FALSE) {
    parent::customDataFrom();
    $params = array('name'=>'HRJobContract_Summary');
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomGroup', $params, $cGrp);
    if (!$this->isFieldSelected($this->_columns[$cGrp['table_name']])) {
      $mapper = CRM_Core_BAO_CustomQuery::$extendsMap;
      $extendsTable = $mapper[$cGrp['extends']];
      $baseJoin = CRM_Utils_Array::value($cGrp['extends'], $this->_customGroupExtendsJoin, "{$this->_aliases[$extendsTable]}.id");
      $this->_from .= " LEFT JOIN {$cGrp['table_name']} {$this->_aliases[$cGrp['table_name']]} ON {$this->_aliases[$cGrp['table_name']]}.entity_id = {$baseJoin}";
    }
  }

  public function where() {
    parent::where();
    $params = array('name'=>'HRJobContract_Summary');
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomGroup', $params, $cGrp);
    $params = array('name'=>'Final_Termination_Date', 'custom_group_id' => $cGrp['id']);
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomField', $params, $cField);
    $dbAlias = $this->_columns[$cGrp['table_name']]['fields']["custom_{$cField['id']}"]['dbAlias'];
    if (!$this->isFieldSelected($this->_columns[$cGrp['table_name']])) {
      $whereClauses[] = "({$dbAlias} >= CURDATE() OR {$dbAlias} IS NULL)";
    }

    $params = array('name'=>'Initial_Join_Date', 'custom_group_id' => $cGrp['id']);
    CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_CustomField', $params, $cField);
    $dbAlias = $this->_columns[$cGrp['table_name']]['fields']["custom_{$cField['id']}"]['dbAlias'];
    $addWhereClauses = "({$this->_aliases['civicrm_hrjobcontract']}.is_primary IS NULL AND {$dbAlias} IS NOT NULL AND {$dbAlias} <= CURDATE())";
    if (!empty($this->_params['current_employee_value'])) {
      $whereClauses[] = "(({$this->_aliases['civicrm_hrjobcontract']}.is_primary = 1 OR {$this->_aliases['civicrm_hrjobcontract']}.is_primary IS NULL) AND ({$this->_aliases['civicrm_hrjobcontract_details']}.period_start_date IS NOT NULL AND {$this->_aliases['civicrm_hrjobcontract_details']}.period_start_date <= CURDATE()))";
      $this->_where = str_replace("AND ( hrjobcontract_civireport.current_employee = 1 )", '', $this->_where);
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

  public function statistics(&$rows) {
    $statistics = parent::statistics($rows);
    if (!empty($this->_statFields)) {
      if ((array_key_exists("civicrm_hrjobcontract_pay_monthly_cost_eq",$this->_params["fields"]) || array_key_exists("civicrm_hrjobcontract_pay_annual_cost_eq_sum",$this->_params["fields"])) && array_key_exists("civicrm_hrjobcontract_pay_hrjobcontract_pay_pay_currency",$this->_params["fields"])) {
      	$this->_select .=", count({$this->_aliases["civicrm_hrjobcontract_pay"]}.pay_annualized_est) as count";
      	$groupByCurrency = "GROUP BY {$this->_aliases["civicrm_hrjobcontract_pay"]}.pay_currency";
      	$sql = "{$this->_select} {$this->_from} {$this->_where} {$groupByCurrency} {$this->_having} {$this->_orderBy} {$this->_limit}";
      	$dao = CRM_Core_DAO::executeQuery($sql);
      	while ($dao->fetch()) {
      	  foreach ($this->_statFields as $title => $alias) {
      	    if ($alias == "civicrm_hrjobcontract_pay_annual_cost_eq_sum") {
              $totalAmount[] = CRM_Utils_Money::format($dao->$alias, $dao->civicrm_hrjobcontract_pay_hrjobcontract_pay_currency)."(".$dao->count.")";
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

  public function modifyColumnHeaders() {
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

  public function alterDisplay(&$rows) {
    $entryFound = FALSE;
    $gender = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id');
    foreach ($rows as $rowNum => $row) {
      $entryFound =
        $this->alterDisplayAddressFields($row, $rows, $rowNum, 'civihr/detail', 'List all contact(s) for this ') ? TRUE : $entryFound;

      if (array_key_exists('civicrm_contact_gender_id', $row)) {
      	if (!empty($row['civicrm_contact_gender_id'])) {
          $rows[$rowNum]['civicrm_contact_gender_id'] = CRM_Utils_Array::value($row['civicrm_contact_gender_id'], $gender);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_hrjobcontract_is_primary', $row)) {
      	if (!empty($row['civicrm_hrjobcontract_is_primary'])) {
          $rows[$rowNum]['civicrm_hrjobcontract_is_primary'] = ($row['civicrm_hrjobcontract_is_primary'] == 1) ? 'Yes' : 'No';
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_hrjobcontract_health_hrjobcontract_health_health_provider_life_insurance', $row) &&
        array_key_exists('civicrm_hrjobcontract_health_life_provider_id', $row) && array_key_exists('civicrm_hrjobcontract_health_life_provider_display_name', $row)
      ) {
        $url =  CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_hrjobcontract_health_life_provider_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_hrjobcontract_health_hrjobcontract_health_health_provider_life_insurance'] = $rows[$rowNum]['civicrm_hrjobcontract_health_life_provider_display_name'];
        $rows[$rowNum]['civicrm_hrjobcontract_health_hrjobcontract_health_health_provider_life_insurance_link'] = $url;
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_hrjobcontract_health_hrjobcontract_health_health_provider', $row) &&
        array_key_exists('civicrm_hrjobcontract_health_provider_id', $row) && array_key_exists('civicrm_hrjobcontract_health_provider_organization_name', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_hrjobcontract_health_provider_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_hrjobcontract_health_hrjobcontract_health_health_provider'] = $rows[$rowNum]['civicrm_hrjobcontract_health_provider_organization_name'];
        $rows[$rowNum]['civicrm_hrjobcontract_health_hrjobcontract_health_health_provider_link'] = $url;
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

