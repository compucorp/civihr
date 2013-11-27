<?php
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
 * Collection of upgrade steps
 */
class CRM_HRVisa_Upgrader extends CRM_HRVisa_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    // $this->executeCustomDataFile('xml/customdata.xml');
    // $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
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
  
  public function upgrade_1104() {
    $this->ctx->log->info('Applying update 1104');
    $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
    $cgid = array_search('Immigration', $groups);
    $cfId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', 'Sponsor_Certificate_number', 'id', 'name');

    if($cgid && !$cfId) {
      $cfparams = array(
        'custom_group_id' => $cgid,
        'name' => 'Sponsor_Certificate_number',
        'label' => 'Sponsor\'s Certificate number',
        'html_type' => 'Text',
        'data_type' => 'String',
        'default_value' => '',
        'weight' => 34,
        'is_active' => 1,
      );
      $cfresult =CRM_Core_BAO_CustomField::create($cfparams);
      $cfId = $cfresult->id;
    }
    if( $cfId ) {
      $ufgroups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
      $ufid = array_search('hrvisa_tab', $ufgroups);
      $eufId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFField', 'custom_'.$cfId, 'id', 'field_name');

      if(!$eufId && $ufid){
        $ufparams = array(
          'field_name' => 'custom_'.$cfId,
          'field_type' => 'Individual',
          'visibility' => 'User and User Admin Only',
          'label' => 'Sponsor\'s Certificate number',
          'is_searchable' => 0,
          'is_active' => 0,
          'uf_group_id' => $ufid,
          'is_multi_summary' => 1,
          'is_active'=> 0,
          'is_required'=> 0,
          'in_selector'=> 0,
        );
        $ufresult = civicrm_api3('uf_field', 'create', $ufparams);
      }
    }
    return TRUE;
  }

  public function upgrade_1105() {
    $this->ctx->log->info('Applying update 1105');
    $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
    $customFieldID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', 'Is_Visa_Required', 'id', 'name');
    $customGroupID = array_search('Extended_Demographics', $groups);

    if ($customFieldID && $customGroupID) {
      CRM_Core_BAO_CustomField::moveField($customFieldID, $customGroupID);

      $result = civicrm_api3('CustomField', 'get', array(
        'sequential' => 1,
        'name' => 'Is_Visa_Required',
      ));
      $weight = $result['values']['weight'];

      //fix the weight so that the field is next to nationality
      $fieldValues['custom_group_id'] = $customGroupID;
      CRM_Utils_Weight::updateOtherWeights('CRM_Core_DAO_CustomField', $weight, 2, $fieldValues);

      $params = array(
        'sequential' => 1,
        'id' => $result['id'],
        'is_active' => 1,
        'html_type' => 'Radio',
        'data_type' => 'Boolean',
        'weight' => 2
      );
      $result = civicrm_api3('CustomField', 'create', $params);
    }
    return TRUE;
  }

  public function upgrade_1116() {
    $this->ctx->log->info('Planning update 1116'); // PEAR Log interface
    $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
    $gid = array_search('hrvisa_tab', $groups);
    $params = array(
      'action' => 'submit',
      'profile_id' => $gid,
    );
    $result = civicrm_api3('profile', 'getfields', $params);
    if($result['is_error'] == 0 ) {
      foreach($result['values'] as $key => $value) {
        if(isset($value['html_type']) && $value['html_type'] == "File") {
          CRM_Core_DAO::executeQuery("UPDATE civicrm_uf_field SET is_multi_summary = 1 WHERE civicrm_uf_field.uf_group_id = {$gid} AND civicrm_uf_field.field_name = '{$key}'");
        }
      }
    }
    return TRUE;
  }

  public function upgrade_1106() {
    $this->ctx->log->info('Applying update 1106');
    // create activity_type 'Visa Expiration'
    $params = array(
      'weight' => 1,
      'label' => 'Visa Expiration',
      'filter' => 0,
      'is_active' => 1,
      'is_default' => 0
    );
    $resultActivityType = civicrm_api3('activity_type', 'create', $params);

    if ($resultActivityType['is_error']) {
      return FALSE;
    }

    // find all contacts who require visa
    $cfId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', 'Is_Visa_Required', 'id', 'name');
    $params = array(
      "custom_{$cfId}" => 1,
      'return.id' => 1
    );

    $result = civicrm_api3('contact', 'get', $params);
    if ($result['count']) {
      foreach ($result['values'] as $value) {
        CRM_HRVisa_Activity::sync($value['id']);
      }
    }

    // create weekly reminder for Visa Expiration
    $actionSchedule = civicrm_api3('action_schedule', 'get', array('name' => 'Visa Expiration Reminder'));
    $activityTypeId =  $resultActivityType['values'][$resultActivityType['id']]['value'];
    if (empty($actionSchedule['id'])) {
      $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
      $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);

      $params = array(
        'name' => 'Visa Expiration Reminder',
        'title' => 'Visa Expiration Reminder',
        'recipient' => $targetID,
        'limit_to' => 1,
        'entity_value' => $activityTypeId,
        'entity_status' => CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name'),
        'start_action_offset' => 1,
        'start_action_unit' => 'week',
        'start_action_condition' => 'before',
        'start_action_date' => 'activity_date_time',
        'is_repeat' => 0,
        'is_active' => 1,
        'body_html' => '<p>Your latest visa expiries on {activity.activity_date_time}</p>',
        'subject' => 'Reminder for Visa Expiration',
        'record_activity' => 1,
        'mapping_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value')
      );
      $result = civicrm_api3('action_schedule', 'create', $params);
      if ($result['is_error']) {
        return FALSE;
      }
    }
    return TRUE;
  }
}
