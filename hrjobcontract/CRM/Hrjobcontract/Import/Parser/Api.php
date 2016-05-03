<?php
class CRM_Hrjobcontract_Import_Parser_Api extends CRM_Hrjobcontract_Import_Parser_BaseClass {
  protected $_entity;
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
    $this->_allFields = array();

    $entityFields = array();
    /** @var CRM_Hrjobcontract_Import_FieldsProvider[] $fieldProviders */
    $fieldProviders = array(
      'HRJobRole' => new CRM_Hrjobcontract_Import_FieldsProvider_HRJobRole()
    );
    if (empty($this->_entity))  {
      $this->_entity = array(
        'HRJobContract',
        'HRJobContractRevision',
        'HRJobDetails',
        'HRJobPay',
        'HRJobHealth',
        'HRJobPension',
        'HRJobHour',
        'HRJobLeave'
      );
    }

    foreach ($this->_entity as $entity) {
      if(!isset($fieldProviders[$entity])) {
        $fieldProviders[$entity] = new CRM_Hrjobcontract_Import_FieldsProvider_Generic($entity);
      }
      $entityFields[$entity] = $fieldProviders[$entity]->provide();

      $this->handleSpecialFields($entityFields, $entity);

      $this->_allFields = array_merge($entityFields[$entity], $this->_allFields);
    }

