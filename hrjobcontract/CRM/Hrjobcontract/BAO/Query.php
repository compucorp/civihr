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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_Hrjobcontract_BAO_Query extends CRM_Contact_BAO_Query_Interface {

  /**
   * static field for all the export/import hrjob fields
   *
   * @var array
   * @static
   */
  static $_hrjobFields = array();

  /**
   * Function get the import/export fields for hrjobcontract
   *
   * @return array self::$_hrjobFields  associative array of hrjobcontract fields
   * @static
   */
  function &getFields() {
    if (!self::$_hrjobFields) {
      self::$_hrjobFields = CRM_Hrjobcontract_BAO_HRJobDetails::export();
      self::$_hrjobFields['hrjobcontract_role_manager_contact'] =
        array(
          'name'  => 'manager_contact',
          'title' => 'Job Manager',
          'type'  => CRM_Utils_Type::T_STRING,
          'where' => 'civicrm_hrjobcontract_role_manager.display_name'
        );
      self::$_hrjobFields = array_merge(self::$_hrjobFields, CRM_Hrjobcontract_BAO_HRJobHealth::export());
      self::$_hrjobFields = array_merge(self::$_hrjobFields, CRM_Hrjobcontract_BAO_HRJobHour::export());

      // special case to check for existence of health record entry
      /*self::$_hrjobFields['hrjobcontract_health_is_healthcare'] =
        array(
          'name'  => 'is_healthcare',
          'title' => 'Is health care',
          'type'  => CRM_Utils_Type::T_BOOLEAN,
          'where' => 'civicrm_hrjobcontract_health.id'
        );*/
      
      self::$_hrjobFields = array_merge(self::$_hrjobFields, CRM_Hrjobcontract_BAO_HRJobLeave::export());
      self::$_hrjobFields = array_merge(self::$_hrjobFields, CRM_Hrjobcontract_BAO_HRJobPay::export());
      self::$_hrjobFields = array_merge(self::$_hrjobFields, CRM_Hrjobcontract_BAO_HRJobPension::export());
      self::$_hrjobFields = array_merge(self::$_hrjobFields, CRM_Hrjobcontract_BAO_HRJobRole::export());
    }
    return self::$_hrjobFields;
  }

  function select(&$query) {
    if (CRM_Contact_BAO_Query::componentPresent($query->_returnProperties, 'hrjobcontract_')) {
      $fields = $this->getFields();
      foreach ($fields as $fldName => $params) {
        if (!empty($query->_returnProperties[$fldName])) {
          $query->_select[$fldName]  = "{$params['where']} as $fldName";
          if ($fldName == 'hrjobcontract_role_manager_contact') {
            $query->_select[$fldName]  = "GROUP_CONCAT(DISTINCT(civicrm_hrjobcontract_role_manager.sort_name) SEPARATOR ' | ') as $fldName";
          }
          if ($fldName == 'hrjobcontract_role_department') {
            $query->_select[$fldName]  = "GROUP_CONCAT(DISTINCT(civicrm_hrjobcontract_role.department) SEPARATOR ' | ') as $fldName";
          }
          $query->_element[$fldName] = 1;
          list($tableName, $dnc) = explode('.', $params['where'], 2);
          $query->_tables[$tableName]  = $query->_whereTables[$tableName] = 1;
        }
      }
    }
  }

  function where(&$query) {
    $grouping = NULL;
    foreach (array_keys($query->_params) as $id) {
      if (empty($query->_params[$id][0])) {
        continue;
      }
      if (substr($query->_params[$id][0], 0, 13) == 'hrjobcontract') {
        if ($query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS) {
          $query->_useDistinct = TRUE;
        }
        $this->whereClauseSingle($query->_params[$id], $query);
      }
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
      case 'hrjobcontract_details_is_primary':
        $query->_qill[$grouping][]  = $value ? ts('Is Primary') : ts('Is not Primary');
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("hrjobcontract.is_primary", $op, $value, "Boolean");
        $query->_tables['civicrm_hrjobcontract'] = $query->_whereTables['civicrm_hrjobcontract'] = 1;
        return;
      case 'hrjobcontract_role_role_level_type':
      case 'hrjobcontract_details_contract_type':
      case 'hrjobcontract_pay_is_paid':
      case 'hrjobcontract_hour_hours_type':
      case 'hrjobcontract_hour_hours_unit':
        $display = $options = $value;
        if (is_array($value) && count($value) >= 1) {
          $op      = 'IN';
          $options = "('" . implode("','", $value) . "')";
          $display = implode(' ' . ts('or') . ' ', $value);
        }
        $query->_qill[$grouping][]  = ts('%1 %2', array(1 => $fields[$name]['title'], 2 => $op)) . ' ' . $display;
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause($fields[$name]['where'], $op, $options);
        list($tableName, $fieldName) = explode('.', $fields[$name]['where'], 2);
        $query->_tables[$tableName]  = $query->_whereTables[$tableName] = 1;
        return;

      /*case 'hrjobcontract_is_healthcare':
        $op = "IS NOT NULL";
        $query->_qill[$grouping][]  = ts('Healthcare is provided');
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("civicrm_hrjobcontract_health.id", $op);
        $query->_tables['civicrm_hrjobcontract_health'] = $query->_whereTables['civicrm_hrjobcontract_health'] = 1;
        return;*/

      case 'hrjobcontract_pension_is_enrolled':
        $display = $options = $value;
        if (is_array($value) && count($value) >= 1) {
          $op      = 'IN';
          $options = "('" . implode("','", $value) . "')";
          $display = implode(' ' . ts('or') . ' ', $value);
        }
        $query->_qill[$grouping][]  = ts('%1 %2', array(1 => $fields[$name]['title'], 2 => $op)) . ' ' . $display;
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("civicrm_hrjobcontract_pension.is_enrolled", $op, $options);
        $query->_tables['civicrm_hrjobcontract_pension'] = $query->_whereTables['civicrm_hrjobcontract_pension'] = 1;
        return;

      case 'hrjobcontract_details_period_start_date_low':
      case 'hrjobcontract_details_period_start_date_high':
        $query->dateQueryBuilder($values,
          'civicrm_hrjobcontract_details', 'hrjobcontract_details_period_start_date', 'period_start_date', 'Period Start Date'
        );
        return;

      case 'hrjobcontract_details_period_end_date_low':
      case 'hrjobcontract_details_period_end_date_high':
        $query->dateQueryBuilder($values,
          'civicrm_hrjobcontract_details', 'hrjobcontract_details_period_end_date', 'period_end_date', 'Period End Date'
        );
        return;

      case 'hrjobcontract_hour_hours_amount':
      case 'hrjobcontract_hour_hours_amount_low':
      case 'hrjobcontract_hour_hours_amount_high':
        // process min/max amount
        $query->numberRangeBuilder($values,
          'civicrm_hrjobcontract_hour', 'hrjobcontract_hour_hours_amount',
          'hours_amount', 'Hours Amount',
          NULL
        );
        return;

      case 'hrjobcontract_hour_hours_fte':
      case 'hrjobcontract_hour_hours_fte_low':
      case 'hrjobcontract_hour_hours_fte_high':
        // process min/max fte
        $query->numberRangeBuilder($values,
          'civicrm_hrjobcontract_hour', 'hrjobcontract_hour_hours_fte',
          'hours_fte', 'Hours FTE',
          NULL
        );
        return;
        
      case 'hrjobcontract_health_health_provider':
        $query->_qill[$grouping][]  = 'Healthcare Provider contains "' . $value . '"';
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("contact_health1.sort_name", 'LIKE', '%' . $value . '%');
        $query->_tables['civicrm_hrjobcontract'] = $query->_whereTables['civicrm_hrjobcontract_health'] = 1;
        return;
      case 'hrjobcontract_health_health_provider_life_insurance':
        $query->_qill[$grouping][]  = 'Life insurance Provider contains "' . $value . '"';
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("contact_health2.sort_name", 'LIKE', '%' . $value . '%');
        $query->_tables['civicrm_hrjobcontract'] = $query->_whereTables['civicrm_hrjobcontract_health'] = 1;
        return;
        
      case 'hrjobcontract_hour_location_standard_hours':
        $hoursLocation = new CRM_Hrjobcontract_BAO_HoursLocation();
        $hoursLocation->find();
        $hoursLocationOptions = array();
        while ($hoursLocation->fetch()) {
            $hoursLocationOptions[$hoursLocation->id] = $hoursLocation->location;
        }
        $displayValue = array();
        foreach ($value as $optionValue) {
            $displayValue[] = $hoursLocationOptions[$optionValue];
        }
        $query->_qill[$grouping][]  = 'Location/Standard hours IN ' . implode(', ', $value) . ''; // $displayValue
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("civicrm_hrjobcontract_hour.location_standard_hours", 'IN', '(' . implode(',', $value) . ')');
        $query->_tables['civicrm_hrjobcontract_hour'] = $query->_whereTables['civicrm_hrjobcontract_hour'] = 1;
        return;
        
      case 'hrjobcontract_leave_leave_type':
        $query->_qill[$grouping][]  = 'Leave Type IN ' . implode(', ', $value) . '';
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("civicrm_hrjobcontract_leave.leave_type", 'IN', '(' . implode(',', $value) . ')');
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("civicrm_hrjobcontract_leave.leave_amount", '>', 0);
        $query->_tables['civicrm_hrjobcontract_leave'] = $query->_whereTables['civicrm_hrjobcontract_leave'] = 1;
        return;
        
      case 'hrjobcontract_pay_pay_amount':
      case 'hrjobcontract_pay_pay_amount_low':
      case 'hrjobcontract_pay_pay_amount_high':
        $query->numberRangeBuilder($values,
          'civicrm_hrjobcontract_pay', 'hrjobcontract_pay_pay_amount',
          'pay_amount', 'Pay Amount',
          NULL
        );
        return;
        
      case 'hrjobcontract_pay_pay_annualized_est':
      case 'hrjobcontract_pay_pay_annualized_est_low':
      case 'hrjobcontract_pay_pay_annualized_est_high':
        $query->numberRangeBuilder($values,
          'civicrm_hrjobcontract_pay', 'hrjobcontract_pay_pay_annualized_est',
          'pay_annualized_est', 'Estimated Annual Pay',
          NULL
        );
        return;
        
      case 'hrjobcontract_pay_pay_per_cycle_gross':
      case 'hrjobcontract_pay_pay_per_cycle_gross_low':
      case 'hrjobcontract_pay_pay_per_cycle_gross_high':
        $query->numberRangeBuilder($values,
          'civicrm_hrjobcontract_pay', 'hrjobcontract_pay_pay_per_cycle_gross',
          'pay_per_cycle_gross', 'Pay Per Cycle Gross',
          NULL
        );
        return;
        
      case 'hrjobcontract_pay_pay_per_cycle_net':
      case 'hrjobcontract_pay_pay_per_cycle_net_low':
      case 'hrjobcontract_pay_pay_per_cycle_net_high':
        $query->numberRangeBuilder($values,
          'civicrm_hrjobcontract_pay', 'hrjobcontract_pay_pay_per_cycle_net',
          'pay_per_cycle_net', 'Pay Per Cycle Net',
          NULL
        );
        return;
        
      case 'hrjobcontract_pension_ee_contrib_pct_low':
      case 'hrjobcontract_pension_ee_contrib_pct_high':
        $query->numberRangeBuilder($values,
          'civicrm_hrjobcontract_pension', 'hrjobcontract_pension_ee_contrib_pct',
          'ee_contrib_pct', 'Employee Contribution Percentage',
          NULL
        );
        return;
        
      case 'hrjobcontract_pension_er_contrib_pct_low':
      case 'hrjobcontract_pension_er_contrib_pct_high':
        $query->numberRangeBuilder($values,
          'civicrm_hrjobcontract_pension', 'hrjobcontract_pension_er_contrib_pct',
          'er_contrib_pct', 'Employer Contribution Percentage',
          NULL
        );
        return;
        
      case 'hrjobcontract_pension_ee_contrib_abs_low':
      case 'hrjobcontract_pension_ee_contrib_abs_high':
        $query->numberRangeBuilder($values,
          'civicrm_hrjobcontract_pension', 'hrjobcontract_pension_ee_contrib_abs',
          'ee_contrib_abs', 'Employee Contribution Absolute Amount',
          NULL
        );
        return;

      default:
        if (!isset($fields[$name])) {
          CRM_Core_Session::setStatus(ts(
              'We did not recognize the search field: %1.',
              array(1 => $name)
            )
          );
          return;
        }
        $whereTable = $fields[$name];
        $value      = trim($value);
        $dataType   = "String";
        
        if (in_array($name, array(
            'hrjobcontract_details_position',
            'hrjobcontract_details_title',
            'hrjobcontract_details_funding_notes',
            'hrjobcontract_health_description',
            'hrjobcontract_health_dependents',
            'hrjobcontract_health_description_life_insurance',
            'hrjobcontract_health_dependents_life_insurance',
            'hrjobcontract_pension_ee_evidence_note',
          )) &&
          strpos($value, '%') === FALSE) {
          $op = 'LIKE';
          $value = "%" . trim($value, '%') . "%";
          $quoteValue = "\"$value\"";
        }
        $wc = ($op != 'LIKE') ? "LOWER($whereTable[where])" : "$whereTable[where]";
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause($wc, $op, $value, $dataType);
        $query->_qill [$grouping][] = "{$whereTable['title']} {$op} {$quoteValue}";
        list($tableName, $fieldName) = explode('.', $whereTable['where'], 2);
        $query->_tables[$tableName] = $query->_whereTables[$tableName] = 1;
    }
  }

  function from($name, $mode, $side) {
    $from = '';
    
    switch ($name) {
      case 'civicrm_contact':
        $from .= " $side JOIN civicrm_hrjobcontract hrjobcontract ON hrjobcontract.contact_id = contact_a.id
            $side JOIN civicrm_hrjobcontract_revision rev ON rev.jobcontract_id = hrjobcontract.id "
              . " AND rev.id = (SELECT id FROM civicrm_hrjobcontract_revision WHERE jobcontract_id = hrjobcontract.id "
              . " AND effective_date <= '" . date('Y-m-d') . "' ORDER BY id DESC LIMIT 1) ";
      break;
      case 'civicrm_hrjobcontract':
        $from .= " /*civicrm_hrjobcontract*/
        ";
        break;
      case 'civicrm_hrjobcontract_role_manager':
        $from .= "
         $side JOIN civicrm_hrjobcontract_role civicrm_hrjobcontract_role_manager_contact ON civicrm_hrjobcontract_role_manager_contact.jobcontract_revision_id = rev.role_revision_id
         $side JOIN civicrm_contact civicrm_hrjobcontract_role_manager ON civicrm_hrjobcontract_role_manager_contact.manager_contact_id = civicrm_hrjobcontract_role_manager.id
        ";
        break;
      case 'civicrm_hrjobcontract_details':
        $from .= " $side JOIN civicrm_hrjobcontract_details ON rev.details_revision_id = civicrm_hrjobcontract_details.jobcontract_revision_id ";
        break;
      case 'civicrm_hrjobcontract_hour':
        $from .= " $side JOIN civicrm_hrjobcontract_hour ON rev.hour_revision_id = civicrm_hrjobcontract_hour.jobcontract_revision_id ";
        break;
      case 'civicrm_hrjobcontract_health':
        $from .= " $side JOIN civicrm_hrjobcontract_health ON rev.health_revision_id = civicrm_hrjobcontract_health.jobcontract_revision_id ";
        $from .= " $side JOIN civicrm_contact contact_health1 ON civicrm_hrjobcontract_health.provider = contact_health1.id ";
        $from .= " $side JOIN civicrm_contact contact_health2 ON civicrm_hrjobcontract_health.provider_life_insurance = contact_health2.id ";
        break;
      case 'civicrm_hrjobcontract_leave':
        $from .= " $side JOIN civicrm_hrjobcontract_leave ON rev.leave_revision_id = civicrm_hrjobcontract_leave.jobcontract_revision_id ";
        break;
      case 'civicrm_hrjobcontract_pension':
        $from .= " $side JOIN civicrm_hrjobcontract_pension ON rev.pension_revision_id = civicrm_hrjobcontract_pension.jobcontract_revision_id ";
        break;
      case 'civicrm_hrjobcontract_pay':
        $from .= " $side JOIN civicrm_hrjobcontract_pay ON rev.pay_revision_id = civicrm_hrjobcontract_pay.jobcontract_revision_id ";
        break;
      case 'civicrm_hrjobcontract_role':
        $from .= " $side JOIN civicrm_hrjobcontract_role ON rev.role_revision_id = civicrm_hrjobcontract_role.jobcontract_revision_id ";
        break;
    }
    
    return $from;
  }

  function setTableDependency(&$tables) {
    if (!empty($tables['civicrm_hrjobcontract_hour']) || !empty($tables['civicrm_hrjobcontract_health']) || !empty($tables['civicrm_hrjobcontract_leave']) || !empty($tables['civicrm_hrjobcontract_pension'])|| !empty($tables['civicrm_hrjobcontract_pay'])) {
      $tables = array_merge(array('civicrm_hrjobcontract' => 1), $tables);
    }
  }

  public function registerAdvancedSearchPane(&$panes) {
    //if (!CRM_Core_Permission::check('access HRJobs')) { echo 'not accessible'; return; }
    $panes['Job Contract'] = 'hrjobcontract';
    $panes['Job Contract: Health']  = 'hrjobcontract_health';
    $panes['Job Contract: Hour']  = 'hrjobcontract_hour';
    $panes['Job Contract: Leave']  = 'hrjobcontract_leave';
    $panes['Job Contract: Pay']  = 'hrjobcontract_pay';
    $panes['Job Contract: Pension'] = 'hrjobcontract_pension';
  }

  public function getPanesMapper(&$panes) {
    //if (!CRM_Core_Permission::check('access HRJobs')) { echo 'not accessible'; return; }
    $panes['Job Contract']          = 'civicrm_hrjobcontract';
    $panes['Job Contract: Health']  = 'civicrm_hrjobcontract_health';
    $panes['Job Contract: Hour']    = 'civicrm_hrjobcontract_hour';
    $panes['Job Contract: Leave']   = 'civicrm_hrjobcontract_leave';
    $panes['Job Contract: Pay']     = 'civicrm_hrjobcontract_pay';
    $panes['Job Contract: Pension'] = 'civicrm_hrjobcontract_pension';
  }

  public function buildAdvancedSearchPaneForm(&$form, $type) {
    //if (!CRM_Core_Permission::check('access HRJobs')) { echo 'not accessible'; return; }
    if ($type == 'hrjobcontract') {
      $form->add('hidden', 'hidden_hrjobcontract', 1);
      $form->addElement('text', 'hrjobcontract_details_position', ts('Position'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobDetails', 'position'));
      $form->addElement('text', 'hrjobcontract_details_title', ts('Title'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobDetails', 'title'));
      $form->addElement('text', 'hrjobcontract_details_funding_notes', ts('Funding Notes'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobDetails', 'funding_notes'));
      
      $form->addElement('text', 'hrjobcontract_details_notice_amount', ts('Notice Period from Employer (Amount)'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobDetails', 'notice_amount'));
      $form->add('select', 'hrjobcontract_details_notice_unit', ts('Notice Period from Employer (Amount)'), CRM_Hrjobcontract_SelectValues::commonUnit(), FALSE,
        array('id' => 'hrjobcontract_details_notice_unit', 'multiple' => true)
      );
      $form->addElement('text', 'hrjobcontract_details_notice_amount_employee', ts('Notice Period from Employee (Amount)'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobDetails', 'notice_amount_employee'));
      $form->add('select', 'hrjobcontract_details_notice_unit_employee', ts('Notice Period from Employer (Amount)'), CRM_Hrjobcontract_SelectValues::commonUnit(), FALSE,
        array('id' => 'hrjobcontract_details_notice_unit_employee', 'multiple' => true)
      );
      
      $hrjcLocation = CRM_Core_PseudoConstant::get('CRM_Hrjobcontract_DAO_HRJobDetails', 'hrjobcontract_details_location');
      $form->add('select', 'hrjobcontract_details_location', ts('Normal Place of Work'), $hrjcLocation, FALSE,
        array('id' => 'hrjobcontract_details_location', 'multiple' => true)
      );
      
      $form->add('select', 'hrjobcontract_role_role_level_type', ts('Level'),
        CRM_Core_PseudoConstant::get('CRM_Hrjobcontract_DAO_HRJobRole', 'hrjobcontract_role_role_level_type'), FALSE,
        array('id' => 'hrjobcontract_role_role_level_type', 'multiple' => true)
      );
      $form->add('select', 'hrjobcontract_details_contract_type', ts('Contract Type'),
        CRM_Core_PseudoConstant::get('CRM_Hrjobcontract_DAO_HRJobDetails', 'hrjobcontract_details_contract_type'), FALSE,
        array('id' => 'hrjobcontract_details_contract_type', 'multiple' => true)
      );
      CRM_Core_Form_Date::buildDateRange($form, 'hrjobcontract_details_period_start_date', 1, '_low', '_high', ts('From:'), FALSE, FALSE);
      CRM_Core_Form_Date::buildDateRange($form, 'hrjobcontract_details_period_end_date', 1, '_low', '_high', ts('From:'), FALSE, FALSE);
      //$form->addYesNo( 'hrjobcontract_details_is_primary', ts('Is Primary?'));
      $form->add('select', 'hrjobcontract_details_is_primary', ts('Is Primary'), array('' => '- select -', 0 => 'No', 1 => 'Yes'), FALSE,
        array('id' => 'hrjobcontract_details_is_primary', 'multiple' => false)
      );
    }
    
    if ($type == 'hrjobcontract_health') {
      $form->add('hidden', 'hidden_hrjobcontract_health', 1);
      //$form->add('checkbox', 'hrjobcontract_health_is_healthcare', ts('Is healthcare provided?'));
      
      $form->addElement('text', 'hrjobcontract_health_health_provider', ts('Healthcare Provider (Complete OR Partial Name)'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobHealth', 'provider'));
      $form->add('select', 'hrjobcontract_health_health_plan_type', ts('Healthcare Plan Type'), CRM_Hrjobcontract_SelectValues::planType(), FALSE,
        array('id' => 'hrjobcontract_health_health_plan_type', 'multiple' => true)
      );
      $form->addElement('text', 'hrjobcontract_health_description', ts('Description Health Insurance'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobHealth', 'description'));
      $form->addElement('text', 'hrjobcontract_health_dependents', ts('Healthcare Dependents'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobHealth', 'dependents'));

      $form->addElement('text', 'hrjobcontract_health_health_provider_life_insurance', ts('Life insurance Provider (Complete OR Partial Name)'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobHealth', 'provider_life_insurance'));
      $form->add('select', 'hrjobcontract_health_life_insurance_plan_type', ts('Life insurance Plan Type'), CRM_Hrjobcontract_SelectValues::planTypeLifeInsurance(), FALSE,
        array('id' => 'hrjobcontract_health_life_insurance_plan_type', 'multiple' => true)
      );
      $form->addElement('text', 'hrjobcontract_health_description_life_insurance', ts('Description Life Insurance'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobHealth', 'description_life_insurance'));
      $form->addElement('text', 'hrjobcontract_health_dependents_life_insurance', ts('Life Insurance Dependents'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobHealth', 'dependents_life_insurance'));
    }
    
    if ($type == 'hrjobcontract_hour') {
      $form->add('hidden', 'hidden_hrjobcontract_hour', 1);
      
      $hoursLocation = new CRM_Hrjobcontract_BAO_HoursLocation();
      $hoursLocation->find();
      $hoursLocationOptions = array();
      while ($hoursLocation->fetch()) {
          $hoursLocationOptions[$hoursLocation->id] = $hoursLocation->location;
      }
      $form->add('select', 'hrjobcontract_hour_location_standard_hours', ts('Location/Standard hours'), $hoursLocationOptions, FALSE,
        array('id' => 'hrjobcontract_hour_location_standard_hours', 'multiple' => true)
      );
      
      $hoursType = CRM_Core_PseudoConstant::get('CRM_Hrjobcontract_DAO_HRJobHour', 'hrjobcontract_hour_hours_type');
      $form->add('select', 'hrjobcontract_hour_hours_type', ts('Hours Types'), $hoursType, FALSE,
        array('id' => 'hrjobcontract_hour_hours_type', 'multiple' => true)
      );
      
      $form->add('text', 'hrjobcontract_hour_hours_amount', ts('Actual Hours (Amount)'), array('size' => 8, 'maxlength' => 8));
      $form->add('select', 'hrjobcontract_hour_hours_unit', ts('Actual Hours (Unit)'), CRM_Hrjobcontract_SelectValues::commonUnit(), FALSE,
        array('id' => 'hrjobcontract_hour_hours_unit', 'multiple' => true)
      );
      
      $form->add('text', 'hrjobcontract_hour_hours_fte', ts('Full-Time Equivalence'), array('size' => 8, 'maxlength' => 8));
      $form->add('text', 'hrjobcontract_hour_hours_fte_num', ts('Full-Time Numerator Equivalence'), array('size' => 8, 'maxlength' => 8));
      $form->add('text', 'hrjobcontract_hour_fte_denom', ts('Full-Time Denominator Equivalence'), array('size' => 8, 'maxlength' => 8));

      $form->add('text', 'hrjobcontract_hour_hours_amount_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_hour_hours_amount_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_hour_hours_amount_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_hour_hours_amount_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');

      $form->add('text', 'hrjobcontract_hour_hours_fte_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_hour_hours_fte_low', ts('Please enter a valid decimal value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_hour_hours_fte_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_hour_hours_fte_high', ts('Please enter a valid decimal value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
    }
    
    if ($type == 'hrjobcontract_leave') {
      $form->add('hidden', 'hidden_hrjobcontract_leave', 1);
      
      $leaveTypeOptions = array();
      $absenceType = new CRM_HRAbsence_BAO_HRAbsenceType();
      $absenceType->find();
      while ($absenceType->fetch()) {
          $leaveTypeOptions[$absenceType->id] = $absenceType->title;
      }
      $form->add('select', 'hrjobcontract_leave_leave_type', ts('Leave Type'), $leaveTypeOptions, FALSE,
        array('id' => 'hrjobcontract_leave_leave_type', 'multiple' => true)
      );
    }
    
    if ($type  == 'hrjobcontract_pay') {
      $form->add('hidden', 'hidden_hrjobcontract_pay', 1);
      
      $payScaleOptions = array();
      $payScale = new CRM_Hrjobcontract_BAO_PayScale();
      $payScale->find();
      while ($payScale->fetch()) {
          $payScaleOptions[$payScale->id] = $payScale->pay_scale;
          if (!empty($payScale->pay_grade))
          {
              $payScaleOptions[$payScale->id] .= ' - ' .
                $payScale->pay_grade . ' - ' .
                $payScale->currency . ' ' .
                $payScale->amount . ' per ' .
                $payScale->periodicity;
          }
      }
      $form->add('select', 'hrjobcontract_pay_pay_scale', ts('Pay Scale'), $payScaleOptions, FALSE,
        array('id' => 'hrjobcontract_pay_pay_scale', 'multiple' => true)
      );
      
      $form->add('select', 'hrjobcontract_pay_is_paid', ts('Paid / Unpaid'),
        CRM_Core_PseudoConstant::get('CRM_Hrjobcontract_DAO_HRJobPay', 'is_paid'), FALSE,
        array('id' => 'hrjobcontract_pay_is_paid', 'multiple' => 'multiple', 'title' => ts('- select -'))
      );

      $form->add('text', 'hrjobcontract_pay_pay_amount', ts('Pay Amount'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_amount', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pay_pay_amount_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_amount_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pay_pay_amount_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_amount_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
      
      $form->add('select', 'hrjobcontract_pay_pay_unit', ts('Pay Unit'), CRM_Hrjobcontract_SelectValues::payUnit(), FALSE,
        array('id' => 'hrjobcontract_pay_pay_unit', 'multiple' => true)
      );
      $form->add('select', 'hrjobcontract_pay_pay_currency', ts('Pay Currency'), array_keys(CRM_Hrjobcontract_Page_JobContractTab::getCurrencyFormats()), FALSE,
        array('id' => 'hrjobcontract_pay_pay_currency', 'multiple' => true)
      );
      
      $form->add('text', 'hrjobcontract_pay_pay_annualized_est', ts('Estimated Annual Pay'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_annualized_est', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pay_pay_annualized_est_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_annualized_est_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pay_pay_annualized_est_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_annualized_est_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
      
      $form->add('select', 'hrjobcontract_pay_pay_is_auto_est', ts('Estimated Auto Pay'), array('' => '- select -', 0 => 'No', 1 => 'Yes'), FALSE,
        array('id' => 'hrjobcontract_pay_pay_is_auto_est', 'multiple' => false)
      );
      
      // TODO: Annual Benefits + Annual Deductions
      
      $payCycleOptions = array();
      $payCycles = array();
      CRM_Core_OptionGroup::getAssoc('hrjc_pay_cycle', $payCycles, true);
      foreach ($payCycles as $payCycle) {
          $payCycleOptions[$payCycle['value']] = $payCycle['label'];
      }
      $form->add('select', 'hrjobcontract_pay_pay_cycle', ts('Pay Cycle'), $payCycleOptions, FALSE,
        array('id' => 'hrjobcontract_pay_pay_cycle', 'multiple' => true)
      );

      $form->add('text', 'hrjobcontract_pay_pay_per_cycle_gross', ts('Pay Per Cycle Gross'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_per_cycle_gross', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pay_pay_per_cycle_gross_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_per_cycle_gross_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pay_pay_per_cycle_gross_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_per_cycle_gross_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
      
      $form->add('text', 'hrjobcontract_pay_pay_per_cycle_net', ts('Pay Per Cycle Net'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_per_cycle_net', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pay_pay_per_cycle_net_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_per_cycle_net_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pay_pay_per_cycle_net_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pay_pay_per_cycle_net_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
    }
    
    if ($type  == 'hrjobcontract_pension') {
      $form->add('hidden', 'hidden_hrjobcontract_pension', 1);
      
      $form->add('select', 'hrjobcontract_pension_is_enrolled', ts('Is Enrolled'), array(0 => 'No', 1 => 'Yes', 2 => 'Opted out'), FALSE,
        array('id' => 'hrjobcontract_pension_is_enrolled', 'multiple' => true)
      );
      
      $form->add('text', 'hrjobcontract_pension_ee_contrib_pct_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pension_ee_contrib_pct_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pension_ee_contrib_pct_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pension_ee_contrib_pct_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
      
      $form->add('text', 'hrjobcontract_pension_er_contrib_pct_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pension_er_contrib_pct_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pension_er_contrib_pct_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pension_er_contrib_pct_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
      
      $pensionTypes = array();
      $pensionTypeOptions = array();
      CRM_Core_OptionGroup::getAssoc('hrjc_pension_type', $pensionTypes, true);
      foreach ($pensionTypes as $pensionType) {
          $pensionTypeOptions[$pensionType['value']] = $pensionType['label'];
      }
      $form->add('select', 'hrjobcontract_pension_pension_type', ts('Pension Provider'), $pensionTypeOptions, FALSE,
        array('id' => 'hrjobcontract_pension_pension_type', 'multiple' => true)
      );
      
      $form->add('text', 'hrjobcontract_pension_ee_contrib_abs_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pension_ee_contrib_abs_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjobcontract_pension_ee_contrib_abs_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjobcontract_pension_ee_contrib_abs_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
      
      $form->addElement('text', 'hrjobcontract_pension_ee_evidence_note', ts('Pension Evidence Note'), CRM_Core_DAO::getAttribute('CRM_Hrjobcontract_DAO_HRJobPension', 'ee_evidence_note'));
    }
  }

  public function setAdvancedSearchPaneTemplatePath(&$paneTemplatePathArray, $type) {
    //if (!CRM_Core_Permission::check('access HRJobs')) { echo 'not accessible'; return; }
    if ($type  == 'hrjobcontract') {
      $paneTemplatePathArray['hrjobcontract'] = 'CRM/Hrjobcontract/Form/Search/Criteria/JobContract.tpl';
    }
    if ($type  == 'hrjobcontract_health') {
      $paneTemplatePathArray['hrjobcontract_health'] = 'CRM/Hrjobcontract/Form/Search/Criteria/Health.tpl';
    }
    if ($type  == 'hrjobcontract_hour') {
      $paneTemplatePathArray['hrjobcontract_hour'] = 'CRM/Hrjobcontract/Form/Search/Criteria/Hour.tpl';
    }
    if ($type  == 'hrjobcontract_leave') {
      $paneTemplatePathArray['hrjobcontract_leave'] = 'CRM/Hrjobcontract/Form/Search/Criteria/Leave.tpl';
    }
    if ($type  == 'hrjobcontract_pay') {
      $paneTemplatePathArray['hrjobcontract_pay'] = 'CRM/Hrjobcontract/Form/Search/Criteria/Pay.tpl';
    }
    if ($type  == 'hrjobcontract_pension') {
      $paneTemplatePathArray['hrjobcontract_pension'] = 'CRM/Hrjobcontract/Form/Search/Criteria/Pension.tpl';
    }
  }

  /**
   * Describe options for available for use in the search-builder.
   *
   * The search builder determines its options by examining the API metadata corresponding to each
   * search field. This approach assumes that each field has a unique-name (ie that the field's
   * unique-name in the API matches the unique-name in the search-builder).
   *
   * @param array $apiEntities list of entities whose options should be automatically scanned using API metadata
   * @param array $fieldOptions keys are field unique-names; values describe how to lookup the options
   *   For boolean options, use value "yesno". For pseudoconstants/FKs, use the name of an API entity
   *   from which the metadata of the field may be queried. (Yes - that is a mouthful.)
   * @void
   */
  public function alterSearchBuilderOptions(&$apiEntities, &$fieldOptions) {
    //if (!CRM_Core_Permission::check('access HRJobs')) { echo 'not accessible'; return; }
    $apiEntities = array_merge($apiEntities, array(
      //'HRJob',
      'HRJobContract',
      'HRJobDetails',
      'HRJobHealth',
      'HRJobHour',
      'HRJobLeave',
      'HRJobPay',
      'HRJobPension',
    ));
  }
}
