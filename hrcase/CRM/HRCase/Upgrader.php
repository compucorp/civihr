<?php

require_once 'Upgrader/Base.php';
require_once 'DefaultCaseAndActivityTypes.php';

use CRM_HRCase_DefaultCaseAndActivityTypes as DefaultCaseAndActivityTypes;

/**
 * Collection of upgrade steps
 */
class CRM_HRCase_Upgrader extends CRM_HRCase_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  public function install() {
    // Enable CiviCase component
    $this->setComponentStatuses([
      'CiviCase' => true,
    ]);

    // Execute upgrader methods during extension installation
    $revisions = $this->getRevisions();
    foreach ($revisions as $revision) {
      $methodName = 'upgrade_' . $revision;
      if (is_callable([$this, $methodName])) {
        $this->{$methodName}();
      }
    }
  }

  public function uninstall() {
    self::activityTypesWordReplacement(true);
    self::removeRelationshipTypes();
    self::removeCaseTypesWithData(array_column(DefaultCaseAndActivityTypes::getDefaultCaseTypes(), 'name'));
    $this->removeActivityTypesList(DefaultCaseAndActivityTypes::getDefaultActivityTypes(), 'CiviTask');
  }

  public function enable() {
    self::toggleRelationshipTypes(1);
    self::toggleCaseTypes(array_column(DefaultCaseAndActivityTypes::getDefaultCaseTypes(), 'name'), 1);
    self::toggleCaseTypes(DefaultCaseAndActivityTypes::getDefaultCiviCRMCaseTypes(), 0);
    self::toggleActivityTypes(DefaultCaseAndActivityTypes::getDefaultActivityTypes(), 1);
    $this->changeActivityTypeComponent('Open Case', 'CiviCase', 'CiviTask');
  }

  public function disable() {
    self::toggleRelationshipTypes(0);
    self::toggleCaseTypes(array_column(DefaultCaseAndActivityTypes::getDefaultCaseTypes(), 'name'), 0);
    self::toggleCaseTypes(DefaultCaseAndActivityTypes::getDefaultCiviCRMCaseTypes(), 1);
    self::toggleActivityTypes(DefaultCaseAndActivityTypes::getDefaultActivityTypes(), 0);
    $this->changeActivityTypeComponent('Open Case', 'CiviTask', 'CiviCase');
  }

  /**
   * Set components as enabled or disabled. Leave any other
   * components unmodified.
   *
   * @param array $components
   *   keys are component names (e.g. "CiviMail"); values are booleans
   *
   * @throws CRM_Core_Exception
   *
   * @return bool
   */
  public function setComponentStatuses($components) {
    $getResult = civicrm_api3('setting', 'getsingle', [
      'domain_id' => CRM_Core_Config::domainID(),
      'return' => ['enable_components'],
    ]);
    if (!is_array($getResult['enable_components'])) {
      throw new CRM_Core_Exception("Failed to determine component statuses");
    }
    // Merge $components with existing list
    $enableComponents = $getResult['enable_components'];
    foreach ($components as $component => $status) {
      if ($status) {
        $enableComponents = array_merge($enableComponents, [$component]);
      } else {
        $enableComponents = array_diff($enableComponents, [$component]);
      }
    }
    civicrm_api3('setting', 'create', [
      'domain_id' => CRM_Core_Config::domainID(),
      'enable_components' => array_unique($enableComponents),
    ]);
    CRM_Core_Component::flushEnabledComponents();
  }

  /**
   * Upgrader to :
   *   1- Replace (case) keyword with (assignment) keyword for civicrm default activity types.
   *   2- Create default relationship types.
   *   2- Disable default CiviCRM case types.
   *
   * @return bool
   */
  public function upgrade_1400() {
    self::activityTypesWordReplacement();
    self::createRelationshipTypes();

    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }

  /**
   * Upgrader to clean up/create activity types and managed entities
   * for Task & Assignments extension (CiviTask Component).
   *
   * @return bool
   */
  public function upgrade_1402() {
    $defaultCaseTypes = DefaultCaseAndActivityTypes::getDefaultCaseTypes();
    $defaultActivityTypes = DefaultCaseAndActivityTypes::getDefaultActivityTypes();

    $this->up1402_removedUnusedManagedEntities(array_column($defaultCaseTypes, 'name'), $defaultActivityTypes);
    $this->up1402_removeUnusedCaseTypes();
    //Removes CiviCase activity types which should belong to CiviTask component
    $this->removeActivityTypesList($defaultActivityTypes, 'CiviCase');
    $this->createOrUpdateDefaultCaseTypes($defaultCaseTypes);
    $this->createActivityTypes($defaultActivityTypes);
    $this->changeActivityTypeComponent('Open Case', 'CiviCase', 'CiviTask');

    return TRUE;
  }

  /**
   * Resets all default case types discarding any customization to match new
   * activity workflow
   */
  public function upgrade_1429() {
    $defaultActivityTypes = DefaultCaseAndActivityTypes::getDefaultActivityTypes();
    $defaultCaseTypes = DefaultCaseAndActivityTypes::getDefaultCaseTypes();
    $this->createActivityTypes($defaultActivityTypes);
    $this->createOrUpdateDefaultCaseTypes($defaultCaseTypes);

    return TRUE;
  }

  /**
   * Upgrader to add new default task type: 'Other Task'
   */
  public function upgrade_1430() {
    $result = civicrm_api3('OptionValue', 'get', [
      'component_id' => "CiviTask",
      'name' => "Other Task",
      'option_group_id' => "activity_type",
    ]);

    if ($result['is_error'] || $result['count'] != 0) {
      return true;
    }

    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => "activity_type",
      'component_id' => "CiviTask",
      'label' => "Other Task",
      'name' => "Other Task",
    ]);

    return true;
  }

  /**
   * Replaces (Case) keyword and (Open Case) keyword with (Assignment) keyword
   * and (Created New Assignment) keyword respectively and vise versa for
   * civicrm default activity types labels when installing/uninstalling the extension.
   *
   * @param boolean $restDefault
   *   If true revert activity types labels to their default
   *  ( For uninstall/disable).
   */
  public static function activityTypesWordReplacement($restDefault = false) {
    $replace = 'Assignment';
    $replaceWith = 'Case';
    $replaceOpenCase = 'Created New Assignment';
    $replaceOpenCaseWith = 'Open Case';
    // Flip values for install/enable
    if (!$restDefault) {
      $tmp = $replace;
      $replace = $replaceWith;
      $replaceWith = $tmp;
      $tmp = $replaceOpenCase;
      $replaceOpenCase = $replaceOpenCaseWith;
      $replaceOpenCaseWith = $tmp;
    }
    // Replace case activity types
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'activity_type', 'id', 'name');
    $sql = "UPDATE civicrm_option_value SET label= replace(label, '{$replace}', '{$replaceWith}') WHERE label like '%{$replace}%' and option_group_id={$optionGroupID} and label <> '{$replaceOpenCase}'";
    CRM_Core_DAO::executeQuery($sql);
    // replace (open case) activity type  which is a special case and should be replaced differently
    $sql = "UPDATE civicrm_option_value SET label= replace(label,'{$replaceOpenCase}', '{$replaceOpenCaseWith}') WHERE label = '{$replaceOpenCase}' and option_group_id={$optionGroupID}";
    CRM_Core_DAO::executeQuery($sql);
  }
  /**
   * Creates default relationship types
   */
  public static function createRelationshipTypes() {
    foreach(self::defaultRelationshipsTypes() as $relationshipType) {
      civicrm_api3('RelationshipType', 'create', [
        'name_a_b' => $relationshipType['name_a_b'],
        'label_a_b' => $relationshipType['name_b_a'],
        'name_b_a' => $relationshipType['name_b_a'],
        'label_b_a' => $relationshipType['name_b_a'],
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
        'is_reserved' => 0,
        'is_active' => 1,
      ]);
    }
  }
  /**
   * Removes default relationship types
   */
  public static function removeRelationshipTypes() {
    foreach(self::defaultRelationshipsTypes() as $relationshipType) {
      // chained API call to delete the relationship type
      civicrm_api3('RelationshipType', 'get', [
        'name_b_a' => $relationshipType['name_b_a'],
        'api.RelationshipType.delete' => ['id' => '$value.id'],
      ]);
    }
  }
  /**
   * (Enables/Disables) a defined list of relationship types
   *
   * @param int $setActive
   *   0 : disable , 1 : enable
   */
  public static function toggleRelationshipTypes($setActive) {
    foreach(self::defaultRelationshipsTypes() as $relationshipType) {
      // chained API call to activate/disable the relationship type
      civicrm_api3('RelationshipType', 'get', [
        'name_b_a' => $relationshipType['name_b_a'],
        'api.RelationshipType.create' => ['id' => '$value.id', 'name_a_b' => '$value.name_a_b', 'name_b_a' => '$value.name_b_a', 'is_active' => $setActive],
      ]);
    }
  }
  /**
   * A list of relationship types to be managed by this extension.
   *
   * @return array
   */
  public static function defaultRelationshipsTypes() {
    $list = [
      ['name_a_b' => 'HR Manager is', 'name_b_a' => 'HR Manager', 'description' => 'HR Manager'],
      ['name_a_b' => 'Line Manager is', 'name_b_a' => 'Line Manager', 'description' => 'Line Manager'],
    ];
    // (Recruiting Manager) should be included only if hrrecruitment extension is disabled.
    if (!self::isExtensionEnabled('org.civicrm.hrrecruitment')) {
      $list = array_merge($list, [ ['name_a_b' => 'Recruiting Manager is', 'name_b_a' => 'Recruiting Manager', 'description' => 'Recruiting Manager'] ]);
    }
    return $list;
  }

  /**
   * (Enables/Disables) case types
   *
   * @param array $caseTypes
   *   Case types list to disable/enable
   * @param int $status
   *   0 : disable , 1 : enable
   */
  public static function toggleCaseTypes($caseTypes, $status) {
    foreach ($caseTypes as $caseType) {
      civicrm_api3('CaseType', 'get', [
        'name' => $caseType,
        'api.CaseType.create' => ['id' => '$value.id', 'is_active' => $status],
      ]);
    }
  }

  /**
   * (Enables/Disables) activity types
   *
   * @param array $activityTypes
   *   Activity types list to disable/enable
   * @param int $status
   *   0 : disable , 1 : enable
   */
  public static function toggleActivityTypes($activityTypes, $status) {
    foreach ($activityTypes as $componentName => $componentActivities) {
      foreach ($componentActivities as $activityType) {
        civicrm_api3('OptionValue', 'get', [
          'name' => $activityType,
          'component_id' => $componentName,
          'api.OptionValue.create' => ['id' => '$value.id', 'is_active' => $status],
        ]);
      }
    }
  }

  /**
   * Checks if tasks and assignments extension is installed or enabled
   *
   * @param String $key
   *   Extension unique key
   *
   * @return boolean
   */
  public static function isExtensionEnabled($key)  {
    $isEnabled = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_Extension',
      $key,
      'is_active',
      'full_name'
    );
    return  !empty($isEnabled) ? true : false;
  }

  /**
   * Removes a list of defined activity types for a given component
   *
   * @param array $activityTypes
   *   A list of activity types names to remove
   * @param int $componentName
   *   (e.g : CiviCase, CiviTask .. etc)
   */
  private function removeActivityTypesList($activityTypes, $componentName) {
    $allActivityTypes = [];
    foreach ($activityTypes as $componentActivities) {
      $allActivityTypes = array_merge($allActivityTypes, $componentActivities);
    }

    civicrm_api3('OptionValue', 'get', [
          'name' => ['IN' => $allActivityTypes],
          'component_id' => $componentName,
          'option_group_id' => 'activity_type',
          'api.OptionValue.delete' => ['id' => '$value.id'],
    ]);
  }

  /**
   * Removes a list of unused ( or unneeded ) case and activity types
   * managed records
   *
   * @param array $caseTypes
   * @param array $activityTypes
   */
  private function up1402_removedUnusedManagedEntities($caseTypes, $activityTypes) {
    $entitiesToRemove['civicase:act:Background Check'] = 'OptionValue';

    $allActivityTypes = [];
    foreach ($activityTypes as $componentActivities) {
      $allActivityTypes = array_merge($allActivityTypes, $componentActivities);
    }

    foreach (array_merge($caseTypes, ['Appraisal', 'Probation']) as $caseType) {
      $entitiesToRemove[$caseType] = 'CaseType';
    }

    foreach ($allActivityTypes as $extension) {
      foreach ($extension as $activityType) {
        $entitiesToRemove['civitask:act:' . $activityType] = 'OptionValue';
      }
    }

    foreach ($entitiesToRemove as $name => $entity) {
      $extension = 'org.civicrm.hrcase';
      if ($name == 'civicase:act:Background Check') {
        $extension = 'civicrm';
      }

      $this->removeManagedEntityRecord($extension, $name, $entity);
    }
  }

  /**
   * Removes unused ( or unneeded ) case types
   */
  private function up1402_removeUnusedCaseTypes() {
    // Remove (Appraisal) & (Probation) case types
    $caseTypesToRemove = ['Appraisal', 'Probation'];

    foreach($caseTypesToRemove as $caseType) {
      $this->removeUnusedCaseType($caseType);
    }
  }

  /**
   * Creates/Updates default case types to be shipped
   * with this extension
   *
   * WARNING: Resets activity types for an assignment, discarding customization
   *
   * @param array $defaultCaseTypes
   *   A list of case types with their related data
   */
  private function createOrUpdateDefaultCaseTypes($defaultCaseTypes) {
    foreach ($defaultCaseTypes as $caseType) {
      $type = civicrm_api3('CaseType', 'get', [
        'sequential' => 1,
        'name' => $caseType['name'],
        'limit' => 1
      ]);

      if (!empty($type['id'])) {
        $caseType['id'] = $type['id'];
      }

      civicrm_api3('CaseType', 'create', $caseType);
    }
  }

  /**
   * Creates default activity types to be shipped
   * with this extension if they are not exits
   *
   * @param array $defaultActivityTypes
   *   A list of activity types grouped by component name
   */
  private function createActivityTypes($defaultActivityTypes) {
    foreach ($defaultActivityTypes as $componentName => $componentActivities) {
      foreach ($componentActivities as $activityType) {
        $type = civicrm_api3('OptionValue', 'get', [
          'sequential' => 1,
          'name' => $activityType,
          'option_group_id' => 'activity_type',
          'component_id' => $componentName,
        ]);

        if (empty($type['id'])) {
          civicrm_api3('OptionValue', 'create', [
            'sequential' => 1,
            'option_group_id' => 'activity_type',
            'label' => $activityType,
            'name' => $activityType,
            'component_id' => $componentName,
          ]);
        }
      }
    }
  }

  /**
   * Changes Activity type component
   *
   * @param string $activityTypeName
   *   Activity type name
   * @param string $currentComponent
   *   Current activity type component
   * @param string $newComponent
   *  New activity type component
   */
  private function changeActivityTypeComponent($activityTypeName, $currentComponent, $newComponent) {
    civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'activity_type',
      'name' => $activityTypeName,
      'component_id' => $currentComponent,
      'api.OptionValue.create' => ['id' => '$value.id', 'component_id' => $newComponent],
    ]);
  }

  /**
   * Removes Managed entity record, given its name and type
   *
   * @param string $extensionKey
   *   The extension Key/Name which created the managed entity
   * @param string $name
   *   The name of the managed entity record to remove
   * @param string $type
   *   The type of managed entity record to remove ( e.g : OptionValue, Contact ..etc )
   */
  private function removeManagedEntityRecord($extensionKey, $name, $type) {
    $dao = new CRM_Core_DAO_Managed();
    $dao->name = $name;
    $dao->module = $extensionKey;
    $dao->entity_type = $type;

    if ($dao->find(TRUE)) {
      $dao->delete();
    }
  }

  /**
   * Removes case type if no cases are attached to it
   *
   * @param string $caseTypeName
   */
  private function removeUnusedCaseType($caseTypeName) {
    try {
      $isCaseAttached = civicrm_api3('Case', 'get', [
        'sequential' => 1,
        'case_type_id' => $caseTypeName,
        'options' => ['limit' => 1],
      ]);

      if (empty($isCaseAttached['values'])) {
        civicrm_api3('CaseType', 'get', [
          'sequential' => 1,
          'name' => $caseTypeName,
          'api.CaseType.delete' => ['id' => '$value.id'],
        ]);
      }
    } catch (CiviCRM_API3_Exception $e) {
      // do nothing
    }
  }

  /**
   * Removes a list of case types along with its related cases
   *
   * @param array $caseTypes
   *   A list of case type names
   */
  private function removeCaseTypesWithData($caseTypes) {
    foreach ($caseTypes as $caseType) {
      $caseTypeRow = civicrm_api3('CaseType', 'get', [
        'sequential' => 1,
        'name' => $caseType,
        'limit' => 1,
      ]);

      if (!empty($caseTypeRow['id'])) {
        // Removing all cases related to this case type , we using
        // DAO instead of API because API deletion does not supports
        // batch delete and this should be much faster
        $caseDAO = new CRM_Case_DAO_Case();
        $caseDAO->case_type_id = $caseTypeRow['id'];
        $caseDAO->delete();

        // Now delete the case type
        civicrm_api3('caseType', 'delete', [
          'id' => $caseTypeRow['id'],
        ]);
      }
    }
  }

}
