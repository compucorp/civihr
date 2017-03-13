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
class CRM_HRUI_Upgrader extends CRM_HRUI_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  public function install() {
    $revisions = $this->getRevisions();

    foreach ($revisions as $revision) {
      $methodName = 'upgrade_' . $revision;

      if (is_callable(array($this, $methodName))) {
        $this->{$methodName}();
      }
    }
  }

  /**
   * Create (Import Custom Fields) menu items
   *
   * @return bool
   */
  public function upgrade_4700() {
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name = 'jobImport'");

    $contactsNavID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');
    $importContactWeight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Import Contacts', 'weight', 'name');
    $params = [
      'name' => 'import_custom_fields',
      'label' => ts('Import Custom Fields'),
      'url' => 'civicrm/import/custom?reset=1',
      'parent_id' => $contactsNavID,
      'is_active' => TRUE,
      'weight' => $importContactWeight,
      'permission' => 'access CiviCRM',
      'domain_id' => CRM_Core_Config::domainID(),
    ];

    $navigation = new CRM_Core_DAO_Navigation();
    $navigation->copyValues($params);
    $navigation->save();

    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }
  
  /**
   * Adds Custom Inline Data group for fields to be shown within contact details
   * and a NI / SSN field alphanumeric field for that group.
   */
  public function upgrade_4701() {
    // Add Inline Custom Group
    $customGroupResult = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'name' => 'Inline_Custom_Data'
    ]);

    if ($customGroupResult['count'] < 1) {
      $groupData = [
        'sequential' => 1,
        'title' => 'Inline Custom Data',
        'name' => 'Inline_Custom_Data',
        'extends' => ['0' => 'Individual'],
        'weight' => 21,
        'collapse_display' => 1,
        'style' => 'Inline',
        'is_active' => 1
      ];
      $customGroupResult = civicrm_api3('CustomGroup', 'create', $groupData);
    }
    $inlineCustomGroup = array_shift($customGroupResult['values']);
    
    // Add NI/SSN Field
    $fieldData = [
      'sequential' => 1,
      'custom_group_id' => $inlineCustomGroup['id'],
      'name' => 'NI_SSN',
      'label' => 'NI / SSN',
      'html_type' => 'Text',
      'data_type' => 'String',
      'weight' => 1,
      'is_required' => 0,
      'is_searchable' => 1,
      'is_active' => 1
    ];
    $createResult = civicrm_api3('CustomField', 'create', $fieldData);
    $niSSNField = array_shift($createResult['values']);

    $identTableName = $this->getIdentTableName();
    $identFieldName = $this->getIdentFieldName();

    $query = "
      UPDATE {$inlineCustomGroup['table_name']}, $identTableName
         SET {$niSSNField['column_name']} = $identFieldName
       WHERE {$inlineCustomGroup['table_name']}.entity_id = $identTableName.entity_id
         AND is_government = 1
    ";
    CRM_Core_DAO::executeQuery($query);

    return true;
  }

  private function getIdentTableName() {
    $customGroupResult = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'name' => 'Identify'
    ]);

    return $customGroupResult['values'][0]['table_name'];
  }
 
  private function getIdentFieldName() {
    $customFieldResult = civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'name' => 'Number'
    ]);

    return $customFieldResult['values'][0]['column_name'];
  }
}
