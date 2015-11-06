<?php

class CRM_Appraisals_BAO_Appraisal extends CRM_Appraisals_DAO_Appraisal
{
    /**
     * Create a new Appraisal based on array-data
     *
     * @param array $params key-value pairs
     * @return CRM_Appraisals_DAO_Appraisal|NULL
     */
    public static function create(&$params) {
        $className = 'CRM_Appraisals_DAO_Appraisal';
        $entityName = 'Appraisal';
        $hook = empty($params['id']) ? 'create' : 'edit';
        
        if ($hook === 'create') {
            if (empty($params['appraisal_cycle_id'])) {
                throw new Exception("Please specify 'appraisal_cycle_id' value to create Appraisal.");
            }
            
            $appraisalCycle = civicrm_api3('AppraisalCycle', 'getsingle', array(
                'sequential' => 1,
                'id' => (int)$params['appraisal_cycle_id'],
            ));
            if ((int)$appraisalCycle['is_error']) {
                throw new Exception("Cannot find Appraisal Cycle with 'id' = " . (int)$params['appraisal_cycle_id'] . '.');
            }

            $params['self_appraisal_due'] = $appraisalCycle['self_appraisal_due'];
            $params['manager_appraisal_due'] = $appraisalCycle['manager_appraisal_due'];
            $params['grade_due'] = $appraisalCycle['grade_due'];
            
            if (empty($params['status_id'])) {
                $params['status_id'] = 1;
            }
        } else {
            $instance = new $className();
            $instance->id = (int)$params['id'];
            if (!$instance->find()) {
                throw new Exception("Cannot find Appraisal with 'id' = " . (int)$params['id'] . '.');
            }
            
            $instance->fetch();
            
            $dueChanged = false;
            if (!empty($params['self_appraisal_due']) && $params['self_appraisal_due'] != $instance->self_appraisal_due) {
                $dueChanged = true;
            }
            if (!empty($params['manager_appraisal_due']) && $params['manager_appraisal_due'] != $instance->manager_appraisal_due) {
                $dueChanged = true;
            }
            if (!empty($params['grade_due']) && $params['grade_due'] != $instance->grade_due) {
                $dueChanged = true;
            }
            
            if ($dueChanged) {
                $instance->due_changed = 1;
                $instance->save();
            }
        }
        
        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
        $instance = new $className();
        $instance->copyValues($params);
        $instance->save();
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        ////TODO: trigger on post: CRM_Tasksassignments_Reminder::sendReminder((int)$instance->id);
        
        return $instance;
    }
}
