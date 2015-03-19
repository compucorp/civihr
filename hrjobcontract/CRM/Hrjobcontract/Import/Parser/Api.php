<?php
class CRM_Hrjobcontract_Import_Parser_Api extends CRM_Hrjobcontract_Import_Parser_BaseClass {
  protected $_entity;
  protected $_fields = array();
  protected $_requiredFields = array();
  protected $_dateFields = array();
  protected $_entityFields = array();
  protected $_allFields = array();
  protected $_jobContractIds = array();
  protected $_previousRevision = array();
  protected $_revisionIds = array();
  protected $_revisionEntityMap = array();
  protected $_jobcontractIdIncremental = 1;
  protected $_revisionIdIncremental = 1;

  /**
   * Params for the current entity being prepared for the api
   * @var array
   */
  protected $_params = array();
  
  function setFields() {
      $this->_fields = array();
      $allFields = array();
      $entityFields = array();
      
      foreach ($this->_entity as $entity) {
        $entityName = "CRM_Hrjobcontract_BAO_{$entity}";
          $entityFields[$entity] = $entityName::importableFields($entity, NULL);
          foreach ($entityFields[$entity] as $key => $field) {
            if (!empty($field['required'])) {
              $this->_requiredFields[$entity] = $field;
            }
            // date is 4 & time is 8. Together they make 12 - in theory a binary operator makes sense here but as it's not a common pattern it doesn't seem worth the confusion
            if (CRM_Utils_Array::value('type', $field) == 12
               || CRM_Utils_Array::value('type', $field) == 4) {
              $this->_dateFields[$entity] = $key;
            }
          }
          $allFields = array_merge($entityFields[$entity], $allFields);
      }
    $this->_entityFields = $entityFields;
    $this->_allFields = $allFields;

    $this->_fields = array_merge(array('do_not_import' => array('title' => ts('- do not import -'))), $allFields);
  }

  /**
   * The summary function is a magic & mystical function
   * it makes a call to setActiveFieldValues - without which import won't work
   * function
   * @param array $values the array of values belonging to this line
   *
   * @return boolean      the result of this processing
   * It is called from both the preview & the import actions
   * (non-PHPdoc)
   * @see CRM_Hrjobcontract_Import_Parser_BaseClass::summary()
   */
  function summary(&$values) {
    $erroneousField = NULL;
    $response      = $this->setActiveFieldValues($values, $erroneousField);
    $errorRequired = FALSE;
    $missingField = '';
    $errorMessage = NULL;
    $errorMessages = array();
    
    return CRM_Import_Parser::VALID;///TODO!

    foreach ($this->_entity as $entity) {
      $this->_params = $this->getActiveFieldParams();
      foreach ($this->_requiredFields as $requiredFieldKey => $requiredFieldVal) {
          // TODO: code below is TEMPORARY!
          if ($requiredFieldVal === 'jobcontract_id') {
              continue;
          }
          
        if (empty($this->_params[$requiredFieldVal])) {
          $errorRequired = TRUE;
          $missingField .= ' ' . $requiredFieldVal; //// TODO: BUG? previously: $requiredField;
          CRM_Contact_Import_Parser_Contact::addToErrorMsg($entity, $requiredFieldVal);
        }
      }
      //checking error in core data
      $this->isErrorInCoreData($this->_params, $errorMessage);
      if ($errorMessage) {
        $errorMessages[] = $errorMessage;
        $tempMsg = "Invalid value for field(s) : $errorMessage";
        CRM_Contact_Import_Parser_Contact::addToErrorMsg($entity, $errorMessage);
      }
    }

    if ($errorRequired) {
      array_unshift($values, ts('Missing required field(s) :') . $missingField);
      return CRM_Import_Parser::ERROR;
    }

    if ($errorMessage) {
      $tempMsg = "Invalid value for field(s) : $errorMessage";
      array_unshift($values, $tempMsg);
      $errorMessage = NULL;
      return CRM_Import_Parser::ERROR;
    }
    return CRM_Import_Parser::VALID;
  }