    $this->_entityFields = $entityFields;
    $this->_fields = array_merge(array('do_not_import' => array('title' => ts('- do not import -'))), $this->_allFields);
  }

  /**
   * @param array $entityFields
   * @param string $entity
   */
  private function handleSpecialFields(array $entityFields, $entity) {
    foreach ($entityFields[$entity] as $key => $field) {
      if (!empty($field['required'])) {
        $this->_requiredFields[] = $key;
      }

      $fieldType = CRM_Utils_Array::value('type', $field);
      $dateFieldTypes = array(
        CRM_Utils_Type::T_DATE | CRM_Utils_Type::T_TIME,
        CRM_Utils_Type::T_DATE
      );
      if ($fieldType !== null && in_array($fieldType, $dateFieldTypes)) {
        $this->_dateFields[] = $key;
      }
    }
  }

  /**
   * handle the values in preview mode
   *
   * @param array $values the array of values belonging to this line
   *
   * @return boolean      the result of this processing
   * @access public
   */
  function preview(&$values) {
    return $this->summary($values);
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
    $this->setActiveFieldValues($values, $erroneousField);

    $errorsMessages = $this->validateFieldsValues();

    if ($errorsMessages !== NULL) {
      $tempMsg = "Invalid value for field(s) : $errorsMessages";
      array_unshift($values, $tempMsg);
      $errorsMessages = NULL;
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

    // first make sure this is a valid line
    $response = $this->summary($values);

    if ($response != CRM_Import_Parser::VALID) {
      return $response;
    }

    $this->_params['skipRecentView'] = TRUE;
    $this->_params['check_permissions'] = TRUE;
    
    //$params = $this->getActiveFieldParams();
    $params = $this->_params;

    try {
      $importedJobContractId = $this->determineContractId($params);
      list($revisionParams, $revisionId) = $this->getRevisionData($entityNames);
      if ($this->_importMode == CRM_Hrjobcontract_Import_Parser::IMPORT_REVISIONS)  {
        $localJobContractId = $params['HRJobContractRevision-jobcontract_id'];
      }
      else  {
        $contactId = $params['HRJobContract-contact_id'];
        $localJobContractId = $this->createJobContract($importedJobContractId, $contactId, $entityNames);
      }
      $contractRevison = $this->createContractRevison($revisionId, $revisionParams, $entityNames, $localJobContractId);
      $this->importRelatedEntities($params, $revisionParams, $localJobContractId, $revisionId, $contractRevison);
    } catch(\RuntimeException $e) {
      array_unshift($values, $e->getMessage());

      return CRM_Import_Parser::ERROR;
    }

    $this->_previousRevision['imported']['id'] = $revisionId;
    return CRM_Import_Parser::VALID;
  }

  /**
   * Format Date params
   *
   * Although the api will accept any strtotime valid string CiviCRM accepts at least one date format
   * not supported by strtotime so we should run this through a conversion
   * @param array $params
   */
  function formatDateParams($entity, $params) {
    $session = CRM_Core_Session::singleton();
    $dateType = $session->get('dateTypes');

    foreach ($params as $key => $value) {
      if(!in_array($key, $this->_dateFields)) {
        continue;
      }

      CRM_Utils_Date::convertToDefaultDate($params, $dateType, $key);
      $params[$key] = CRM_Utils_Date::processDate($params[$key]);
    }

    return $params;
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

  private function getBAOName($entity) {
    if($entity === 'HRJobRole') {
      return 'CRM_Hrjobroles_BAO_HrJobRoles';
    }

    return 'CRM_Hrjobcontract_BAO_' . $entity;
  }
  
  function validateFields($entity, $params, $action = 'create') {
    $BAOName = $this->getBAOName($entity);
    $fields = call_user_func(array($BAOName, 'fields'));
    $fieldKeys = call_user_func(array($BAOName, 'fieldKeys'));

    $relationKeys = array('jobcontract_id', 'job_contract_id', 'jobcontract_revision_id', 'id');
    $mappedParams = array();
    foreach ($fieldKeys as $key => $value) {
      $fieldName = $entity . '-' . $key;
      if (!empty($params[$fieldName])) {
        $mappedParams[$value] = $params[$fieldName];
      } else if(!in_array($key, $relationKeys) && array_search($fieldName, $this->_requiredFields) !== false) {
        throw new \RuntimeException(sprintf('The field %s is required.', !empty($fields[$key]['title']) ? $fields[$key]['title'] : $key));
      }
    }

    // disable validation of pseudoconstant values
    // if they don't exist, they'll be ignored later
    foreach($fields as &$field) {
      if(isset($field['pseudoconstant'])) {
        unset($field['pseudoconstant']);
      }
    }

    _civicrm_api3_validate_fields($entity, $action, $mappedParams, $fields);
    foreach ($fieldKeys as $key => $value) {
      $fieldName = $entity . '-' . $key;
      if (!empty($mappedParams[$value])) {
        $params[$fieldName] = $mappedParams[$value];
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
      if (!isset($this->_activeEntityFields[$entity][$entity.'-'.$this->_activeFields[$i]->_name])) {
        unset($params[$entity.'-'.$this->_activeFields[$i]->_name]);
      }
    }

    return $params;
  }

  /**
   * @param array $params
   * @return integer
   */
  private function determineContractId($params) {
    $importedJobContractId = NULL;

    if (!empty($params['HRJobContract-jobcontract_id'])) {
      $importedJobContractId = (int) $params['HRJobContract-jobcontract_id'];
    }

    if (!$importedJobContractId) {
      $importedJobContractId = $this->_jobcontractIdIncremental++;
    }
    return $importedJobContractId;
  }

  private function determineContactId($params) {
    if(!empty($params['HRJobContract-contact_id'])) {
      $contactId = $params['HRJobContract-contact_id'];
      $user = new CRM_Contact_BAO_Contact();
      $user->id = $contactId;
      $user->find();

      if(!$user->fetch()) {
        throw new \RuntimeException(sprintf('Contact with ID %d does not exist.', $contactId));
      }

      return $contactId;
    }

    if (!empty($params['HRJobContract-email'])) {
      $checkEmail = new CRM_Core_BAO_Email();
      $checkEmail->email = $params['HRJobContract-email'];
      $checkEmail->find(TRUE);

      if (empty($checkEmail->contact_id))
      {
        throw new \RuntimeException(sprintf('Contact with email %s does not exist.', $params['HRJobContract-email']));
      }

      return $checkEmail->contact_id;
    }

    if (!empty($params['HRJobContract-external_identifier'])) {
      $checkCid = new CRM_Contact_DAO_Contact();
      $checkCid->external_identifier = $params['HRJobContract-external_identifier'];
      $checkCid->find(TRUE);

      if (empty($checkCid->id)) {
        throw new \RuntimeException(sprintf('Contact with external identifier %s does not exist.', $params['HRJobContract-external_identifier']));
      }
      return $checkCid->id;
    }


    $error = 'Missing "contact id" / "email" / "external identifier" value.';
    throw new \RuntimeException($error);
  }

  private function getRevisionData($entityNames) {
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
      throw new \RuntimeException($error);
    }

    return array($revisionParams, max($revisionData));
  }

  private function createJobContract($importedJobContractId, $contactId, $entityNames) {
    if (empty($this->_jobContractIds[$importedJobContractId])) {
      try {
        $jobContractCreateResponse = civicrm_api3('HRJobContract', 'create', array('contact_id' => $contactId));
      }
      catch (CiviCRM_API3_Exception $e) {
        throw new \RuntimeException($e->getMessage());
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

    return $this->_jobContractIds[$importedJobContractId];
  }

  /**
   * @param $revisionId
   * @param $revisionParams
   * @param $entityNames
   * @param $localJobContractId
   * @return array
   */
  private function createContractRevison($revisionId, $revisionParams, $entityNames, $localJobContractId) {
    $newRevisionInstance = NULL;
    if ($this->_previousRevision['imported']['id'] !== $revisionId) {
      // create new Revision:
      $newRevisionParams = $revisionParams;
      unset($newRevisionParams['id']);
      foreach ($entityNames as $value) {
        unset($newRevisionParams[$value . '_revision_id']);
      }
      $newRevisionParams['jobcontract_id'] = $localJobContractId;
      if ($this->_importMode == CRM_Hrjobcontract_Import_Parser::IMPORT_REVISIONS)  {
        $newRevisionParams = $this->formatDateParams(array(), $newRevisionParams);
        $newRevisionParams['effective_date'] = $newRevisionParams['HRJobContractRevision-effective_date'];
      }
      $newRevisionInstance = CRM_Hrjobcontract_BAO_HRJobContractRevision::create($newRevisionParams);

      if (!empty($this->_previousRevision['imported']['id'])) {
        foreach ($entityNames as $value) {
          $field = $value . '_revision_id';
          $newRevisionInstance->$field = $this->_previousRevision['local'][$value];
        }
        $newRevisionInstance->save();
      }
    }

    return $newRevisionInstance;
  }

  /**
   * @param $revisionParams
   * @param $jobContractId
   * @param $ei
   * @param $revisionId
   * @param $contractRevision
   * @return mixed
   */
  private function importRelatedEntities(array $params, $revisionParams, $jobContractId, $revisionId, $contractRevision) {
    /** @var CRM_Hrjobcontract_Import_EntityHandler[] $entityHandlers */
    $entityHandlers = array(
      'HRJobRole' => new CRM_Hrjobcontract_Import_EntityHandler_HRJobRole(),
      'HRJobLeave' => new CRM_Hrjobcontract_Import_EntityHandler_HRJobLeave(),
      'HRJobHealth' => new CRM_Hrjobcontract_Import_EntityHandler_HRJobHealth(),
      'HRJobDetails' => new CRM_Hrjobcontract_Import_EntityHandler_HRJobDetails()
    );
    $ei = CRM_Hrjobcontract_ExportImportValuesConverter::singleton();

    foreach ($this->_entity as $entity) {
      if (in_array($entity, array('HRJobContract', 'HRJobContractRevision'))) {
        continue;
      }

      $tableName = _civicrm_get_table_name($entity);

      if (empty($revisionParams[$tableName . '_revision_id'])) {
        continue;
      }

      $params['HRJobContract-jobcontract_id'] = $jobContractId;

      if(!isset($params['HRJobRole-start_date'])) {
        $params['HRJobRole-start_date'] = $params['HRJobDetails-period_start_date'];
      }

      if (!empty($contractRevision)) {
        $params['HRJobContract-jobcontract_revision_id'] = $contractRevision->id;
      }
      else {
        throw new API_Exception('JobContract revision has not been created.');
      }

      $params = $this->formatDateParams($entity, $params);

      $entityInstance = null;
      if ($revisionParams[$tableName . '_revision_id'] === $revisionId) {
        if ($entity === 'HRJobLeave' || ($this->_previousRevision['imported'][$tableName] !== $revisionId)) {
          $handler = isset($entityHandlers[$entity])
            ? $entityHandlers[$entity]
            : new CRM_Hrjobcontract_Import_EntityHandler_Generic($entity);

          $entityInstance = $handler->handle($params, $contractRevision, $this->_previousRevision);
          $this->_previousRevision['local'][$tableName] = isset($entityInstance[0]) ? $entityInstance[0]->id : null;
          $this->_previousRevision['imported'][$tableName] = $revisionParams[$tableName . '_revision_id'];
        }
      }
    }
  }

  private function validateFieldsValues()  {
    $params = &$this->getActiveFieldParams();
    $errorMessages = NULL;

    $requiredFields = array(
      'HRJobDetails-title',
      'HRJobDetails-position',
      'HRJobDetails-contract_type',
      'HRJobDetails-period_start_date'
    );

    if ($this->_importMode == CRM_Hrjobcontract_Import_Parser::IMPORT_REVISIONS) {
      $requiredFields = array_merge($requiredFields, array(
        'HRJobContractRevision-jobcontract_id',
        'HRJobContractRevision-effective_date'
      ));
    }
    else  {
      try  {
        $params['HRJobContract-contact_id'] = $this->determineContactId($params);
      }
      catch (\RuntimeException $e) {
        CRM_Contact_Import_Parser_Contact::addToErrorMsg($e->getMessage(), $errorMessages);
      }
    }

    foreach ($requiredFields as $item) {
      if (!in_array($item, $this->_mapperKeys) || $params[$item] == '')  {
        CRM_Contact_Import_Parser_Contact::addToErrorMsg("{$this->_fields[$item]->_title} is required", $errorMessages);
      }
    }

    foreach ($params as $key => $val) {
      if ($val != '')  {
        $validationData = $this->validateField($key, $val);
        if ($validationData['error_message'] !== NULL)  {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg($validationData['error_message'], $errorMessages);
          continue;
        }
        $params[$key] = $validationData['value'];
      }
    }
    $this->_params = $params;
    return $errorMessages;
  }



  private function validateField($key, $value)  {
    $errorMessage = NULL;
    $convertedValue = $value;
    $optionReturnType = 'id';
    // TODO: all values should be stored by values and this part should be removed
    if ( in_array($key, array('HRJobPension-pension_type', 'HRJobDetails-contract_type')) )  {
      $optionReturnType = 'label';
    }
    elseif ( in_array($key, array('HRJobHour-hours_type', 'HRJobPay-pay_cycle', 'HRJobDetails-location', 'HRJobDetails-end_reason')) )  {
      $optionReturnType = 'value';
    }

    switch($key)  {
      case 'HRJobContractRevision-jobcontract_id':
        $contractDetails = CRM_Hrjobcontract_BAO_HRJobContract::checkContract($value);
        if ($contractDetails == 0)  {
          $errorMessage = "{$this->_fields[$key]->_title} is not found";
        }
        break;
      case 'HRJobHour-fte_denom':
      case 'HRJobHour-hours_fte':
      case 'HRJobHour-fte_num':
      case 'HRJobPension-ee_contrib_abs':
      case 'HRJobPension-ee_contrib_pct':
      case 'HRJobPension-er_contrib_pct':
      case 'HRJobPay-pay_per_cycle_gross':
      case 'HRJobPay-pay_per_cycle_net':
      case 'HRJobPay-pay_amount':
        if ( filter_var($value, FILTER_VALIDATE_FLOAT) === FALSE || $value < 0 )  {
          $errorMessage = "{$this->_fields[$key]->_title} should be positive number";
        }
        break;
      case 'HRJobDetails-notice_amount_employee':
      case 'HRJobDetails-notice_amount':
        if ( filter_var($value, FILTER_VALIDATE_INT) === FALSE  || $value < 0 )  {
          $errorMessage = "{$this->_fields[$key]->_title} should be positive integer";
        }
        break;
      case 'HRJobHour-location_standard_hours':
      case 'HRJobPay-pay_scale':
      case 'HRJobPay-pay_currency':
      case 'HRJobContractRevision-change_reason':
      case 'HRJobPension-pension_type':
      case 'HRJobDetails-contract_type':
      case 'HRJobHour-hours_type':
      case 'HRJobPay-pay_cycle':
      case 'HRJobDetails-location':
      case 'HRJobDetails-end_reason':
        $optionID = $this->getOptionID($key, $value, $optionReturnType);
        if ($optionID !== FALSE) {
          $convertedValue = $optionID;
        } else {
          $errorMessage = "{$this->_fields[$key]->_title} is not valid";
        }
        break;
      case 'HRJobHour-hours_unit':
      case 'HRJobHealth-plan_type':
      case 'HRJobHealth-plan_type_life_insurance':
      case 'HRJobPay-pay_unit':
      case 'HRJobDetails-notice_unit_employee':
      case 'HRJobDetails-notice_unit':
      case 'HRJobPension-is_enrolled':
      case 'HRJobPay-is_paid':
        $optionID = $this->getHardCodedOptionID($key, $value);
        if ($optionID !== FALSE) {
          $convertedValue = $optionID;
        } else {
          $errorMessage = "{$this->_fields[$key]->_title} is not valid";
        }
        break;
      case 'HRJobDetails-period_start_date':
      case 'HRJobDetails-period_end_date':
      case 'HRJobContractRevision-effective_date':
        $session = CRM_Core_Session::singleton();
        $dateType = $session->get('dateTypes');
        $dateValue = CRM_Utils_Date::formatDate($value, $dateType);
        if ($dateValue) {
          $convertedValue = $dateValue;
        }
        else {
          $errorMessage = "{$this->_fields[$key]->_title} is not a valid date";
        }
        break;
      case 'HRJobHealth-provider':
      case 'HRJobHealth-provider_life_insurance':
        $providerType = 'Health_Insurance_Provider';
        if ($key == 'HRJobHealth-provider_life_insurance')  {
          $providerType = 'Life_Insurance_Provider';
        }
        $convertedValue = NULL;
        $result = CRM_Hrjobcontract_BAO_HRJobHealth::checkProvider($value, $providerType);
        if ($result != 0)  {
          $convertedValue = $result;
        }
        else  {
          $errorMessage = "{$this->_fields[$key]->_title} is not an existing provider";
        }
        break;
      case 'HRJobLeave-leave_amount':
        $convertedValue = array();
        $leaveAmounts = explode(',', $value);
        if (!empty($leaveAmounts))  {
          foreach($leaveAmounts as $leave)  {
            $typeAndAmount = explode(':', $leave);
            if (!empty($typeAndAmount))  {
              $leaveType = trim($typeAndAmount[0]);
              $leaveAmount = trim($typeAndAmount[1]);
              if ( filter_var($leaveAmount, FILTER_VALIDATE_INT) === FALSE  || $leaveAmount < 0 )  {
                $errorMessage = "Leave Amount values should be positive integers";
                break;
              }
              $typeID = $this->getOptionID('HRJobLeave-leave_type', $leaveType);
              if ($typeID === FALSE) {
                $errorMessage = "({$leaveType}) is not a valid leave type";
              }
              $convertedValue[$typeID] = $leaveAmount;
            }
            else {
              $errorMessage = "{$this->_fields[$key]->_title} format is not correct";
              break;
            }
          }
        }
        else {
          $errorMessage = "{$this->_fields[$key]->_title} format is not correct";
        }
        break;
      case 'HRJobPay-annual_benefits':
      case 'HRJobPay-annual_deductions':
        $valueType = 'benefit';
        if ($key == 'HRJobPay-annual_deductions')  {
          $valueType = 'deduction';
        }
        $result = $this->validateBenefitsAndDeductions($valueType, $value);
        $convertedValue = $result['value'];
        $errorMessage = $result['error_message'];
        break;
    }

    return array('value'=>$convertedValue, 'error_message'=>$errorMessage);
  }

  // TODO: add function comments
  private function validateBenefitsAndDeductions($value_type, $value)  {
    $errorMessage = NULL;
    $outputArray = array();
    $rows = explode(';', $value);
    if (!empty($rows))  {
      foreach($rows as $row)  {
        if (empty($row))  {
          continue;
        }
        $row = trim($row);
        $columns = explode(',', $row);
        $valuesList = $outputRow =  array();
        if (!empty($columns))  {
          foreach($columns as $item)  {
            $item = explode(':', $item);
            if (empty($item))  {
              $errorMessage = "annual {$value_type}s format is not correct";
              break;
            }
            $valuesList[strtolower(trim($item[0]))] = isset($item[1]) ? trim($item[1]) : '';
          }
          $nameID = '';
          if (!empty($valuesList['name']))  {
            $nameID = $this->getOptionID("{$value_type}_names", $valuesList['name'], 'value');
            if ($nameID === FALSE) {
              $errorMessage = "{$valuesList['name']} is not a valid annual {$value_type}s name";
              break;
            }
          }
          $outputRow['name'] = $nameID;

          $typeID = '';
          if (!empty($valuesList['type']))  {
            $typeID = $this->getOptionID("{$value_type}_types", $valuesList['type'], 'value');
            if ($typeID === FALSE) {
              $errorMessage = "{$valuesList['type']} is not a valid annual {$value_type}s type";
              break;
            }
          }

          if (!empty($valuesList['type']) && $valuesList['type'] == '%')  {
            if ( (!isset($valuesList['amount pct']) || filter_var($valuesList['amount pct'], FILTER_VALIDATE_FLOAT) === FALSE) )  {
              $errorMessage = "annual {$value_type}s percentage amount is not valid or set";
              break;
            }
            $amount_pct = (float)$valuesList['amount pct'];
            // get pay amount to calculate amount absolute value
            $pay_amount = 0;
            if (isset($this->_params['HRJobPay-pay_amount']))  {
              if ( filter_var($value, FILTER_VALIDATE_FLOAT) !== FALSE && $value >= 0 )  {
                $pay_amount = (float)$this->_params['HRJobPay-pay_amount'];
              }
            }
            $amount_abs = ($amount_pct/100)*$pay_amount;
          }
          else  {
            if(!isset($valuesList['amount abs']) || filter_var($valuesList['amount abs'], FILTER_VALIDATE_FLOAT) === FALSE )  {
              $errorMessage = "annual {$value_type}s absolute amount is not valid or set";
              break;
            }
            $amount_pct = '';
            $amount_abs = $valuesList['amount abs'];
          }

          $outputRow['type'] = $typeID;
          $outputRow['amount_pct'] = (string)$amount_pct;
          $outputRow['amount_abs'] = (string)$amount_abs;
          $outputArray[] = $outputRow;
        }
        else  {
          $errorMessage = "annual {$value_type}s format is not correct";
          break;
        }
      }
    }
    else {
      $errorMessage = "annual {$value_type}s  format is not correct";
    }
    return array('value'=>$outputArray, 'error_message'=>$errorMessage);
  }
}
