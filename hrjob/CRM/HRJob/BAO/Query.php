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
    }
    return self::$_hrjobFields;
  }

  function select(&$query) {
  }

  function where(&$query) {
    $grouping = NULL;
    foreach (array_keys($query->_params) as $id) {
      if (!CRM_Utils_Array::value(0, $query->_params[$id])) {
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
      case 'hrjob_pay_grade':
      case 'hrjob_period_type':
        if (is_array($value) && count($value) > 1) {
          $op   = 'IN';
          $options = "('" . implode("','", $value) . "')";

          $query->_qill[$grouping][]  = ts('%1 %2', array(1 => $fields[$name]['title'], 2 => $op)) . ' ' . implode(' ' . ts('or') . ' ', $value);
          $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause($fields[$name]['where'], $op, $options);
          list($tableName, $fieldName) = explode('.', $fields[$name]['where'], 2);
          $query->_tables[$tableName]  = $query->_whereTables[$tableName] = 1;
        }
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
        $from = " $side JOIN civicrm_hrjob ON civicrm_hrjob.contact_id = contact_a.id ";
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
    }
    return $from;
  }

  function setTableDependency(&$tables) {
    if (
      CRM_Utils_Array::value('civicrm_hrjob_hour', $tables) ||
      CRM_Utils_Array::value('civicrm_hrjob_health', $tables) ||
      CRM_Utils_Array::value('civicrm_hrjob_pension', $tables) ||
      CRM_Utils_Array::value('civicrm_hrjob_pay', $tables) 
    ) {
      $tables = array_merge(array('civicrm_hrjob' => 1), $tables);
    }
  }

  public function registerAdvancedSearchPane(&$panes) {
    $panes['Job'] = 'hrjob';
    $panes['Job: Hour']  = 'hrjob_hour';
    $panes['Job: Health']  = 'hrjob_health';
    $panes['Job: Pension'] = 'hrjob_pension';
    $panes['Job: Pay']  = 'hrjob_pay';
  }

  public function buildAdvancedSearchPaneForm(&$form, $type) {
    if ($type  == 'hrjob') {
      $form->add('hidden', 'hidden_hrjob', 1);
      $form->addElement('text', 'hrjob_position', ts('Position'), CRM_Core_DAO::getAttribute('CRM_HRJob_DAO_HRJob', 'position'));
      $form->addElement('text', 'hrjob_title', ts('Title'), CRM_Core_DAO::getAttribute('CRM_HRJob_DAO_HRJob', 'title'));
      $form->addElement('text', 'hrjob_seniority', ts('Seniority'), CRM_Core_DAO::getAttribute('CRM_HRJob_DAO_HRJob', 'seniority'));
      $form->addElement('text', 'hrjob_contract_type', ts('Contract Type'), CRM_Core_DAO::getAttribute('CRM_HRJob_DAO_HRJob', 'contract_type'));
      $form->add('select', 'hrjob_period_type', ts('Period Type'), 
        array('Temporary' => ts('Temporary'), 'Permanent' => ts('Permanent')), FALSE,
        array('id' => 'hrjob_period_type', 'multiple' => 'multiple', 'title' => ts('- select -'))
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
      $form->addElement('text', 'hrjob_hours_amount', ts('Hours Amount'), CRM_Core_DAO::getAttribute('CRM_HRJob_DAO_HRJobHour', 'hours_amount'));
      $form->add('select', 'hrjob_hours_unit', ts('Hours Unit'), 
        array('Day' => ts('Day'), 'Week' => ts('Week'), 'Month' => ts('Month'), 'Year' => ts('Year')), FALSE,
        array('id' => 'hrjob_hours_type', 'multiple' => 'multiple', 'title' => ts('- select -'))
      );
      $form->addElement('text', 'hrjob_hours_fte', ts('Hours FTE'), CRM_Core_DAO::getAttribute('CRM_HRJob_DAO_HRJobHour', 'hours_fte'));
    }
    if ($type  == 'hrjob_pension') {
      $form->add('hidden', 'hidden_hrjob_pension', 1);
      $form->addYesNo( 'hrjob_is_enrolled', ts('Is enrolled?'));
    }
    if ($type  == 'hrjob_pay') {
      $form->add('hidden', 'hidden_hrjob_pay', 1);
      $form->add('select', 'hrjob_pay_grade', ts('Pay Grade'), 
        CRM_Core_PseudoConstant::get('CRM_HRJob_DAO_HRJobPay', 'pay_grade'), FALSE,
        array('id' => 'hrjob_pay_grade', 'multiple' => 'multiple', 'title' => ts('- select -'))
      );
    }
  }

  public function setAdvancedSearchPaneTemplatePath(&$paneTemplatePathArray, $type) {
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
}
