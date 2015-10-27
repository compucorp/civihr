<?php

class CRM_Appraisals_Upgrader extends CRM_Appraisals_Upgrader_Base
{    
    public function install() {

      // $this->executeCustomDataFile('xml/customdata.xml');
      $this->executeSqlFile('sql/install.sql');

      /*$revisions = $this->getRevisions();
      foreach ($revisions as $revision) {
          $methodName = 'upgrade_' . $revision;
          if (is_callable(array($this, $methodName))) {
            $this->{$methodName}();
          }
      }*/
    }
    
    /**
     * Add Appraisal Criteria to the CiviCRM Administration menu
     */
    public function upgrade_0001() {
        CRM_Core_DAO::executeQuery("DELETE FROM `civicrm_navigation` WHERE name IN ('appraisal_criteria')");

        $administerNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Dropdown Options', 'id', 'name');

        $jobContractOptionsMenuTree = array(
          array(
            'label'      => ts('Appraisal Criteria'),
            'name'       => 'appraisal_criteria',
            'url'        => 'civicrm/appraisal_criteria',
            'permission' => 'administer CiviCRM',
            'parent_id'  => $administerNavId,
          ),
        );

        foreach ($jobContractOptionsMenuTree as $key => $menuItems) {
          $menuItems['is_active'] = 1;
          CRM_Core_BAO_Navigation::add($menuItems);
        }

        CRM_Core_BAO_Navigation::resetNavigation();
        
        return TRUE;
    }
    
    /*
     * Install Appraisal statuses
     * 
     * - Awaiting self appraisal (When newly created)
     * - Awaiting manager appraisal
     * - Awaiting grade
     * - Awaiting HR approval
     * - Complete
     */
    public function upgrade_0002() {

          $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'appraisal_status', 'id', 'name');
          if (!$optionGroupID) {
              $params = array(
                'name' => 'appraisal_status',
                'title' => 'Appraisal Status',
                'is_active' => 1,
                'is_reserved' => 1,
              );
              civicrm_api3('OptionGroup', 'create', $params);
              $optionsValue = array(
                  1 => 'Awaiting self appraisal',
                  2 => 'Awaiting manager appraisal',
                  3 => 'Awaiting grade',
                  4 => 'Awaiting HR approval',
                  5 => 'Complete',
              );
              foreach ($optionsValue as $key => $value) {
                $opValueParams = array(
                  'option_group_id' => 'appraisal_status',
                  'name' => $value,
                  'label' => $value,
                  'value' => $key,
                );
                civicrm_api3('OptionValue', 'create', $opValueParams);
              }
          }

        return TRUE;
    }

    /**
     * Add "Appraisal Cycle" as a new 'tag_used_for' option value.
     */
    public function upgrade_0003() {
        $optionGroupResult = civicrm_api3('OptionGroup', 'getsingle', array(
          'sequential' => 1,
          'name' => "tag_used_for",
        ));
        if ($optionGroupResult['id']) {
          $optionValueResult = civicrm_api3('OptionValue', 'create', array(
            'sequential' => 1,
            'option_group_id' => $optionGroupResult['id'],
            'label' => "Appraisal Cycles",
            'value' => "civicrm_appraisal_cycle",
            'name' => "Appraisal Cycles",
            'grouping' => 0,
            'is_optgroup' => 0,
            'is_reserved' => 0,
            'is_active' => 1,
          ));
        }

        return TRUE;
    }
    
    /**
     * Installing main groups of Appraisal Cycle tags.
     */
    public function upgrade_0004() {
        $tags = array(
            "department", "level", "location", "region", // "role"(?), "status"(?)
        );
        foreach ($tags as $tag) {
            $result = civicrm_api3('Tag', 'create', array(
              'sequential' => 1,
              'name' => $tag,
              'parent_id' => "",
              'used_for' => "civicrm_appraisal_cycle",
            ));
        }

        return TRUE;
    }

    public function enable() {
        return TRUE;
    }

    public function disable() {
        return TRUE;
    }
    
    /**
     * Uninstall function which removes:
     *  - civicrm_entity_tag
     *  - civicrm_tag (where 'used_for' = 'civicrm_appraisal_cycle')
     *  - OptionValues where option_group = 'tag_used_for'
     *  - OptionValues and OptionGroup for 'appraisal_status'
     *  - civicrm_navigation where name = 'appraisal_criteria'
     * @return boolean
     */
    public function uninstall()
    {
        // Remove civicrm_entity_tag:
        // TODO
        
        // Remove civicrm_tag (where 'used_for' = 'civicrm_appraisal_cycle'):
        // TODO
        
        // Remove OptionValues where option_group = 'tag_used_for':
        // TODO
        
        // Remove OptionValues and OptionGroup for 'appraisal_status':
        // TODO
        
        // Remove civicrm_navigation where name = 'appraisal_criteria':
        // TODO
        
        return TRUE;
    }
}
