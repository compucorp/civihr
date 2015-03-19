<?php

class CRM_Hrjobcontract_BAO_HRJobContract extends CRM_Hrjobcontract_DAO_HRJobContract {
    
    static $_importableFields = array();

  /**
   * Create a new HRJobContract based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRJob_DAO_HRJobContract|NULL
   *
   */
  public static function create($params) {
    $className = 'CRM_HRJobContract_DAO_HRJobContract';
    $entityName = 'HRJobContract';
    $hook = empty($params['id']) ? 'create' : 'edit';
    
    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
    
    if ((is_numeric(CRM_Utils_Array::value('is_primary', $params)) || $hook === 'create') && empty($params['import'])) {
        CRM_Hrjobcontract_DAO_HRJobContract::handlePrimary($instance, $params);
    }
    
    if (module_exists('rules')) {
        rules_invoke_event('hrjobcontract_after_create', $instance);
    }

    return $instance;
  }
  
  /**
   * Delete current HRJobContract based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRJob_DAO_HRJobContract|NULL
   *
   */
  public function delete($useWhere = false) {
      $id = $this->id;
      $result = parent::delete($useWhere);
      if ($result !== false && module_exists('rules')) {
          rules_invoke_event('hrjobcontract_after_delete', $id);
      }
  }
  
  /**
   * Get a count of records with the given property
   *
   * @param $params
   * @return int
   */
  public static function getRecordCount($params) {
    $dao = new CRM_Hrjobcontract_DAO_HRJobContract();
    $dao->copyValues($params);
    return $dao->count();
  }
  
  /**
   * combine all the importable fields from the lower levels object
   *
   * The ordering is important, since currently we do not have a weight
   * scheme. Adding weight is super important
   *
   * @param int     $contactType     contact Type
   * @param boolean $status          status is used to manipulate first title
   * @param boolean $showAll         if true returns all fields (includes disabled fields)
   * @param boolean $isProfile       if its profile mode
   * @param boolean $checkPermission if false, do not include permissioning clause (for custom data)
   *
   * @return array array of importable Fields
   * @access public
   * @static
   */
  static function &importableFields($contactType = 'Individual',
    $status          = FALSE,
    $showAll         = FALSE,
    $isProfile       = FALSE,
    $checkPermission = TRUE,
    $withMultiCustomFields = FALSE
  ) {
      
     $contactType = 'Individual';
     
     $fields = CRM_Hrjobcontract_DAO_HRJobContract::import();
     
      $tmpContactField = $contactFields = array();
      $contactFields = array( );
      
        $contactFields = CRM_Contact_BAO_Contact::importableFields($contactType, NULL);

        // Using new Dedupe rule.
        $ruleParams = array(
          'contact_type' => $contactType,
          'used'         => 'Unsupervised',
        );
        $fieldsArray = CRM_Dedupe_BAO_Rule::dedupeRuleFields($ruleParams);
        if (is_array($fieldsArray)) {
          foreach ($fieldsArray as $value) {
            $customFieldId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField',
              $value,
              'id',
              'column_name'
            );
            $value = $customFieldId ? 'custom_' . $customFieldId : $value;
            $tmpContactField[trim($value)] = CRM_Utils_Array::value(trim($value), $contactFields);
            if (!$status) {
              $title = $tmpContactField[trim($value)]['title'] . ' (match to contact)';
            }
            else {
              $title = $tmpContactField[trim($value)]['title'];
            }

            $tmpContactField[trim($value)]['title'] = $title;
          }
        }
        
      $extIdentifier = CRM_Utils_Array::value('external_identifier', $contactFields);
      if ($extIdentifier) {
        $tmpContactField['external_identifier'] = $extIdentifier;
        $tmpContactField['external_identifier']['title'] =
          CRM_Utils_Array::value('title', $extIdentifier) . ' (match to contact)';
      }

      $fields = array_merge($fields, $tmpContactField);

      self::$_importableFields = $fields;
    return self::$_importableFields;//$fields;
  }
}
