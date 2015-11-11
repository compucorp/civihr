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
        CRM_Core_DAO::executeQuery(
            "UPDATE civicrm_navigation SET is_active={$status} WHERE name IN ('appraisal_criteria')"
        );
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
        CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name IN ('appraisal_criteria')");
        CRM_Core_BAO_Navigation::resetNavigation();
        
        return TRUE;
    }
}
