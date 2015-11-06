<?php

class CRM_Appraisals_BAO_AppraisalCycle extends CRM_Appraisals_DAO_AppraisalCycle
{
    /**
     * Create a new AppraisalCycle based on array-data
     *
     * @param array $params key-value pairs
     * @return CRM_Appraisals_DAO_AppraisalCycle|NULL
     */
    public static function create(&$params) {
        $className = 'CRM_Appraisals_DAO_AppraisalCycle';
        $entityName = 'AppraisalCycle';
        $hook = empty($params['id']) ? 'create' : 'edit';
        
        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
        $instance = new $className();
        $instance->copyValues($params);
        $instance->save();
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        
        if ($hook === 'edit') {
            self::populateDueDates($params);
        }
        
        return $instance;
    }
    
    /**
     * Populate change of any due date (self_appraisal_due, manager_appraisal_due, grade_due)
     * to all Appraisals of this Appraisal Cycle which have 'due_changed' = 0.
     */
    public static function populateDueDates(array $params) {
        $populateData = array();
        
        if (empty($params['id'])) {
            throw new Exception("Cannot populate Appraisal due dates with no Appraisal Cycle 'id' given.");
        }
        
        if (isset($params['self_appraisal_due'])) {
            $populateData['self_appraisal_due'] = $params['self_appraisal_due'];
        }
        if (isset($params['manager_appraisal_due'])) {
            $populateData['manager_appraisal_due'] = $params['manager_appraisal_due'];
        }
        if (isset($params['grade_due'])) {
            $populateData['grade_due'] = $params['grade_due'];
        }
        
        $appraisal = new CRM_Appraisals_BAO_Appraisal();
        $appraisal->appraisal_cycle_id = $params['id'];
        $appraisal->find();
        while ($appraisal->fetch()) {
            echo 'a';
        }
        
        
    }
}
