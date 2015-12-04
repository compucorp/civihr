<?php

class CRM_Appraisals_Upgrader extends CRM_Appraisals_Upgrader_Base
{    
    public function install() {
        $this->executeSqlFile('sql/install.sql');

        $revisions = $this->getRevisions();
        foreach ($revisions as $revision) {
            $methodName = 'upgrade_' . $revision;
            if (is_callable(array($this, $methodName))) {
              $this->{$methodName}();
            }
        }
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
     * Install Appraisal Cycle types
     * 
     * - Annual
     * - Mid Probation
     * - End Probation
     */
    public function upgrade_0002() {

        $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'appraisal_cycle_type', 'id', 'name');
        if (!$optionGroupID) {
            $params = array(
                'name' => 'appraisal_cycle_type',
                'title' => 'Appraisal Cycle Type',
                'is_active' => 1,
                'is_reserved' => 1,
            );
            civicrm_api3('OptionGroup', 'create', $params);
            $optionsValue = array(
                1 => 'Annual',
                2 => 'Mid Probation',
                3 => 'End Probation',
            );
            foreach ($optionsValue as $key => $value) {
                $opValueParams = array(
                    'option_group_id' => 'appraisal_cycle_type',
                    'name' => $value,
                    'label' => $value,
                    'value' => $key,
                );
                civicrm_api3('OptionValue', 'create', $opValueParams);
            }
        }

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
    public function upgrade_0003() {

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
     * Install default Appraisals Criteria entries
     */
    public function upgrade_0004() {
        
        $criteria = array(
            1 => 'Below Expectations',
            2 => 'Meets Expectations',
            3 => 'Exceeds Expectations',
            4 => 'Excellent',
            5 => 'Lorem Ipsum',
        );
        
        foreach ($criteria as $value => $label) {
            $instance = new CRM_Appraisals_BAO_AppraisalCriteria();
            $instance->value = $value;
            $criteriaFound = $instance->find(true);
            if ($criteriaFound) {
                continue;
            }
            $params = array(
                'value' => $value,
                'label' => $label,
                'is_active' => 1,
            );
            CRM_Appraisals_BAO_AppraisalCriteria::create($params);
        }
        
        return TRUE;
    }
    
    /**
     * Alter Appraisal Cycle columns and add 'cycle_' prefix
     */
    public function upgrade_0005() {
        
        $queries = array(
            'ALTER TABLE `civicrm_appraisal_cycle` CHANGE `name` `cycle_name` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL',
            'ALTER TABLE `civicrm_appraisal_cycle` CHANGE `self_appraisal_due` `cycle_self_appraisal_due` DATE NULL DEFAULT NULL',
            'ALTER TABLE `civicrm_appraisal_cycle` CHANGE `manager_appraisal_due` `cycle_manager_appraisal_due` DATE NULL DEFAULT NULL',
            'ALTER TABLE `civicrm_appraisal_cycle` CHANGE `grade_due` `cycle_grade_due` DATE NULL DEFAULT NULL',
            'ALTER TABLE `civicrm_appraisal_cycle` CHANGE `type_id` `cycle_type_id` INT(10) UNSIGNED NULL DEFAULT NULL',
        );
        
        foreach ($queries as $query) {
            CRM_Core_DAO::executeQuery($query);
        }
        
        return TRUE;
    }
    
    /**
     * Remove 'appraisal_criteria' from Administer Dropdown menu
     * 
     * @return boolean
     */
    public function upgrade_0006() {
        CRM_Core_DAO::executeQuery("DELETE FROM `civicrm_navigation` WHERE name IN ('appraisal_criteria')");
        CRM_Core_BAO_Navigation::resetNavigation();
        
        return TRUE;
    }
    
    /**
     * Install Appraisals top navigation
     * 
     * @return boolean
     */
    public function upgrade_0007() {
        // Add Appraisals to the Top Navigation menu
        CRM_Core_DAO::executeQuery("DELETE FROM `civicrm_navigation` WHERE name = 'appraisals' and parent_id IS NULL");

        $weight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'weight', 'name');
        //$contactNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');
        $appraisalsNavigation = new CRM_Core_DAO_Navigation();
        $params = array (
            'domain_id'  => CRM_Core_Config::domainID(),
            'label'      => ts('Appraisals'),
            'name'       => 'appraisals',
            'url'        => null,
            'parent_id'  => null,
            'weight'     => $weight + 1,
            //'permission' => 'access Appraisals',
            'separator'  => 1,
            'is_active'  => 1
        );
        $appraisalsNavigation->copyValues($params);
        $appraisalsNavigation->save();

        if ($appraisalsNavigation->id) {
            $submenu = array(
                array(
                    'label' => ts('Appraisals Dashboard'),
                    'name' => 'appraisals_dashboard',
                    'url' => 'civicrm/appraisals/dashboard',
                ),
                array(
                    'label' => ts('Search Appraisals'),
                    'name' => 'search_appraisals',
                    'url' => 'civicrm/appraisals/search',
                ),
                array(
                    'label' => ts('Import Appraisals'),
                    'name' => 'import_appraisals',
                    'url' => 'civicrm/appraisals/import',
                ),
            );

            foreach ($submenu as $key => $item)
            {
                $item['parent_id'] = $appraisalsNavigation->id;
                $item['weight'] = $key;
                $item['is_active'] = 1;
                CRM_Core_BAO_Navigation::add($item);
            }
        }

        $administerNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Administer', 'id', 'name');
        $aministerAppraisalMenu = array(
            'label'      => ts('Appraisal grade labels'),
            'name'       => 'appraisal_grade_labels',
            'url'        => 'civicrm/appraisal_criteria',
            'permission' => 'administer CiviCRM',
            'parent_id'  => $administerNavId,
            'is_active' => 1,
        );
        CRM_Core_BAO_Navigation::add($aministerAppraisalMenu);

        CRM_Core_BAO_Navigation::resetNavigation();
        
        return TRUE;
    }
    
    /**
     * Create additional Appraisal database fields for tracking Appraisal history
     * 
     * @return boolean
     */
    public function upgrade_0008() {
        CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_appraisal` ADD `original_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `status_id`, ADD `created_date` DATETIME NULL DEFAULT NULL AFTER `original_id`, ADD `is_current` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `created_date`");
        
        return TRUE;
    }
    
    public function enable() {
        $this->setIsActive(1);
        
        return TRUE;
    }

    public function disable() {
        $this->setIsActive(0);
        
        return TRUE;
    }
    
    protected function setIsActive($status) {
        // Enable / Disable all OptionGroups:
        CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$status} WHERE name IN ('appraisal_status')");
        
        // Enable / Disable all OptionValues:
        CRM_Core_DAO::executeQuery(
            "UPDATE civicrm_option_value JOIN civicrm_option_group ON civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$status} WHERE civicrm_option_group.name IN ('appraisal_status')"
        );
        
        // Enable / Disable navigation items:
        CRM_Core_DAO::executeQuery("UPDATE `civicrm_navigation` SET is_active = {$status} WHERE name IN ('appraisals', 'appraisals_dashboard', 'search_appraisals', 'import_appraisals', 'appraisal_grade_labels')");
        CRM_Core_BAO_Navigation::resetNavigation();
        
        return TRUE;
    }
    