  /**
   * handle the values in import mode
   *
   * @param int $onDuplicate the code for what action to take on duplicates
   * @param array $values the array of values belonging to this line
   *
   * @return boolean      the result of this processing
   * @access public
   */
  function import($onDuplicate, &$values) {
    $entityNames = array(
        'details',
        'hour',
        'health',
        'leave',
        'pay',
        'pension',
        'role',
    );
    $ei = CRM_Hrjobcontract_ExportImportValuesConverter::singleton();
    $response = $this->summary($values);
    $this->formatDateParams();
    $this->_params['skipRecentView'] = TRUE;
    $this->_params['check_permissions'] = TRUE;
    
    $params = $this->getActiveFieldParams();
    
    $formatValues = array();
    foreach ($params as $key => $field) {
      if ($field == NULL || $field === '') {
        continue;
      }

      $formatValues[$key] = $field;
    }
    
    $importedJobContractId = null;
    
    if (!empty($params['jobcontract_id'])) {
        $importedJobContractId = (int)$params['jobcontract_id'];
    }
    
    if (!$importedJobContractId) {
        $importedJobContractId = $this->_jobcontractIdIncremental++;
    }
    
    if (empty($params['contact_id']) && !empty($params['email'])) {
        $checkEmail = new CRM_Core_BAO_Email();
        $checkEmail->email = $params['email'];
        $checkEmail->find(TRUE);
        if (!empty($checkEmail->contact_id))
        {
            $params['contact_id'] = $checkEmail->contact_id;
        }
    }
    
    if (!empty($formatValues['external_identifier'])) {
      $checkCid = new CRM_Contact_DAO_Contact();
      $checkCid->external_identifier = $formatValues['external_identifier'];
      $checkCid->find(TRUE);
      if (!empty($params['contact_id']) && $params['contact_id'] != $checkCid->id) {
        array_unshift($values, 'Mismatch of External identifier :' . $formatValues['external_identifier'] . ' and Contact Id:' . $params['contact_id']);
        return CRM_Import_Parser::ERROR;
      }
      if (!empty($checkCid->id)) {
          $params['contact_id'] = $checkCid->id;
      }
    }
    
    if (empty($params['contact_id'])) {
        $error = 'Missing "contact_id" / "email" / "external_identifier" value.';
        array_unshift($values, $error);
        return CRM_Import_Parser::ERROR;
    }
    
    $revisionParams = $this->getEntityParams('HRJobContractRevision');
    $revisionData = array();
    foreach ($entityNames as $value) {
        if (empty($revisionParams[$value . '_revision_id'])) {
            $revisionParams[$value . '_revision_id'] = $this->_revisionIdIncremental;
        }
        $revisionData[$value] = $revisionParams[$value . '_revision_id'];
    }
    $this->_revisionIdIncremental++;
    
    if (empty($revisionData)) {
        $error = 'Missing Revision data.';
        array_unshift($values, $error);
        return CRM_Import_Parser::ERROR;
    }
    
    $revisionId = max($revisionData);
    
    if (empty($this->_jobContractIds[$importedJobContractId])) {
        try {
            $jobContractCreateResponse = civicrm_api3('HRJobContract', 'create', array('contact_id' => $params['contact_id']));
        }
        catch (CiviCRM_API3_Exception $e) {
            $error = $e->getMessage();
            array_unshift($values, $error);
            return CRM_Import_Parser::ERROR;
        }
        $this->_jobContractIds[$importedJobContractId] = (int)$jobContractCreateResponse['id'];
        $this->_previousRevision = array();
        foreach ($entityNames as $value) {
            $this->_previousRevision['imported'][$value] = null;
            $this->_previousRevision['local'][$value] = null;
        }
        $this->_previousRevision['imported']['id'] = null;
        $this->_previousRevision['local']['id'] = null;
        $this->_revisionIds = array();
        $this->_revisionEntityMap = array();
    }
    $localJobContractId = $this->_jobContractIds[$importedJobContractId];
    
    $newRevisionInstance = null;
    if ($this->_previousRevision['imported']['id'] !== $revisionId) {
        // create new Revision:
        $newRevisionParams = $revisionParams;
        unset($newRevisionParams['id']);
        foreach ($entityNames as $value) {
            unset($newRevisionParams[$value . '_revision_id']);
        }
        $newRevisionParams['jobcontract_id'] = $localJobContractId;
        $newRevisionParams = $this->validateFields('HRJobContractRevision', $newRevisionParams);
        $newRevisionInstance = CRM_Hrjobcontract_BAO_HRJobContractRevision::create($newRevisionParams);
        
        if (!empty($this->_previousRevision['imported']['id'])) {
            foreach ($entityNames as $value) {
                $field = $value . '_revision_id';
                $newRevisionInstance->$field = $this->_previousRevision['local'][$value];
            }
            $newRevisionInstance->save();
        }
    }
    
    
    
    try {
      foreach ($this->_entity as $entity) {
        
        if (in_array($entity, array('HRJobContract', 'HRJobContractRevision'))) {
            continue;
        }
        
        $entityClass = 'CRM_Hrjobcontract_BAO_' . $entity;
        $tableName = _civicrm_get_table_name($entity);
        
        if (empty($revisionParams[$tableName . '_revision_id'])) {
            continue;
        }
        
        $params = $this->getEntityParams($entity);
        $params['jobcontract_id'] = $localJobContractId;
        
        foreach ($params as $key => $value) {
            $params[$key] = $ei->import($tableName, $key, $value);
        }
        
        $params = $this->validateFields($entity, $params);
        $params['import'] = 1;
        if ($revisionParams[$tableName . '_revision_id'] === $revisionId) {
            if ($tableName === 'leave' || ($this->_previousRevision['imported'][$tableName] !== $revisionId)) {
                if (!empty($newRevisionInstance)) {
                    $params['jobcontract_revision_id'] = $newRevisionInstance->id;
                } else {
                    $params['jobcontract_revision_id'] = $this->_previousRevision['local'][$tableName];
                }
                if ($tableName === 'leave')
                {
                    foreach ($params['leave_amount'] as $leaveTypeId => $leaveAmount)
                    {
                        $params['leave_type'] = $leaveTypeId;
                        $params['leave_amount'] = $leaveAmount;
                        $entityInstance = $entityClass::create($params);
                    }
                }
                else
                {
                    $entityInstance = $entityClass::create($params);
                }
                $this->_previousRevision['local'][$tableName] = $entityInstance->jobcontract_revision_id;
            }
        }
        $this->_previousRevision['imported'][$tableName] = $revisionParams[$tableName . '_revision_id'];
      }
    } catch(CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      array_unshift($values, $error);
      return CRM_Import_Parser::ERROR;
    }
    
    if (!empty($newRevisionInstance)) {
        foreach ($entityNames as $value) {
            $field = $value . '_revision_id';
            $newRevisionInstance->$field = $this->_previousRevision['local'][$value];
        }
        $newRevisionInstance->save();
    }
    $this->_previousRevision['imported']['id'] = $revisionId;
  }

