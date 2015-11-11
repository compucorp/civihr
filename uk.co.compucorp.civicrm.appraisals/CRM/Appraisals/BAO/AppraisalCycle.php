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
        
        if (empty($populateData)) {
            return false;
        }

        $queryParams = array();
        $queryFieldSet = array();
        $i = 1;
        foreach ($populateData as $field => $value) {
            $queryFieldSet[] = $field . ' = %' . $i;
            $queryParams[$i++] = array($value, 'String');
        }
        $query = 'UPDATE civicrm_appraisal SET ' . implode(', ', $queryFieldSet) . ' WHERE appraisal_cycle_id = %' . $i . ' AND due_changed = 0';
        $queryParams[$i] = array($params['id'], 'Integer');
        CRM_Core_DAO::executeQuery($query, $queryParams);

        return true;
    }
}
