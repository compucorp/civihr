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
class CRM_HREmerg_Upgrader extends CRM_HREmerg_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    $relationshipTypeId = $this->findCreateEmergencyContactRelationType();
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('hremerg_relationship_type_id', $relationshipTypeId);
    $this->executeCustomDataTemplateFile('hremerg-customdata.xml.tpl');
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


  /**
   * @return int
   * @throws CRM_Core_Exception
   */
  public function findCreateEmergencyContactRelationType() {
    $relationshipType = civicrm_api('RelationshipType', 'getsingle', array(
      'version' => 3,
      'label_a_b' => 'Emergency Contact Is',
    ));
    if (!empty($relationshipType['id'])) {
      return $relationshipType['id'];
    }
    else {
      $result = civicrm_api('RelationshipType', 'create', array(
        'version' => 3,
        'name_a_b' => 'Emergency Contact',
        'label_a_b' => 'Emergency Contact is',
        'name_b_a' => 'Emergency Contact',
        'label_b_a' => 'Emergency Contact for',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
        'is_active' => '1',
      ));
      if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
        CRM_Core_Error::debug_var('relationshipTypeResult', $result);
        throw new CRM_Core_Exception('Failed to register relationship type');
      }
      return $result['id'];
    }
  }

  public function executeCustomDataTemplateFile($relativePath) {
    $smarty = CRM_Core_Smarty::singleton();
    $xmlCode = $smarty->fetch($relativePath);
    //x dpm($xmlCode);
    $xml = simplexml_load_string($xmlCode);

    require_once 'CRM/Utils/Migrate/Import.php';
    $import = new CRM_Utils_Migrate_Import();
    $import->runXmlElement($xml);
    return TRUE;
  }

  public function upgrade_1400() {
    $this->ctx->log->info('Planning update 1400'); // PEAR Log interface
    $profileId = civicrm_api3('UFGroup', 'getsingle', array( 'return' => "id",  'name' => "new_individual",));
    $summaryId = civicrm_api3('UFGroup', 'getsingle', array('return' => "id",  'name' => "summary_overlay"));
    $location = civicrm_api3('LocationType', 'getsingle', array('return' => "id", 'name' => "Main"));
    $phoneTypes = CRM_Core_OptionGroup::values('phone_type');
    $phone =  array(
      array_search('Phone',$phoneTypes) => 'Phone No',
      array_search('Mobile',$phoneTypes) => 'Mobile No',
    );
    $postal_weight = civicrm_api3('UFField', 'getsingle', array('return' => "weight", 'uf_group_id' => $summaryId['id'], 'field_name' => "postal_code"));
    $postalParams = array(
      'uf_group_id' => $summaryId['id'],
      'is_active' => '1',
      'label' => 'Postal Code Suffix',
      'field_type' => 'Contact',
      'weight' => ++$postal_weight['weight'],
      'field_name' => 'postal_code_suffix',
    );
    civicrm_api3('UFField', 'create', $postalParams);

    foreach ( $phone as $name=>$label) {
      $params = array(
        'uf_group_id' => $summaryId['id'],
        'is_active' => '1',
        'label' => $label,
        'field_type' => 'Contact',
        'location_type_id' => $location['id'],
        'field_name' => 'phone',
        'phone_type_id' => $name,
      );
      civicrm_api3('UFField', 'create', $params);
    }
    $state_weight = civicrm_api3('UFField', 'getsingle', array('return' => "weight", 'uf_group_id' => $summaryId['id'], 'field_name' => "state_province"));
    $country = array(
      'uf_group_id' => $summaryId['id'],
      'is_active' => '1',
      'label' => 'Country',
      'field_type' => 'Contact',
      'weight' => $state_weight['weight'],
      'field_name' => 'country',
    );
    civicrm_api3('UFField', 'create', $country);

    $i = 4;
    foreach ( $phone as $name=>$label) {
      $params = array(
        'uf_group_id' => $profileId['id'],
        'is_active' => '1',
        'label' => $label,
        'field_type' => 'Contact',
        'weight' => $i,
        'location_type_id' => $location['id'],
        'field_name' => 'phone',
        'phone_type_id' => $name,
      );
      $i++;
      civicrm_api3('UFField', 'create', $params);
    }
    $fields = array(
      'street_address' => 'Street Address',
      'city' => 'City',
      'postal_code' => 'Postal Code',
      'postal_code_suffix' => 'Postal Code Suffix',
      'country' => 'Country',
      'state_province' => 'State',
    );
    foreach ( $fields as $name=>$label) {
      $params = array(
        'uf_group_id' => $profileId['id'],
        'is_active' => '1',
        'label' => $label,
        'field_type' => 'Contact',
        'weight' => $i,
        'field_name' => $name,
      );
      $i++;
      civicrm_api3('UFField', 'create', $params);
    }
    return TRUE;
  }
}
