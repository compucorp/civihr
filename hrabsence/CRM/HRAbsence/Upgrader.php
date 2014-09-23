<?php

/**
 * Collection of upgrade steps
 */
class CRM_HRAbsence_Upgrader extends CRM_HRAbsence_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    $this->installActivityTypes();
    $this->addDefaultPeriod();
    $this->installAbsenceTypes();

    //$this->executeSqlFile('sql/myinstall.sql');
  }

  public function installActivityTypes() {
    $activityTypesResult = civicrm_api3('activity_type', 'get', array());
    $weight = count($activityTypesResult["values"]);

    if (!in_array("Public Holiday", $activityTypesResult["values"])) {
      $weight = $weight + 1;
      $params = array(
        'weight' => $weight,
        'label' => 'Public Holiday',
        'filter' => 1,
        'is_active' => 1,
        'is_optgroup' => 0,
        'is_default' => 0,
        'grouping' => 'Timesheet',
      );
      $resultCreatePublicHoliday = civicrm_api3('activity_type', 'create', $params);
    }

    if (!in_array("Absence", $activityTypesResult["values"])) {
      $weight = $weight + 1;
      $params = array(
        'weight' => $weight,
        'label' => 'Absence',
        'filter' => 1,
        'is_active' => 1,
        'is_optgroup' => 0,
        'is_default' => 0,
        'grouping' => 'Timesheet',
      );
      $resultCreateAbsence = civicrm_api3('activity_type', 'create', $params);
    }
  }

  public function addDefaultPeriod() {
    if (CRM_HRAbsence_BAO_HRAbsencePeriod::getRecordCount($params = array()) == 0) {
      $currentYear = date('Y');
      $params = array(
        'name' => $currentYear,
        'title' => $currentYear.' (Jan 1 to Dec 31)',
        'start_date' => $currentYear.'-01-01 00:00:00',
        'end_date' => $currentYear.'-12-31 23:59:59',
      );
      CRM_HRAbsence_BAO_HRAbsencePeriod::create($params);
    }
  }

  public function installAbsenceTypes() {
    $leaves = TRUE;
    $weight = 0;
    $values = '';
    $options =  CRM_Core_OptionGroup::values('hrjob_leave_type', TRUE, FALSE);
    if (empty($options)) {
      $leaves = FALSE;
      $options = array(
        'Sick' => 'Sick',
        'Vacation' => 'Vacation',
        'Maternity' => 'Maternity',
        'Paternity' => 'Paternity',
        'TOIL' => 'TOIL',
        'Other' => 'Other'
      );
    }
    $seperator = CRM_Core_DAO::VALUE_SEPARATOR;
    foreach ($options as $orgKey => $orgValue) {
      $params = array(
        'title' => $orgValue,
        'is_active' => 1,
        'allow_debits' => 1
      );
      if ($orgKey == 'TOIL') {
        $params['allow_credits'] = 1;
      }

      $absenceTypes = CRM_HRAbsence_BAO_HRAbsenceType::create($params);
      $values .= " WHEN '{$orgValue}' THEN '{$absenceTypes->id}'";

      if ($absenceTypes->debit_activity_type_id) {
        $absenceTypeID[] = $absenceTypes->debit_activity_type_id;
        if ($orgKey == 'Sick') {
          $sickTypeID = $absenceTypes->debit_activity_type_id;
        }
      }
      if ($absenceTypes->credit_activity_type_id) {
        $absenceTypeID[] = $absenceTypes->credit_activity_type_id;
        if ($orgKey == 'Sick') {
          $sickTypeID = $absenceTypes->debit_activity_type_id;
        }
      }
    }

    if (CRM_Core_DAO::checkTableExists('civicrm_hrjob_leave') && $leaves) {
      $query = "UPDATE civicrm_hrjob_leave
        SET leave_type = CASE leave_type
        {$values}
        END;";
      CRM_Core_DAO::executeQuery($query);
    }
    CRM_Core_OptionGroup::deleteAssoc('hrjob_leave_type');

    $absenceTypeIDs = implode($seperator, $absenceTypeID);
    $paramsCGroup = array(
      'title' => 'Absence Comment',
      'extends' => array(
        '0' => 'Activity',
      ),
      'style' => 'Inline',
      'extends_entity_column_value' => array(
        '0' => $absenceTypeIDs
      ),
      'is_active' => 1,
    );
    $resultCGroup = civicrm_api3('custom_group', 'create', $paramsCGroup);

    $paramsCField = array(
      'custom_group_id' => $resultCGroup['id'],
      'label' => 'Comment',
      'html_type' => 'TextArea',
      'data_type' => 'Memo',
      'is_active' => 1,
    );
    $resultCField = civicrm_api3('custom_field', 'get', $paramsCField);
    if ($resultCField['count'] == 0) {
      $resultCField = civicrm_api3('custom_field', 'create', $paramsCField);
    }

    $paramsSGroup = array(
      'title' => 'Type of Sickness',
      'extends' => array(
        '0' => 'Activity',
       ),
      'style' => 'Inline',
      'extends_entity_column_value' => array(
        '0' => $sickTypeID
      ),
      'is_active' => 1,
    );
    $resultSGroup = civicrm_api3('custom_group', 'create', $paramsSGroup);

    $paramsSField = array(
      'custom_group_id' => $resultSGroup['id'],
      'label' => 'Sick Type',
      'html_type' => 'Select',
      'data_type' => 'String',
      'is_active' => 1,
    );
    $resultSField = civicrm_api3('custom_field', 'get', $paramsSField);
    if ($resultSField['count'] == 0) {
      $resultSField = civicrm_api3('custom_field', 'create', $paramsSField);
    }

    $sickType = array('Cold','Cough','Fever');
    foreach ($sickType as $Key => $val) {
      $paramsOVal = array(
        'sequential' => 1,
        'name' => $val,
        'option_group_id' => $resultSField['values'][$resultSField['id']]['option_group_id'],
      );
      civicrm_api3('OptionValue', 'create', $paramsOVal);
    }
  }

  /**
   * Example: Run a simple query when a module is enabled
   **/
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE civicrm_hrabsence_type SET is_active = 1');
  }

  /**
   * Example: Run a simple query when a module is disabled
   **/
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE civicrm_hrabsence_type SET is_active = 0');
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled
   **/
  public function uninstall() {
    $dao = CRM_Core_DAO::executeQuery('SELECT * from civicrm_hrabsence_type');
    while($dao->fetch()) {
      if($dao->credit_activity_type_id) {
        $query = "DELETE FROM civicrm_activity WHERE activity_type_id IN ( {$dao->credit_activity_type_id} )";
        CRM_Core_DAO::executeQuery($query);
        CRM_Core_BAO_OptionValue::del($dao->credit_activity_type_id);
      }
      if($dao->debit_activity_type_id) {
        $query = "DELETE FROM civicrm_activity WHERE activity_type_id IN ( {$dao->debit_activity_type_id} )";
        CRM_Core_DAO::executeQuery($query);
        CRM_Core_BAO_OptionValue::del($dao->debit_activity_type_id);
      }
    }
    CRM_Core_DAO::executeQuery('DROP TABLE civicrm_hrabsence_entitlement');
    CRM_Core_DAO::executeQuery('DROP TABLE civicrm_hrabsence_period');
    CRM_Core_DAO::executeQuery('DROP TABLE civicrm_hrabsence_type');
  }

  /**
   * Example: Run a couple simple queries
   *
   * @return TRUE on success
   * @throws Exception
   *
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
  } // */


  /**
   * Example: Run an external SQL script
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */
  public function upgrade_1201() {
    $this->ctx->log->info('Planning update 1201'); // PEAR Log interface

    $seperator = CRM_Core_DAO::VALUE_SEPARATOR;

    $params = array(
      'version' => 3,
      'sequential' => 1,
    );
    $result = civicrm_api3('HRAbsenceType', 'get', $params);
    foreach ($result['values'] as $key => $value) {
      if ($value['title'] == 'Sick') {
        $sickTypeID = $value['debit_activity_type_id'];
      }
      if ($value['debit_activity_type_id']) {
        $absenceTypeID[] = $value['debit_activity_type_id'];
      }
      if ($value['credit_activity_type_id']) {
        $absenceTypeID[] = $value['credit_activity_type_id'];
      }
    }
    $absenceTypeid = array_unique($absenceTypeID);
    $absenceTypeIDs = implode($seperator, $absenceTypeid);

    $paramsCGroup = array(
      'title' => 'Absence Comment',
      'extends' => array(
        '0' => 'Activity',
      ),
      'style' => 'Inline',
      'extends_entity_column_value' => array(
        '0' => $absenceTypeIDs
      ),
      'is_active' => 1,
    );
    $resultCGroup = civicrm_api3('custom_group', 'create', $paramsCGroup);

    $paramsCField = array(
      'custom_group_id' => $resultCGroup['id'],
      'label' => 'Comment',
      'html_type' => 'TextArea',
      'data_type' => 'Memo',
      'is_required' => 1,
      'is_searchable' => 0,
      'is_active' => 1,
    );
    civicrm_api3('custom_field', 'create', $paramsCField);

    $paramsSGroup = array(
      'title' => 'Type of Sickness',
      'extends' => array(
        '0' => 'Activity',
       ),
      'style' => 'Inline',
      'extends_entity_column_value' => array(
        '0' => $sickTypeID
      ),
      'is_active' => 1,
    );
    $resultSGroup = civicrm_api3('custom_group', 'create', $paramsSGroup);

    $paramsSField = array(
      'custom_group_id' => $resultSGroup['id'],
      'label' => 'Sick Type',
      'html_type' => 'Select',
      'data_type' => 'String',
      'is_required' => 1,
      'is_searchable' => 0,
      'is_active' => 1,
    );
    $resultSField = civicrm_api3('custom_field', 'create', $paramsSField);

    $sickType = array('Cold','Cough','Fever');
    foreach ($sickType as $Key => $val) {
      $paramsOVal = array(
        'sequential' => 1,
        'name' => $val,
        'option_group_id' => $resultSField['values'][$resultSField['id']]['option_group_id'],
      );
      civicrm_api3('OptionValue', 'create', $paramsOVal);
    }
    return TRUE;
  }

  public function upgrade_1400() {
    $this->ctx->log->info('Planning update 1400'); // PEAR Log interface
    /* Create message template for absence leave application */
    $msg_text = '{ts}Dear{/ts} {$displayName},
    {ts}Employee:{/ts} {$empName}
    {ts}Position:{/ts} {$empPosition}
    {ts}Absence Type:{/ts} {$absenceType}
    {ts}Dates:{/ts} {$startDate} - {$endDate}

      {if $cancel}
        {ts}Leave has been cancelled.{/ts}
      {elseif $reject}
        {ts}Leave has been rejected.{/ts}
      {elseif $approval}
        Leave has been approved for {$appDays}.
      {/if}

      {ts}Date{/ts} | {ts}Absence{/ts} | {if $approval and $totDays neq $appDays} {ts}Approve{/ts} {/if}
      {foreach from=$absentDateDurations item=value key=label}
        {if $value.duration != 0}
          {$label|date_format} | {if $value.duration == $jobHoursTime.Full_Time*60} {ts}Full Day{/ts} {elseif $value.duration == $jobHoursTime.Part_Time*60} {ts}Half Day{/ts} {/if} | {if $approval and $totDays neq $appDays} {if $value.approval == 2}{ts}Approved{/ts} {elseif $value.approval == 9} {ts}Unapproved{/ts} {/if} {/if}
        {/if}
      {/foreach}
      {ts}Total{/ts} | {$totDays}
    {if $customGroup}
      {foreach from=$customGroup item=value key=customName}
        {foreach from=$value item=v key=n}
          {$customName} : {$v}
        {/foreach}
      {/foreach}
    {/if}';

  $msg_html = '<p>{ts}Dear{/ts} {$displayName},</p>
    <table>
      <tbody>
        <tr>
          <td>{ts}Employee:{/ts}</td>
          <td>{$empName}</td>
        </tr>
		    <tr>
          <td>{ts}Position:{/ts}</td>
          <td>{$empPosition}</td>
        </tr>
        <tr>
          <td>{ts}Absence Type:{/ts}</td>
          <td>{$absenceType}</td>
        </tr>
        <tr>
          <td>{ts}Dates:{/ts}</td>
          <td>{$startDate|date_format} - {$endDate|date_format}</td>
        </tr>
      </tbody>
    </table>
      {if $cancel}
        <p> {ts}Leave has been cancelled.{/ts} </p>
      {elseif $reject}
        <p> {ts}Leave has been rejected.{/ts} </p>
      {elseif $approval}
        <p> Leave has been approved for {$appDays}.</p>
      {/if}
      <br/>
      <table border="1" border-spacing="0">
        <tbody>
          <tr>
            <th> {ts}Date{/ts} </th>
            <th> {ts}Absence{/ts} </th>
            {if $approval and $totDays neq $appDays}
              <th> {ts}Status{/ts} </th>
            {/if}
          </tr>
          {foreach from=$absentDateDurations item=value key=label}
            {if $value.duration != 0}
              <tr>
                <td>{$label|date_format}</td>
                <td>{if $value.duration == $jobHoursTime.Full_Time*60} {ts}Full Day{/ts} {elseif $value.duration == $jobHoursTime.Part_Time*60} {ts}Half Day{/ts} {else} &nbsp;{/if}</td>
                {if $approval and $totDays neq $appDays}
                  <td>{if $value.approval == 2} {ts}Approved{/ts} {elseif $value.approval == 9} {ts}Unapproved{/ts} {else}{/if}</td>
                {/if}
              </tr>
            {/if}
          {/foreach}
          <tr>
            <td>{ts}Total{/ts}</td>
            <td>{$totDays}</td>
            {if $approval and $totDays neq $appDays}
              <td> &nbsp; </td>
            {/if}
          </tr>
        </tbody>
      </table>
    <br/>
    {if $customGroup}
      <table>
        <tbody>
          {foreach from=$customGroup item=value key=customName}
            {foreach from=$value item=v key=n}
              <tr>
                <td>
                  {$customName} :
                </td>
                <td>
                  {$v}
                </td>
              </tr>
            {/foreach}
          {/foreach}
        </tbody>
      </table>
    {/if}';

    $subject = '{if $approval}Absences Approved{elseif $cancel}Absences Cancelled{elseif $reject}Absences Rejected{else}Absences Application{/if}';
    $msg_params = array(
      'msg_title' => 'Absence Email',
      'msg_subject' => $subject,
      'msg_text' => $msg_text,
      'msg_html' => $msg_html,
      'workflow_id' => NULL,
      'is_default' => '1',
      'is_reserved' => '0',
    );
    civicrm_api3('message_template', 'create', $msg_params);

    //Update absence status
    $absenceType = CRM_HRAbsence_BAO_HRAbsenceType::getActivityTypes();
    $absenceTypeIds = implode(",",array_flip($absenceType));
    $selectQuery = "SELECT id, status_id FROM civicrm_activity WHERE activity_type_id IN ({$absenceTypeIds})";
    $dao = CRM_Core_DAO::executeQuery($selectQuery);
    $absenceStatus = array();
    while ($dao->fetch()) {
      $absenceStatus[$dao->id] = $dao->status_id;
    }
    foreach($absenceStatus as $k => $v) {
      $updateQuery = "UPDATE civicrm_activity SET status_id = {$v} WHERE source_record_id = {$k}";
      CRM_Core_DAO::executeQuery($updateQuery);
    }

    return TRUE;
  }
}
