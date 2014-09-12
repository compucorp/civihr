<?php
class CRM_HRJob_Import_Parser_Api extends CRM_HRJob_Import_Parser_BaseClass {
  protected $_entity;
  protected $_fields = array();
  protected $_requiredFields = array();
  protected $_dateFields = array();
  protected $_entityFields = array();
  protected $_allFields = array();

  /**
   * Params for the current entity being prepared for the api
   * @var array
   */
  protected $_params = array();

  function setFields() {
    $this->_fields = array();
    $allFields = array();

    foreach ($this->_entity as $entity) {
      $fields = civicrm_api3($entity, 'getfields', array('action' => 'create'));
      $entityFields[$entity] = $fields['values'];
      foreach ($fields['values'] as $field => $values) {
        if (!empty($values['api.required']) && $field != 'job_id') {
          $requirefields[$entity] = $this->_requiredFields[$entity] = $field;
        }
        // date is 4 & time is 8. Together they make 12 - in theory a binary operator makes sense here but as it's not a common pattern it doesn't seem worth the confusion
        if (CRM_Utils_Array::value('type', $values) == 12
           || CRM_Utils_Array::value('type', $values) == 4) {
          $this->_dateFields[$entity] = $field;
        }
      }
      $allFields = array_merge($fields['values'], $allFields);
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
   * @see CRM_HRjob_Import_Parser_BaseClass::summary()
   */
  function summary(&$values) {
    $erroneousField = NULL;
    $response      = $this->setActiveFieldValues($values, $erroneousField);
    $errorRequired = FALSE;
    $missingField = '';
    $errorMessage = NULL;
    $errorMessages = array();

    foreach ($this->_entity as $entity) {
      $this->_params = $this->getActiveFieldParams();
      foreach ($this->_requiredFields as $requiredFieldKey => $requiredFieldVal) {
        if (empty($this->_params[$requiredFieldVal])) {
          $errorRequired = TRUE;
          $missingField .= ' ' . $requiredField;
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
    $response = $this->summary($values);
    $this->formatDateParams();
    $this->_params['skipRecentView'] = TRUE;
    $this->_params['check_permissions'] = TRUE;
    //JOB ID
    $params = $this->getActiveFieldParams();

    for ($i = 0; $i < $this->_activeFieldCount; $i++) {
      if (!isset($this->_activeEntityFields['HRJob'][$this->_activeFields[$i]->_name])) {
        unset($params[$this->_activeFields[$i]->_name]);
      }
    }
    self::formatData($params);
    try{
      $fieldJob = civicrm_api3('HRJob', 'create', $params);
    }
    catch(CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      array_unshift($values, $error);
      return CRM_Import_Parser::ERROR;
    }
    try{
      foreach ($this->_entity as $entity) {
        if ($entity != 'HRJob') {
          $params = $this->getActiveFieldParams();
          for ($i = 0; $i < $this->_activeFieldCount; $i++) {
            if (!isset($this->_activeEntityFields[$entity][$this->_activeFields[$i]->_name])) {
              unset($params[$this->_activeFields[$i]->_name]);
            }
          }
          self::formatData($params);
          if (!empty($params)) {
            if ($entity == 'HRJobLeave') {
              foreach($params['leave_amount'] as $key => $val) {
                $params = $val;
                $params['job_id'] = $fieldJob['id'];
                $field = civicrm_api3($entity, 'create', $params);
              }
            }
            else {
              $params['job_id'] = $fieldJob['id'];
              $field = civicrm_api3($entity, 'create', $params);
            }
          }
        }
      }
    } catch(CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      array_unshift($values, $error);
      return CRM_Import_Parser::ERROR;
    }
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

  /**
   * Set import entity
   * @param string $entity
   */
  function setEntity($entity) {
    $this->_entity = $entity;
  }
}
