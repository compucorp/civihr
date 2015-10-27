<?php

class CRM_Appraisals_BAO_AppraisalCycle extends CRM_Appraisals_DAO_AppraisalCycle
{
    /**
     * Create a new AppraisalCycle based on array-data
     *
     * @param array $params key-value pairs
     * @return CRM_Appraisals_DAO_AppraisalCycle|NULL
     */
    public static function create(&$params)
    {
        $entityName = 'AppraisalCycle';
        $hook = empty($params['id']) ? 'create' : 'edit';
        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
        
        $instance = parent::create($params);
        
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        
        return $instance;
    }
}
