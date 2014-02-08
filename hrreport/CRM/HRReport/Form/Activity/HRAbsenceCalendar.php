<?php
// $Id$

/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.0                                                 |
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
class CRM_HRReport_Form_Activity_HRAbsenceCalendar extends CRM_Report_Form {
  protected $_selectAliasesTotal = array();

  protected $_customGroupExtends = array(
    'Activity'
  );

  protected $_nonDisplayFields = array();

  function __construct() {
    // There could be multiple contacts. We not clear on which contact id to display.
    // Lets hide it for now.
    $this->_exposeContactID = FALSE;
    $this->activityTypes = CRM_HRAbsence_BAO_HRAbsenceType::getActivityTypes();
    $this->activityStatus = CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus();
    asort($this->activityTypes);

    $this->_columns = array(
      'civicrm_contact' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
        array(
          'display_name' =>
          array(
            'name' => 'sort_name',
            'title' => ts('Individual'),
            'default' => TRUE,
            'required' => TRUE,
          ),
        ),
        'filters' =>
        array(
          'display_name' =>
          array(
            'name' => 'display_name',
            'title' => ts('Individual'),
            'operator' => 'like',
            'dbAlias' => 'cc.display_name',
            'type' => CRM_Report_Form::OP_STRING,
          ),
          'current_user' =>
          array(
            'name' => 'current_user',
            'title' => ts('Limit To Current User'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('0' => ts('No'), '1' => ts('Yes')),
          ),
        ),
      ),
      'civicrm_activity' =>
      array(
        'dao' => 'CRM_Activity_DAO_Activity',
        'fields' =>
        array(
          'id' =>
          array(
            'no_display' => TRUE,
            'title' => ts('Activity ID'),
            'required' => TRUE,
          ),
          'source_record_id' =>
          array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'activity_type_id' =>
          array('title' => ts('Absence Type'),
            'required' => TRUE,
            'type' => CRM_Utils_Type::T_STRING,
          ),
        ),
        'filters' =>
        array(
          'absence_date' =>
          array(
            'title' => ts('Absence Date'),
            'default' => 'this.month',
            'type' => CRM_Utils_Type::T_DATE,
            'operatorType' => CRM_Report_Form::OP_DATE,
          ),
          'activity_type_id' =>
          array('title' => ts('Absence Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->activityTypes,
          ),
          'status_id' =>
          array('title' => ts('Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->activityStatus,
          ),
        ),
      ),
      'civicrm_activity_contact' =>
      array(
        'dao' => 'CRM_Activity_DAO_ActivityContact',
        'fields' =>
        array(
          // so we have $this->_alias populated
        ),
      ),
    ) ;

    parent::__construct();
  }

  function preProcessCommon() {
    parent::preProcessCommon();
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrabsence', 'css/hrabsence.css', 140, 'html-header');
  }

  function select() {
  }

  function where($sourceRecordIds = null) {
    $this->_where = "WHERE
 request.source_record_id IN ( ". implode(',', $sourceRecordIds).") AND cac.record_type_id =3";

    $clauses = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {

        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('type', $field) & CRM_Utils_Type::T_DATE) {
            continue;
          }
          if (array_key_exists("{$fieldName}_value", $this->_params)) {
            if ($field['name'] == 'activity_type_id' && count($this->_params["{$fieldName}_value"])) {
              $sqlOp = $this->getSQLOperator(CRM_Utils_Array::value("{$fieldName}_op", $this->_params));
              $clause = "absence.activity_type_id {$sqlOp} (" . implode(',',$this->_params["{$fieldName}_value"]) . ") ";
            }

            if ($field['name'] == 'display_name' && $this->_params["{$fieldName}_value"]) {
              $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
              $clause = $this->whereClause($field,
                $op,
                CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }
          }
          if ($field['name'] == 'current_user') {
            if (CRM_Utils_Array::value("{$fieldName}_value", $this->_params) == 1) {
              // get current user
              $session = CRM_Core_Session::singleton();
              if ($contactID = $session->get('userID')) {
                $clause = "cc.id = {$contactID}";
              }
              else {
                $clause = NULL;
              }
            }
            else {
              $clause = NULL;
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

  static function formRule($fields, $file, $self) {
    $errors = array();
    if ($fields['absence_date_relative'] == NULL) {
      $errors['absence_date_relative'] = ts('Please choose a Date Range');
    }
    elseif ($fields['absence_date_relative'] == '0') {
      if (empty($fields['absence_date_from'])) {
        $errors['absence_date_from'] = ts('Please choose a From Date');
          }
      if (empty($fields['absence_date_to'])) {
        $errors['absence_date_to'] = ts('Please choose a End Date');
      }

      $start = CRM_Utils_Date::processDate($fields['absence_date_from']);
      $end = CRM_Utils_Date::processDate($fields['absence_date_to']);
      if ($end < $start) {
        $errors['absence_date_to'] = ts('End date should be after Start date.');
      }
    }
    if (!array_key_exists('activity_type_id_value', $fields) ||
      (array_key_exists('activity_type_id_value', $fields) && (count($fields['activity_type_id_value']) == 0)) ||
      ($fields['activity_type_id_op'] == 'notin' && (count($fields['activity_type_id_value']) == count($self->activityTypes)))
    ) {
      $errors['activity_type_id_value'] = ts('Please choose atleast one Absence Type');
    }
    return $errors;
  }

  function add2group($groupID) {
  }

  function postProcess() {
    parent::beginPostProcess();

    $absenceCalendar = $filteredActivityTypes = $monthDays = $validSourceRecordIds = $statistics = $legend = array();
    $viewLinks = FALSE;

    $res = civicrm_api3('HRAbsenceType', 'get', array());
    $absenceTypes = $res['values'];

    //for legen color code
    if (array_key_exists('activity_type_id_value', $this->_params)) {
      $filteredActivityTypes = array_flip($this->_params['activity_type_id_value']);
      if ($this->_params['activity_type_id_op'] == 'notin') {
       $filteredActivityTypes = array_diff_key(array_flip(array_keys($this->activityTypes)), $filteredActivityTypes);
      }
    }

    for ($i=1; $i<=31; $i++) {
       $monthDays[] = $i;
    }

    foreach($absenceTypes as $key => $absenceType) {
      $count = $key-1;
      if (array_key_exists('debit_activity_type_id', $absenceType) &&
        array_key_exists($absenceType['debit_activity_type_id'], $filteredActivityTypes)
      ) {
        $legend[$absenceType['debit_activity_type_id']] = array(
          'title' => $absenceType['title'],
          'class' => "hrabsence-bg-{$count}-debit"
        );
      }
      if (array_key_exists('credit_activity_type_id', $absenceType) &&
        array_key_exists($absenceType['credit_activity_type_id'], $filteredActivityTypes)
      ) {
        $legend[$absenceType['credit_activity_type_id']] = array(
          'title' => $absenceType['title'] . ' (Credit)',
          'class' => "hrabsence-bg-{$count}-credit"
        );
      }
    }
    //for two or more absence type color code
    if (count($legend) >= 2) {
      $legend['mixed'] = array('title' => ts('Mixed'), 'class' => 'hrabsence-bg-mixed');
    }

    if (!empty($legend)) {
      $this->assign('legend', $legend);
      $this->assign('legendWidthPercent', ((1/count($legend))*100).'%');
    }

    $activityTypeID = CRM_Core_OptionGroup::getValue('activity_type', 'Absence', 'name');
    list($durationFromDate, $durationToDate) = $this->getFromTo(
      CRM_Utils_Array::value("absence_date_relative", $this->_params),
      CRM_Utils_Array::value("absence_date_from", $this->_params),
      CRM_Utils_Array::value("absence_date_to", $this->_params)
    );

    $durationYearCount =(date('Y', strtotime($durationToDate)))-(date('Y', strtotime($durationFromDate)));
    for ($i=0 ; $i<=$durationYearCount ; $i++) {
      $startCount = 1;
      $endCount = 12;
      $startDay = $endDay = null;
      //if the end date's year is same as that of start dates's year
      if($durationYearCount == 0) {
        $startCount = (int)date('m', strtotime($durationFromDate));
        $endCount = (int)date('m', strtotime($durationToDate));
      }
      elseif ($i==0) {
        $startCount = (int)date('m', strtotime($durationFromDate));
        $endCount = 12;
      }
      elseif ($i==$durationYearCount) {
        $startCount = 1;
        $endCount = (int)date('m', strtotime($durationToDate));
      }
      for ($j=$startCount; $j<=$endCount; $j++) {
        $absenceCalendar[date('Y', strtotime($durationFromDate))+$i][$j] = array(
          'start_day' => 1,
          'end_day' => cal_days_in_month(CAL_GREGORIAN,$j,((date('Y', strtotime($durationFromDate))+$i))),
          'month_name' => date("F", mktime(0, 0, 0, $j, 10)),
        );
      }
    }
    //assigning the start_day and end_day to corrosponding month in our absenceCalender array
    $absenceCalendar[date('Y', strtotime($durationFromDate))][(int)date('m', strtotime($durationFromDate))]['start_day'] = (int)date('d', strtotime($durationFromDate));
    $absenceCalendar[date('Y', strtotime($durationToDate))][(int)date('m', strtotime($durationToDate))]['end_day'] = (int)date('d', strtotime($durationToDate));


    $sql = "
SELECT source_record_id
FROM civicrm_activity
WHERE source_record_id IS NOT NULL AND
activity_type_id = {$activityTypeID}
GROUP BY source_record_id
HAVING ((to_days({$durationFromDate}) <= to_days(Min(activity_date_time))) AND
(to_days(Max(activity_date_time))  <= to_days({$durationToDate})))";

    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $validSourceRecordIds[] = $dao->source_record_id;
    }

    if (count($validSourceRecordIds) == 0) {
      CRM_Core_Session::setStatus(ts("There is no absence record for chosen Absence Date range"), ts('No Result Found'));
      return;
    }

    $this->_select =  "SELECT
YEAR(request.activity_date_time) as year,
MONTH(request.activity_date_time) as month,
DAY(request.activity_date_time) as day,
absence.activity_type_id as ati,
cac.contact_id as contact_id,
request.source_record_id,
cc.display_name as contact_name";

    $from = "
FROM civicrm_activity absence
INNER JOIN civicrm_activity request ON request.source_record_id = absence.id
LEFT JOIN civicrm_activity_contact cac ON cac.activity_id = absence.id
LEFT JOIN civicrm_contact cc ON cac.contact_id = cc.id
";

    $this->where($validSourceRecordIds);

    $sql = "{$this->_select} {$from} {$this->_where}";
    $dao = CRM_Core_DAO::executeQuery($sql);

    if (CRM_Core_Permission::check('access CiviCRM')) {
      $viewLinks = TRUE;
      $onHover = ts('View Contact Summary for this Contact');
      $onHoverAct = ts('View Absence Record');
    }

    while ($dao->fetch()) {
      if (array_key_exists($dao->year, $absenceCalendar) &&
        array_key_exists($dao->month, $absenceCalendar[$dao->year]) &&
        $dao->day >= $absenceCalendar[$dao->year][$dao->month]['start_day'] &&
        $dao->day <= $absenceCalendar[$dao->year][$dao->month]['end_day']
      ) {
        if (array_key_exists('contacts', $absenceCalendar[$dao->year][$dao->month]) &&
          array_key_exists($dao->contact_id, $absenceCalendar[$dao->year][$dao->month]['contacts']) &&
          array_key_exists($dao->day, $absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id]) &&
          $absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id][$dao->day]['activity_type_id'] != $dao->ati
        ) {
          $absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id][$dao->day]['activity_type_id'] = 'mixed';
        }
        else {
          $absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id][$dao->day]['activity_type_id'] = $dao->ati;
        }
        if ($viewLinks) {
          $url = CRM_Utils_System::url("civicrm/contact/view",'reset=1&cid=' . $dao->contact_id, $this->_absoluteUrl);
          $absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id]['link'] =
            "<a title='" . $onHover . "' href='" . $url . "'>".$dao->contact_name."</a>";
        }
        $absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id][$dao->day]['day_name'] = substr(date("D", mktime(0, 0, 0, $dao->month, $dao->day, $dao->year )), 0, -1);
      }
     $this->assign('monthDays', $monthDays);
     $this->assign('absenceCalendar', $absenceCalendar);
    }

    $this->filterStat($statistics);
    $this->assign('statistics', $statistics);

  }

  function alterDisplay(&$rows) {
  }
}