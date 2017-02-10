<?php

require_once 'Upgrader/Base.php';

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
  }

  public function enable() {
    self::toggleRelationshipTypes(1);
    self::toggleDefaultCaseTypes(0);
  }

  public function disable() {
    self::toggleRelationshipTypes(0);
    self::toggleDefaultCaseTypes(1);
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
    self::toggleDefaultCaseTypes(0);

    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
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
   * (Enables/Disables) CiviCRM default case types
   *
   * @param int $setActive
   *   0 : disable , 1 : enable
   */
  public static function toggleDefaultCaseTypes($setActive) {
    $defaultCaseTypes = [
      'adult_day_care_referral',
      'housing_support',
    ];
    foreach($defaultCaseTypes as $caseType) {
      // chained API call to activate/disable the case type
      civicrm_api3('CaseType', 'get', [
        'name' => $caseType,
        'api.CaseType.create' => ['id' => '$value.id', 'is_active' => $setActive],
      ]);
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

}
