<?php

class CRM_Appraisals_BAO_Appraisal extends CRM_Appraisals_DAO_Appraisal
{
    /**
     * Create a new Appraisal based on array-data
     *
     * @param array $params key-value pairs
     * @return CRM_Appraisals_DAO_Appraisal|NULL
     */
    public static function create(&$params)
    {
        $entityName = 'Appraisal';
        $hook = empty($params['id']) ? 'create' : 'edit';
        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
        
        if (empty($params['status_id']) && $hook === 'create')
        {
            $params['status_id'] = 1;
        }
        
        $instance = parent::create($params);
        ////TODO: CRM_Tasksassignments_Reminder::sendReminder((int)$instance->id);
        
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        
        return $instance;
    }
}
