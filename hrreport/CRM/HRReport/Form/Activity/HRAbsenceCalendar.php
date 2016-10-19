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
    $this->activityStatus = CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus('name');
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
          'sort_name' =>
          array(
            'name' => 'sort_name',
            'title' => ts('Individual'),
            'operator' => 'like',
            'dbAlias' => 'cc.sort_name',
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
            'options' => CRM_HRAbsence_BAO_HRAbsenceType::getActivityStatus(),
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

  function setDefaultValues($freeze = TRUE) {
    parent::setDefaultValues($freeze);
    $activityStatus = array_flip($this->activityStatus);
    $this->_defaults["status_id_value"] = array($activityStatus['Scheduled'], $activityStatus['Completed']);
    return $this->_defaults;
  }

  function preProcessCommon() {
    parent::preProcessCommon();
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrabsence', 'css/hrabsence.css', 140, 'html-header');

    //assigning legend to templete
    $res = civicrm_api3('HRAbsenceType', 'get', array());
    $absenceTypes = $res['values'];
    foreach($absenceTypes as $key => $absenceType) {
      $count = $key-1;
      if (array_key_exists('debit_activity_type_id', $absenceType)) {
        $legend[$absenceType['debit_activity_type_id']] = array(
          'title' => $absenceType['title'],
          'class' => "hrabsence-bg-{$count}-debit"
        );
      }
      if (array_key_exists('credit_activity_type_id', $absenceType)) {
        $legend[$absenceType['credit_activity_type_id']] = array(
          'title' => $absenceType['title'] . ' (Credit)',
          'class' => "hrabsence-bg-{$count}-credit"
        );
      }
    }
    //for two or more absence type color code
    $legend['Mixed'] = array('title' => ts('Mixed'), 'class' => 'hrabsence-bg-mixed');

    $this->assign('legend', $legend);
    $this->assign('legendWidthPercent', ((1/count($legend))*100).'%');
  }

  function select() {
  }

  function from() {
    $this->_from = "
FROM civicrm_activity absence
INNER JOIN civicrm_activity request ON request.source_record_id = absence.id
LEFT JOIN civicrm_activity_contact cac ON cac.activity_id = absence.id
LEFT JOIN civicrm_contact cc ON cac.contact_id = cc.id
";

    if ($this->_aclFrom) {
      $this->_from .= $this->_aclFrom;
    }
  }

  function where($sourceRecordIds = null) {
    $targetValue = CRM_Core_OptionGroup::getValue('activity_contacts', 'Activity Targets', 'name');
    $this->_where = "WHERE
cac.record_type_id = {$targetValue} ";

    if (is_array($sourceRecordIds)) {
      $this->_where .= "AND request.source_record_id IN (" . implode(',', $sourceRecordIds) . ") ";
    }
    elseif ($sourceRecordIds == 'all') {
      $activityTypeID = CRM_Core_OptionGroup::getValue('activity_type', 'Absence', 'name');
      $this->_where .= "AND request.activity_type_id = {$activityTypeID} ";
    }

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
              // If the activity_type_id_value is not an array that's mean it is Not
              // coming from the (absence type) select filter and it should be discarded.
              if (!is_array($this->_params["{$fieldName}_value"])) {
                unset($this->_params["{$fieldName}_value"]);
              } else {
                $sqlOp = $this->getSQLOperator(CRM_Utils_Array::value("{$fieldName}_op", $this->_params));
                $clause = "absence.{$fieldName} {$sqlOp} (" . implode(',',$this->_params["{$fieldName}_value"]) . ") ";
              }
            }

            if ($field['name'] == 'sort_name' && $this->_params["{$fieldName}_value"]) {
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
          if ($field['name'] == 'status_id') {
            $clause = NULL;
            if ($status = CRM_Utils_Array::value("{$fieldName}_value", $this->_params)) {
              $clause = "request.status_id IN (". implode(',',$status).")";
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
  }

  static function formRule($fields, $file, $self) {
    $errors = array();
    if ($fields['absence_date_relative'] == '0') {
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
    return $errors;
  }

  function add2group($groupID) {
    if(empty($groupID)) {
      CRM_Core_Session::setStatus(" ", ts('Please select a Group'),'warning');
      return;
    }
    $query = "SELECT cac.contact_id as contact_id {$this->_from} {$this->_where} GROUP BY cac.contact_id";
    $dao = CRM_Core_DAO::executeQuery($query);

    $contactIDs = array();
    // Add resulting contacts to group
    while ($dao->fetch()) {
          $contactIDs[$dao->contact_id] = $dao->contact_id;
    }

    if ( !empty($contactIDs) ) {
      CRM_Contact_BAO_GroupContact::addContactsToGroup($contactIDs, $groupID);
      CRM_Core_Session::setStatus(ts("Listed contact(s) have been added to the selected group."), ts('Contacts Added'), 'success');
    }
    else {
      CRM_Core_Session::setStatus(ts("The listed records(s) cannot be added to the group."));
   }
  }

  function buildACLClause($tableAlias = array()) {

    if (CRM_Core_Permission::check('view all contacts')) {
      $this->_aclFrom = $this->_aclWhere = NULL;
      return;
    }

    $session = CRM_Core_Session::singleton();
    $contactID = $session->get('userID');
    if (!$contactID) {
      $contactID = 0;
    }
    $contactID = CRM_Utils_Type::escape($contactID, 'Integer');

    CRM_Contact_BAO_Contact_Permission::cache($contactID);
    $clauses = array();
    foreach ($tableAlias as $k => $alias) {
      $clauses[] = " INNER JOIN civicrm_acl_contact_cache aclContactCache_{$k} ON ( {$alias}.contact_id = aclContactCache_{$k}.contact_id OR {$alias}.contact_id IS NULL ) AND aclContactCache_{$k}.user_id = $contactID ";
    }

    $this->_aclFrom = implode(" ", $clauses);
  }

  function postProcess() {
    $this->buildACLClause(array('cac'));
    parent::beginPostProcess();

    $activityStatus = array_flip($this->activityStatus);
    $statusCSSStyle = array(
      $activityStatus['Scheduled']  => 'font-style:italic;',
      $activityStatus['Completed'] => 'font-weight:bold;',
      $activityStatus['Cancelled'] => 'text-decoration:line-through;',
      $activityStatus['Rejected'] => 'text-decoration:line-through;'
    );

    $absenceCalendar = $monthDays  = $statistics = $legend = array();
    $validSourceRecordIds = null;
    $viewLinks = FALSE;

    $activityTypeID = CRM_Core_OptionGroup::getValue('activity_type', 'Absence', 'name');
    list($durationFromDate, $durationToDate) = $this->getFromTo(
      CRM_Utils_Array::value("absence_date_relative", $this->_params),
      CRM_Utils_Array::value("absence_date_from", $this->_params),
      CRM_Utils_Array::value("absence_date_to", $this->_params)
    );

    $sql = "
FROM civicrm_activity
WHERE source_record_id IS NOT NULL AND
activity_type_id = {$activityTypeID}
";

    if ($durationFromDate && $durationToDate) {
      $sql = "SELECT source_record_id " . $sql;
      $sql .= "
        GROUP BY source_record_id
        HAVING ((to_days({$durationFromDate}) <= to_days(Min(activity_date_time))) AND
        (to_days(Max(activity_date_time))  <= to_days({$durationToDate})))
        ";
    }
    else {
      $sql = "SELECT Min(activity_date_time) as fromDate, Max(activity_date_time) as toDate" . $sql;
    }

    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      if (property_exists($dao, 'fromDate')) {
        $durationFromDate = CRM_Utils_Date::processDate($dao->fromDate);
        $durationToDate = CRM_Utils_Date::processDate($dao->toDate);
        $validSourceRecordIds = 'all';
      }
      else {
        $validSourceRecordIds[] = $dao->source_record_id;
      }
    }

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
          'end_day' => date("t", mktime(0,0,0,$j, 1, ((date('Y', strtotime($durationFromDate))+$i)))),
          'month_name' => date("F", mktime(0, 0, 0, $j, 10)),
        );
      }
    }
    //assigning the start_day and end_day to corrosponding month in our absenceCalender array
    $absenceCalendar[date('Y', strtotime($durationFromDate))][(int)date('m', strtotime($durationFromDate))]['actual_start_day'] = (int)date('d', strtotime($durationFromDate));
    $absenceCalendar[date('Y', strtotime($durationToDate))][(int)date('m', strtotime($durationToDate))]['actual_end_day'] = (int)date('d', strtotime($durationToDate));

    foreach ($absenceCalendar as $key=>$val ) {
      krsort($val);
      $absenceCalendar[$key] = $val;
    }
    krsort($absenceCalendar);

    if (count($validSourceRecordIds) == 0 || !$validSourceRecordIds) {
      CRM_Core_Session::setStatus(ts("There is no absence record for chosen Absence Date range"), ts('No Result Found'));
      return;
    }

    $select =  "SELECT
YEAR(request.activity_date_time) as year,
MONTH(request.activity_date_time) as month,
DAY(request.activity_date_time) as day,
absence.id as aid,
absence.activity_type_id as ati,
request.status_id status,
cac.contact_id as contact_id,
request.source_record_id,
cc.sort_name as contact_name";

    $this->from();
    $this->where($validSourceRecordIds);

    $sql = "{$select} {$this->_from} {$this->_where}
ORDER BY YEAR(request.activity_date_time), MONTH(request.activity_date_time), cc.sort_name
";
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
          $absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id][$dao->day]['activity_type_id'] = 'Mixed';
        }
        else {
          $absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id][$dao->day]['activity_type_id'] = $dao->ati;
        }
        if ($viewLinks) {
          $url = CRM_Utils_System::url("civicrm/contact/view",'reset=1&cid=' . $dao->contact_id, $this->_absoluteUrl);
          $absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id]['link'] =
            "<a title='" . $onHover . "' href='" . $url . "' style='font-weight:bold;'>".$dao->contact_name."</a>";
        }
        if ($absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id][$dao->day]['activity_type_id'] != 'Mixed') {
          $dateUrl = CRM_Utils_System::url("civicrm/absence/set",'reset=1&action=update&aid=' . $dao->aid, $this->_absoluteUrl);
          $day_name = "<a title='". $this->activityStatus[$dao->status] ."' href={$dateUrl} style='" . $statusCSSStyle[$dao->status] . "'>" . substr(date("D", mktime(0, 0, 0, $dao->month, $dao->day, $dao->year )), 0, -1) ."</a>";
        }
        else {
          $day_name = substr(date("D", mktime(0, 0, 0, $dao->month, $dao->day, $dao->year )), 0, -1);
        }
        $absenceCalendar[$dao->year][$dao->month]['contacts'][$dao->contact_id][$dao->day]['day_name'] = $day_name;
      }
    }

    //remove those months from calendar report which don't have any absences
    foreach ($absenceCalendar as $year => $monthlyRecord) {
      foreach ($monthlyRecord as $month => $record) {
        if (!array_key_exists('contacts', $record)) {
          unset($absenceCalendar[$year][$month]);
        }
      }
    }

    $this->modifyColumnHeaders();
    $this->doTemplateAssignment($absenceCalendar);
    $this->endPostProcess($absenceCalendar);
  }

  function doTemplateAssignment(&$rows) {
    $monthDays = $statistics = array();

    for ($i=1; $i<=31; $i++) {
      $monthDays[] = $i;
    }

    $this->assign('monthDays', $monthDays);
    $this->assign('rows', $rows);

    $this->filterStat($statistics);
    $this->assign('statistics', $statistics);
  }

  function endPostProcess(&$rows = NULL) {
    $csvRows = array();
    $count = 0;

    foreach ($rows as $year => $yearlyRecord) {
      foreach ($yearlyRecord as $month => $monthlyrecord) {
        if (!array_key_exists('contacts', $monthlyrecord)) {
          continue;
        }
        foreach ($monthlyrecord['contacts'] as $contact_id => $record) {
          $csvRows[$count]['year'] = $year;
          $csvRows[$count]['month'] = $monthlyrecord['month_name'];
          $csvRows[$count]['contact_id'] = $contact_id;
          $csvRows[$count]['individual'] = CRM_Contact_BAO_Contact::displayName($contact_id);
          for ($i=1; $i<=31; $i++) {
            $csvRows[$count]['day_'.$i] = "";
          }
          foreach ($record as $day => $dayRecord) {
            if($day == 'link') {
              continue;
            }
            if (array_key_exists($dayRecord['activity_type_id'], $this->activityTypes)) {
              $csvRows[$count]['day_'.$day] = $this->activityTypes[$dayRecord['activity_type_id']];
            }
            else {
              $csvRows[$count]['day_'.$day] = $dayRecord['activity_type_id'];
            }
          }
          $count++;
        }
      }
    }
    parent::endPostProcess($csvRows);
  }

  function modifyColumnHeaders() {
    $this->_columnHeaders = array(
      'year' => array(
        'title' => 'Year',
        'type' => CRM_Utils_Type::T_INT,
      ),
      'month' => array(
        'title' => 'Month',
        'type' => CRM_Utils_Type::T_STRING,
      ),
      'individual' => array(
        'title' => 'Individual',
        'type' => CRM_Utils_Type::T_STRING,
      ),
      'contact_id' => array(
        'title' => 'Contact ID',
        'type' => CRM_Utils_Type::T_INT,
      ),
    );

    for ($i=1; $i<=31; $i++) {
      $this->_columnHeaders['day_'.$i] = array(
        'title' => 'Day '.$i,
        'type' => CRM_Utils_Type::T_STRING,
      );
    }
  }
}