  /**
   * Format Date params
   *
   * Although the api will accept any strtotime valid string CiviCRM accepts at least one date format
   * not supported by strtotime so we should run this through a conversion
   * @param unknown $params
   */
  function formatDateParams() {
    $session = CRM_Core_Session::singleton();
    $dateType = $session->get('dateTypes');
    $setDateFields = array_intersect_key($this->_params, array_flip($this->_dateFields));
    foreach ($setDateFields as $key => $value) {
      CRM_Utils_Date::convertToDefaultDate($this->_params, $dateType, $key);
      $this->_params[$key] = CRM_Utils_Date::processDate($this->_params[$key]);
    }
  }

  function formatData(&$params) {
    $fields = $this->_allFields;
    foreach ($params as $key => $value)  {
      if ($value) {
        if (array_key_exists($key, $fields)) {
          if (array_key_exists('enumValues', $fields[$key])) {
            $enumValue = $fields[$key]['enumValues'];
            $enumArray = explode(',', $enumValue);
            if ($val = array_search(strtolower(trim($value)), array_map('strtolower', $enumArray))) {
              $params[$key] = $enumArray[$val];
            }
          }
          if (array_key_exists('pseudoconstant', $fields[$key])) {
	    if (array_key_exists('optionGroupName', $fields[$key]['pseudoconstant'])) {
	      $options = CRM_Core_OptionGroup::values($fields[$key]['pseudoconstant']['optionGroupName'], FALSE, FALSE, FALSE, NULL, 'name');
	      if (array_key_exists(strtolower(trim($value)), array_change_key_case($options))) {
		$flipOpt = array_change_key_case($options);
		$params[$key] = $flipOpt[strtolower(trim($value))];
	      }
	    }
          }
          if ($fields[$key]['type'] == CRM_Utils_Type::T_BOOLEAN ) {
            $params[$key] = CRM_Utils_String::strtoboolstr($value);
          }
        }
      }
    }
  }
  
  function validateFields($entity, $params, $action = 'create') {
    $bao = 'CRM_Hrjobcontract_BAO_' . $entity;
    $fields = $bao::fields();
    $fieldKeys = $bao::fieldKeys();
    
    $mappedParams = array();
    foreach ($fieldKeys as $key => $value) {
      if (!empty($params[$key])) {
        $mappedParams[$value] = $params[$key];
      }
    }
    _civicrm_api3_validate_fields($entity, $action, $mappedParams, $fields);
    foreach ($fieldKeys as $key => $value) {
      if (!empty($mappedParams[$value])) {
        $params[$key] = $mappedParams[$value];
      }
    }
    
    return $params;
  }

  /**
   * Set import entity
   * @param string $entity
   */
  function setEntity($entity) {
    $this->_entity = $entity;
  }
  
  /**
   * Return params for specified entity
   * @param string $entity
   * @return array params
   */
  function getEntityParams($entity) {
    $params = $this->getActiveFieldParams();
    for ($i = 0; $i < $this->_activeFieldCount; $i++) {
      if (!isset($this->_activeEntityFields[$entity][$this->_activeFields[$i]->_name])) {
        unset($params[$this->_activeFields[$i]->_name]);
      }
    }
    return $params;
  }
}
