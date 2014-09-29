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
class CRM_HRJob_BAO_Query extends CRM_Contact_BAO_Query_Interface {

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
      self::$_hrjobFields['hrjob_role_manager_contact'] =
        array(
          'name'  => 'manager_contact',
          'title' => 'Job Manager',
          'type'  => CRM_Utils_Type::T_STRING,
          'where' => 'civicrm_hrjob_role_manager.display_name'
        );
      self::$_hrjobFields = array_merge(self::$_hrjobFields, CRM_HRJob_BAO_HRJobHour::export());

      // special case to check for existence of health record entry
      self::$_hrjobFields['hrjob_is_healthcare'] =
        array(
          'name'  => 'is_healthcare',
          'title' => 'Is health care',
          'type'  => CRM_Utils_Type::T_BOOLEAN,
          'where' => 'civicrm_hrjob_health.id'
        );

      self::$_hrjobFields = array_merge(self::$_hrjobFields, CRM_HRJob_BAO_HRJobPension::export());
      self::$_hrjobFields = array_merge(self::$_hrjobFields, CRM_HRJob_BAO_HRJobPay::export());
      self::$_hrjobFields = array_merge(self::$_hrjobFields, CRM_HRJob_BAO_HRJobRole::export());
    }
    return self::$_hrjobFields;
  }

  function select(&$query) {
    if (CRM_Contact_BAO_Query::componentPresent($query->_returnProperties, 'hrjob_')) {
      $fields = $this->getFields();
      foreach ($fields as $fldName => $params) {
        if (!empty($query->_returnProperties[$fldName])) {
          $query->_select[$fldName]  = "{$params['where']} as $fldName";
          if ($fldName == 'hrjob_role_manager_contact') {
            $query->_select[$fldName]  = "GROUP_CONCAT(DISTINCT(civicrm_hrjob_role_manager.sort_name) SEPARATOR ' | ') as $fldName";
          }
          if ($fldName == 'hrjob_role_department') {
            $query->_select[$fldName]  = "GROUP_CONCAT(DISTINCT(civicrm_hrjob_role.department) SEPARATOR ' | ') as $fldName";
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
      if (substr($query->_params[$id][0], 0, 6) == 'hrjob_') {
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
      case 'hrjob_role_level_type':
      case 'hrjob_contract_type':
      case 'hrjob_is_paid':
      case 'hrjob_period_type':
      case 'hrjob_hours_type':
      case 'hrjob_hours_unit':
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

      case 'hrjob_is_healthcare':
        $op = "IS NOT NULL";
        $query->_qill[$grouping][]  = ts('Healthcare is provided');
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("civicrm_hrjob_health.id", $op);
        $query->_tables['civicrm_hrjob_health'] = $query->_whereTables['civicrm_hrjob_health'] = 1;
        return;

      case 'hrjob_is_enrolled':
        $query->_qill[$grouping][]  = $value ? ts('Is enrolled') : ts('Is not enrolled');
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause("civicrm_hrjob_pension.is_enrolled", $op, $value, "Boolean");
        $query->_tables['civicrm_hrjob_pension'] = $query->_whereTables['civicrm_hrjob_pension'] = 1;
        return;

      case 'hrjob_period_start_date_low':
      case 'hrjob_period_start_date_high':
        $query->dateQueryBuilder($values,
          'civicrm_hrjob', 'hrjob_period_start_date', 'period_start_date', 'Period Start Date'
        );
        return;

      case 'hrjob_period_end_date_low':
      case 'hrjob_period_end_date_high':
        $query->dateQueryBuilder($values,
          'civicrm_hrjob', 'hrjob_period_end_date', 'period_end_date', 'Period End Date'
        );
        return;

      case 'hrjob_hours_amount':
      case 'hrjob_hours_amount_low':
      case 'hrjob_hours_amount_high':
        // process min/max amount
        $query->numberRangeBuilder($values,
          'civicrm_hrjob_hour', 'hrjob_hours_amount',
          'hours_amount', 'Hours Amount',
          NULL
        );
        return;

      case 'hrjob_hours_fte':
      case 'hrjob_hours_fte_low':
      case 'hrjob_hours_fte_high':
        // process min/max fte
        $query->numberRangeBuilder($values,
          'civicrm_hrjob_hour', 'hrjob_hours_fte',
          'hours_fte', 'Hours FTE',
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

        if (in_array($name, array('hrjob_position', 'hrjob_title')) &&
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
    $from = NULL;
    switch ($name) {
      case 'civicrm_hrjob':
        $from = " $side JOIN civicrm_hrjob ON civicrm_hrjob.contact_id = contact_a.id AND civicrm_hrjob.is_primary = 1";
        break;
      case 'civicrm_hrjob_role_manager':
        $from = " $side JOIN civicrm_hrjob_role civicrm_hrjob_role_manager_contact ON civicrm_hrjob.id = civicrm_hrjob_role_manager_contact.job_id $side JOIN civicrm_contact civicrm_hrjob_role_manager ON civicrm_hrjob_role_manager_contact.manager_contact_id = civicrm_hrjob_role_manager.id";
        break;
      case 'civicrm_hrjob_hour':
        $from = " $side JOIN civicrm_hrjob_hour ON civicrm_hrjob.id = civicrm_hrjob_hour.job_id ";
        break;
      case 'civicrm_hrjob_health':
        $from = " $side JOIN civicrm_hrjob_health ON civicrm_hrjob.id = civicrm_hrjob_health.job_id ";
        break;
      case 'civicrm_hrjob_pension':
        $from = " $side JOIN civicrm_hrjob_pension ON civicrm_hrjob.id = civicrm_hrjob_pension.job_id ";
        break;
      case 'civicrm_hrjob_pay':
        $from = " $side JOIN civicrm_hrjob_pay ON civicrm_hrjob.id = civicrm_hrjob_pay.job_id ";
        break;
    case 'civicrm_hrjob_role':
      $from = " $side JOIN civicrm_hrjob_role ON civicrm_hrjob.id = civicrm_hrjob_role.job_id ";
      break;
    }
    return $from;
  }

  function setTableDependency(&$tables) {
    if (!empty($tables['civicrm_hrjob_hour']) || !empty($tables['civicrm_hrjob_health']) || !empty($tables['civicrm_hrjob_pension'])|| !empty($tables['civicrm_hrjob_pay'])) {
      $tables = array_merge(array('civicrm_hrjob' => 1), $tables);
    }
  }

  public function registerAdvancedSearchPane(&$panes) {
    if (!CRM_Core_Permission::check('access HRJobs')) return;
    $panes['Job'] = 'hrjob';
    $panes['Job: Hour']  = 'hrjob_hour';
    $panes['Job: Health']  = 'hrjob_health';
    $panes['Job: Pension'] = 'hrjob_pension';
    $panes['Job: Pay']  = 'hrjob_pay';
  }

  public function getPanesMapper(&$panes) {
    if (!CRM_Core_Permission::check('access HRJobs')) return;
    $panes['Job']          = 'civicrm_hrjob';
    $panes['Job: Hour']    = 'civicrm_hrjob_hour';
    $panes['Job: Health']  = 'civicrm_hrjob_health';
    $panes['Job: Pension'] = 'civicrm_hrjob_pension';
    $panes['Job: Pay']     = 'civicrm_hrjob_pay';
  }

  public function buildAdvancedSearchPaneForm(&$form, $type) {
    if (!CRM_Core_Permission::check('access HRJobs')) return;
    if ($type  == 'hrjob') {
      $form->add('hidden', 'hidden_hrjob', 1);
      $form->addElement('text', 'hrjob_position', ts('Position'), CRM_Core_DAO::getAttribute('CRM_HRJob_DAO_HRJob', 'position'));
      $form->addElement('text', 'hrjob_title', ts('Title'), CRM_Core_DAO::getAttribute('CRM_HRJob_DAO_HRJob', 'title'));
      $form->add('select', 'hrjob_role_level_type', ts('Level'),
        CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJobRole', 'level_type'), FALSE,
        array('id' => 'hrjob_level_type', 'multiple' => 'multiple', 'title' => ts('- select -'))
      );
      $form->add('select', 'hrjob_contract_type', ts('Contract Type'),
        CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJob', 'contract_type'), FALSE,
        array('id' => 'hrjob_contract_type', 'multiple' => 'multiple', 'title' => ts('- select -'))
      );
      CRM_Core_Form_Date::buildDateRange($form, 'hrjob_period_start_date', 1, '_low', '_high', ts('From:'), FALSE, FALSE);
      CRM_Core_Form_Date::buildDateRange($form, 'hrjob_period_end_date', 1, '_low', '_high', ts('From:'), FALSE, FALSE);
    }
    if ($type  == 'hrjob_health') {
      $form->add('hidden', 'hidden_hrjob_health', 1);
      $form->add('checkbox', 'hrjob_is_healthcare', ts('Is healthcare provided?'));
    }
    if ($type  == 'hrjob_hour') {
      $form->add('hidden', 'hidden_hrjob_hour', 1);
      $hoursType = CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJobHour', 'hours_type');
      $form->add('select', 'hrjob_hours_type', ts('Hours Types'), $hoursType, FALSE,
        array('id' => 'hrjob_hours_type', 'multiple' => 'multiple', 'title' => ts('- select -'))
      );

      $form->add('select', 'hrjob_hours_unit', ts('Hours Unit'),
        array('Day' => ts('Day'), 'Week' => ts('Week'), 'Month' => ts('Month'), 'Year' => ts('Year')), FALSE,
        array('id' => 'hrjob_hours_unit', 'multiple' => 'multiple', 'title' => ts('- select -'))
      );

      $form->add('text', 'hrjob_hours_amount_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjob_hours_amount_low', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjob_hours_amount_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjob_hours_amount_high', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');

      $form->add('text', 'hrjob_hours_fte_low', ts('From'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjob_hours_fte_low', ts('Please enter a valid decimal value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');
      $form->add('text', 'hrjob_hours_fte_high', ts('To'), array('size' => 8, 'maxlength' => 8));
      $form->addRule('hrjob_hours_fte_high', ts('Please enter a valid decimal value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
    }
    if ($type  == 'hrjob_pension') {
      $form->add('hidden', 'hidden_hrjob_pension', 1);
      $form->addYesNo( 'hrjob_is_enrolled', ts('Is enrolled?'));
    }
    if ($type  == 'hrjob_pay') {
      $form->add('hidden', 'hidden_hrjob_pay', 1);
      $form->add('select', 'hrjob_pay_grade', ts('Paid / Unpaid'),
        CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJobPay', 'is_paid'), FALSE,
        array('id' => 'hrjob_pay_grade', 'multiple' => 'multiple', 'title' => ts('- select -'))
      );
    }
  }

  public function setAdvancedSearchPaneTemplatePath(&$paneTemplatePathArray, $type) {
    if (!CRM_Core_Permission::check('access HRJobs')) return;
    if ($type  == 'hrjob') {
      $paneTemplatePathArray['hrjob'] = 'CRM/HRJob/Form/Search/Criteria/Job.tpl';
    }
    if ($type  == 'hrjob_hour') {
      $paneTemplatePathArray['hrjob_hour'] = 'CRM/HRJob/Form/Search/Criteria/Hour.tpl';
    }
    if ($type  == 'hrjob_health') {
      $paneTemplatePathArray['hrjob_health'] = 'CRM/HRJob/Form/Search/Criteria/Health.tpl';
    }
    if ($type  == 'hrjob_pension') {
      $paneTemplatePathArray['hrjob_pension'] = 'CRM/HRJob/Form/Search/Criteria/Pension.tpl';
    }
    if ($type  == 'hrjob_pay') {
      $paneTemplatePathArray['hrjob_pay'] = 'CRM/HRJob/Form/Search/Criteria/Pay.tpl';
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
    if (!CRM_Core_Permission::check('access HRJobs')) return;
    $apiEntities = array_merge($apiEntities, array(
      'HRJob',
      'HRJobHealth',
      'HRJobHour',
      'HRJobPay',
      'HRJobPension',
    ));
  }
}
