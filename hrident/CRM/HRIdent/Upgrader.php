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
 * Collection of upgrade steps
 */
class CRM_HRIdent_Upgrader extends CRM_HRIdent_Upgrader_Base {

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
  public function upgrade_1113() {
    $this->ctx->log->info('Planning update 1113'); // PEAR Log interface
    $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
    $gid = array_search('hrident_tab', $groups);
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

  public function upgrade_1200() {
    $this->ctx->log->info('Planning update 1200'); //PEAR Log interface
    $params = array(
      'option_group_id' => 'type_20130502144049',
      'label' => 'National Insurance',
      'value' => 'National Insurance',
      'name' => 'National_Insurance',
    );

    $result = civicrm_api3('OptionValue', 'create', $params);
    if ($result['is_error'] == 0) {
      return TRUE;
    }
    return FALSE;
  }

  public function upgrade_1400() {
    $this->ctx->log->info('Planning update 1400'); //PEAR Log interface
    // create custom field
    $cusGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Identify', 'id', 'name');
    $custGroupParams = array(
      'custom_group_id' => $cusGroupID,
      'name' => "is_government",
      'label' => "Is Government",
      'data_type' => "Boolean",
      'html_type' => "Radio",
      'is_active' => "1",
      'is_view' => "1",
      'column_name' => 'is_government',
    );
    $result = civicrm_api3('CustomField', 'create', $custGroupParams);
    $cusField = array(
      'custom_group_id' => "Identify",
      'name' => "is_government",
      'return' => "id",
    );
    $govRecord_id  = 'custom_'.civicrm_api3('CustomField', 'getvalue', $cusField);

    //create uffield
    $ufGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrident_tab', 'id', 'name');
    $ufFieldParam = array(
      'uf_group_id' => $ufGroupID,
      'field_name' => $govRecord_id,
      'is_active' => "1",
      'label' => "Is Government",
      'field_type' => "Individual",
      'is_view' => "1",
    );
    $result = civicrm_api3('UFField', 'create', $ufFieldParam);
    $groupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Identify', 'id', 'name');
    CRM_Core_DAO::setFieldValue('CRM_Core_DAO_CustomGroup', $groupID, 'is_reserved', '0');

    //HR-355 Change the title of option group to identify type
    $optgroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'type_20130502144049', 'id', 'name');
    CRM_Core_DAO::setFieldValue('CRM_Core_DAO_OptionGroup', $optgroupID, 'title', 'Government ID');

    $sql = "UPDATE civicrm_custom_field SET in_selector = '1' WHERE custom_group_id = {$cusGroupID} AND name IN ('Type','Number','Issue_Date','Expire_Date','Country','State_Province','Evidence_File')";
    CRM_Core_DAO::executeQuery($sql);
    CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET style = 'Tab with table' WHERE id = {$cusGroupID}");

    return TRUE;
  }
  
  public function upgrade_1500(){
      $this->ctx->log->info('Planning update 1500'); //PEAR Log interface
      
      // Make is_goverment field editable
      $sql = "UPDATE civicrm_custom_field SET is_view = '0' WHERE name = 'is_government'";
      CRM_Core_DAO::executeQuery($sql);
      
      return true;
  }

  /**
   * Upgrader to set Government ID >> National Insurance
   * to be the default one and changing its weight
   * to be on the top.
   */
  public function upgrade_1501() {
    civicrm_api3('OptionValue', 'create', array(
      'sequential' => 1,
      'option_group_id' => "type_20130502144049",
      'name' => "National_Insurance",
      'is_default' => 1,
      'weight' => 0,
    ));
  }
}
