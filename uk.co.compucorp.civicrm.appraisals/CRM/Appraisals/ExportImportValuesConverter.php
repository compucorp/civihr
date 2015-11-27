<?php

class CRM_Appraisals_ExportImportValuesConverter
{
    static private $_singleton = NULL;
    
    protected $_appraisalCycleType = array();
    protected $_appraisalStatus = array();
    protected $_appraisalCriteria = array();
    
    private function __construct()
    {
        $this->_initialize();
    }
    
    /**
     * singleton function used to manage this object
     *
     * @return CRM_Appraisals_ExportImportValuesConverter
     * @static
     */
    static function &singleton()
    {
        if (self::$_singleton === NULL)
        {
            self::$_singleton = new self;
        }
        return self::$_singleton;
    }
    
    protected function _initialize()
    {
        $this->_appraisalCycleType = CRM_Core_OptionGroup::values('appraisal_cycle_type');
        $this->_appraisalStatus = CRM_Core_OptionGroup::values('appraisal_status');
        
        $appraisalCriteria = civicrm_api3('AppraisalCriteria', 'get', array(
            'sequential' => 1,
            'is_active' => 1,
        ));
        foreach ($appraisalCriteria['values'] as $row)
        {
            $this->_appraisalCriteria[$row['value']] = $row['label'];
        }
    }
    
    public function export($tableName, $fieldName, $value)
    {
        $functionName = $tableName . '_' . $fieldName . '_export';
        if (is_callable(array($this, $functionName)))
        {
            $value = self::$functionName($value);
        }
        return $value;
    }
    
    public function import($tableName, $fieldName, $value)
    {
        $functionName = $tableName . '_' . $fieldName . '_import';
        if (is_callable(array($this, $functionName)))
        {
            $value = self::$functionName($value);
        }
        return $value;
    }
    
    public function civicrm_appraisal_cycle_cycle_type_id_export($value)
    {
        return $value . ' (' . $this->_appraisalCycleType[$value] . ')';
    }
    public function civicrm_appraisal_cycle_cycle_type_id_import($value)
    {
        $keys = preg_split("/[\(*\)]/i", $value);
        if (empty($keys[0]) || empty($keys[1]))
        {
            return $value;
        }
        // $keys[0] - appraisal cycle type 'value'
        // $keys[1] - appraisal cycle type 'label'
        return (int)$keys[0];
    }
    
    public function civicrm_appraisal_status_id_export($value)
    {
        return $value . ' (' . $this->_appraisalStatus[$value] . ')';
    }
    public function civicrm_appraisal_status_id_import($value)
    {
        $keys = preg_split("/[\(*\)]/i", $value);
        if (empty($keys[0]) || empty($keys[1]))
        {
            return $value;
        }
        // $keys[0] - appraisal status 'value'
        // $keys[1] - appraisal status 'label'
        return (int)$keys[0];
    }
    
    public function civicrm_appraisal_grade_export($value)
    {
        return $value . ' (' . $this->_appraisalCriteria[$value] . ')';
    }
    public function civicrm_appraisal_grade_import($value)
    {
        $keys = preg_split("/[\(*\)]/i", $value);
        if (empty($keys[0]) || empty($keys[1]))
        {
            return $value;
        }
        // $keys[0] - appraisal criteria 'value'
        // $keys[1] - appraisal criteria 'label'
        return (int)$keys[0];
    }
    
/*    public function contract_is_primary_export($value)
    {
        return (int)$value ? 'Yes' : 'No';
    }
    public function contract_is_primary_import($value)
    {
        return strtolower($value) === 'yes' ? 1 : 0;
    }*/
    
    public function getContactByExternalIdentifier($externalIdentifier)
    {
        $contactId = null;
        
        if (!empty($externalIdentifier)) {
          $checkCid = new CRM_Contact_DAO_Contact();
          $checkCid->external_identifier = $externalIdentifier;
          $checkCid->find(TRUE);
          if (!empty($checkCid->id)) {
              $contactId = $checkCid->id;
          }
        }
        
        return $contactId;
    }
    
    public function getContactByEmail($email)
    {
        $contactId = null;
        
        if (!empty($email))
        {
            $checkEmail = new CRM_Core_BAO_Email();
            $checkEmail->email = $email;
            $checkEmail->find(TRUE);
            if (!empty($checkEmail->contact_id))
            {
                $contactId = $checkEmail->contact_id;
            }
        }
        
        return $contactId;
    }
    
    public function getContactById($id)
    {
        $contactId = null;
        // id:
        if (!empty($id)) {
          $checkId = new CRM_Contact_DAO_Contact();
          $checkId->id = $id;
          $checkId->find(TRUE);
          if (!empty($checkId->id)) {
              $contactId = $checkId->id;
          }
        }
        
        return $contactId;
    }
}
