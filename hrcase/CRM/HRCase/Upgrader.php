<?php
require_once 'Upgrader/Base.php';

/**
 * Collection of upgrade steps
 */
class CRM_HRCase_Upgrader extends CRM_HRCase_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    // Enable CiviCase component
    $this->setComponentStatuses(array(
      'CiviCase' => true,
    ));

    // Execute upgrader methods during extension installation
    $revisions = $this->getRevisions();
    foreach ($revisions as $revision) {
      $methodName = 'upgrade_' . $revision;

      if (is_callable(array($this, $methodName))) {
        $this->{$methodName}();
      }
    }
  }

  /**
   * Set components as enabled or disabled. Leave any other
   * components unmodified.
   *
   *
   * @param array $components keys are component names (e.g. "CiviMail"); values are booleans
   * @throws CRM_Core_Exception
   * @return bool
   */
  public function setComponentStatuses($components) {
    $getResult = civicrm_api3('setting', 'getsingle', array(
      'domain_id' => CRM_Core_Config::domainID(),
      'return' => array('enable_components'),
    ));
    if (!is_array($getResult['enable_components'])) {
      throw new CRM_Core_Exception("Failed to determine component statuses");
    }

    // Merge $components with existing list
    $enableComponents = $getResult['enable_components'];
    foreach ($components as $component => $status) {
      if ($status) {
        $enableComponents = array_merge($enableComponents, array($component));
      } else {
        $enableComponents = array_diff($enableComponents, array($component));
      }
    }
    civicrm_api3('setting', 'create', array(
      'domain_id' => CRM_Core_Config::domainID(),
      'enable_components' => array_unique($enableComponents),
    ));
    CRM_Core_Component::flushEnabledComponents();
  }


  /**
   * Upgrader to :
   *   1- Replace (case) with (assignment) for some default activity types.
   *   2- Create some relationship types.
   * @return bool
   */
  public function upgrade_1400() {
    self::activityTypesWordReplacement();
    self::createRelationshipTypes();

    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }

  /**
   * Upgrader to :
   *  1- Remove CiviCRM default case types.
   *  2- Remove CiviCRM default relationship types.
   *  3- Remove CiviCRM default activity types.
   *
   * @return bool
   */
  public function upgrade_1401() {

    self::removeCivicrmCaseTypes();
    self::removeCivicrmRelationshipTypes();
    self::removeCivicrmActivityTypes();

    return TRUE;
  }

  /**
   * Replace (Case) and (Open Case) with (Assignment) and (Created New Assignment)
   * respectively and vise versa.
   *
   * @param boolean $restDefault If true revert activity types labels to their default
   *   ( For uninstall/disable).
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
   * Create a defined list of relationship types
   *
   */
  public static function createRelationshipTypes() {
    $toInsert = '';
    foreach(self::relationshipsTypesList() as $relationship) {
      $toInsert .= "('{$relationship['name_a_b']}','{$relationship['name_a_b']}','{$relationship['name_b_a']}','{$relationship['name_b_a']}','{$relationship['name_b_a']}','Individual','Individual',NULL,NULL,0,1),";
    }
    $toInsert = rtrim($toInsert, ',');
    $sql = "INSERT INTO `civicrm_relationship_type`
            (
            `name_a_b`,
            `label_a_b`,
            `name_b_a`,
            `label_b_a`,
            `description`,
            `contact_type_a`,
            `contact_type_b`,
            `contact_sub_type_a`,
            `contact_sub_type_b`,
            `is_reserved`,
            `is_active`)
            VALUES {$toInsert}";
    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * Remove a defined list of relationship types
   *
   */
  public static function removeRelationshipTypes() {
    $toDelete = CRM_Utils_Array::collect('name_b_a', self::relationshipsTypesList());
    $toDelete = implode("','", $toDelete);
    $sql = "DELETE FROM `civicrm_relationship_type` WHERE name_b_a IN ('{$toDelete}')";
    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * (Enable/Disable) a defined list of relationship types
   *
   * @param int $setActive 0 : disable , 1 : enable
   */
  public static function toggleRelationshipTypes($setActive) {
    $toToggle = CRM_Utils_Array::collect('name_b_a', self::relationshipsTypesList());
    $toToggle = implode("','", $toToggle);
    $sql = "UPDATE `civicrm_relationship_type` SET is_active={$setActive} WHERE name_b_a IN ('{$toToggle}')";
    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * Remove the case types which created by CiviCRM.
   *
   */
  public static function removeCivicrmCaseTypes() {
    $toDelete = self::civicrmCaseTypesList();
    $toDelete = implode("','", $toDelete);
    $sql = "DELETE FROM `civicrm_case_type` WHERE name IN  ('{$toDelete}')";
    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * Remove the relationship types which created by CiviCRM.
   *
   */
  public static function removeCivicrmRelationshipTypes() {
    $toDelete = self::civicrmRelationshipTypesList();
    $toDelete = implode("','", $toDelete);
    $sql = "DELETE FROM `civicrm_relationship_type` WHERE name_b_a IN ('{$toDelete}')";
    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * Remove the activity types which created by CiviCRM.
   *
   */
  public static function removeCivicrmActivityTypes() {
    $toDelete = self::civicrmActivityTypesList();
    $toDelete = implode("','", $toDelete);
    $sql = "DELETE FROM `civicrm_option_value` WHERE name IN  ('{$toDelete}')";
    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * A list of relationship types to be managed by this extension.
   *
   * @return array
   */
  public static function relationshipsTypesList() {
    $list = [
      ['name_a_b' => 'HR Manager is', 'name_b_a' => 'HR Manager', 'description' => 'HR Manager'],
      ['name_a_b' => 'Line Manager is', 'name_b_a' => 'Line Manager', 'description' => 'Line Manager'],
    ];

    // (Recruiting Manager) should be included only if hrrecruitment extension is disabled.
    if (!_hrcase_isExtensionEnabled('org.civicrm.hrrecruitment')) {
      $list = array_merge($list, [ ['name_a_b' => 'Recruiting Manager is', 'name_b_a' => 'Recruiting Manager', 'description' => 'Recruiting Manager'] ]);
    }
    return $list;
  }

  /**
   * A list of case types created by CiviCRM which need to be removed.
   *
   * @return array
   */
  public static function civicrmCaseTypesList() {
    return [
      'adult_day_care_referral',
      'housing_support'
    ];
  }

  /**
   * A list of relationship types created by CiviCRM which need to be removed.
   *
   * @return array
   */
  public static function civicrmRelationshipTypesList() {
    return [
      'Benefits Specialist',
      'Case Coordinator',
      'Parent of',
      'Health Services Coordinator',
      'Homeless Services Coordinator',
      'Partner of',
      'Senior Services Coordinator',
      'Sibling of',
      'Spouse of',
      'Supervisor',
      'Volunteer is',
    ];
  }

  /**
   * A list of activity types created by CiviCRM which need to be removed.
   *
   * @return array
   */
  public static function civicrmActivityTypesList() {
    return [
      'Medical evaluation',
      'Mental health evaluation',
      'Secure temporary housing',
      'Income and benefits stabilization',
      'Long-term housing plan',
      'ADC referral',
    ];
  }

}