    /**
     * Uninstall function which removes:
     *  - OptionValues and OptionGroup for 'appraisal_status'
     *  //- Files related to Appraisals entities
     *  - civicrm_navigation where name = 'appraisal_criteria'
     * 
     * @return boolean
     */
    public function uninstall()
    {
        // Delete all OptionGroups and OptionValues:
        $result = civicrm_api3('OptionGroup', 'getsingle', array(
            'sequential' => 1,
            'name' => "appraisal_status",
        ));
        if (!empty($result['id'])) {
            CRM_Core_DAO::executeQuery("DELETE FROM civicrm_option_value WHERE option_group_id = %1", array(
                1 => array($result['id'], 'Integer'),
            ));
        }
        CRM_Core_DAO::executeQuery("DELETE FROM civicrm_option_group WHERE name IN ('appraisal_status')");
        
        ////delete Appraisal files to entities relations: TODO
        ////CRM_Core_DAO::executeQuery("DELETE FROM civicrm_entity_file WHERE entity_table LIKE 'civicrm_appraisal_%'");
        
        // Delete navigation items:
        CRM_Core_DAO::executeQuery("DELETE FROM `civicrm_navigation` WHERE name IN ('appraisals', 'appraisals_dashboard', 'search_appraisals', 'import_appraisals', 'appraisal_grade_labels')");
        CRM_Core_BAO_Navigation::resetNavigation();
        
        return TRUE;
    }
}
