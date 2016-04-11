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
   * Params for the current import mode used ( Import Contracts Or Contracts Revision )
   * @var integer
   */
  protected $_importMode = NULL;

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
    $errorRequired = FALSE;
    $missingField = '';
    $errorMessage = NULL;
    $errorMessages = array();

    $params = &$this->getActiveFieldParams();

    //for date-Formats
    $session = CRM_Core_Session::singleton();
    $dateType = $session->get('dateTypes');
    $filter_postive_options = array(
      'options' => array( 'min_range' => 0)
    );
    // check some parameters if they are valid
    foreach ($params as $key => $val) {
      switch ($key) {
        case 'HRJobHour-hours_amount':
          if ( filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Actual Hours (Amount) should be positive number', $errorMessage);
          }
          break;
        case 'HRJobHour-hours_unit':
          if (!empty($val)) {
            $optionID = $this->getStaticOptionKey($key, $val);
            if ($optionID !== FALSE) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Actual Hours (Unit) is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobHour-fte_denom':
          if ( filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Full-Time Denominator Equivalence should be positive number', $errorMessage);
          }
          break;
        case 'HRJobHour-hours_fte':
          if ( filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Full-Time Equivalence should be positive number', $errorMessage);
          }
          break;
        case 'HRJobHour-fte_num':
          if ( filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Full-Time Numerator Equivalence should be positive number', $errorMessage);
          }
          break;
        case 'HRJobHour-hours_type':
          if (!empty($val)) {
            $optionID = $this->getOptionKey($key, $val, 'value');
            if ($optionID !== 0) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Hours Type is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobHour-location_standard_hours':
          if (!empty($val)) {
            $optionID = $this->getOptionKey($key, $val);
            if ($optionID !== 0) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Location/Standard hours is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobPension-ee_contrib_abs':
          if ( filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Employee Contribution Absolute Amount should be positive number', $errorMessage);
          }
          break;
        case 'HRJobPension-ee_contrib_pct':
          if ( filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Employee Contribution Percentage should be positive number', $errorMessage);
          }
          break;
        case 'HRJobPension-er_contrib_pct':
          if ( filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Employer Contribution Percentage should be positive number', $errorMessage);
          }
          break;
        case 'HRJobPension-pension_type':
          if (!empty($val)) {
            $optionID = $this->getOptionKey($key, $val, 'label');
            if ($optionID !== 0) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Pension Provider is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobPension-is_enrolled':
          if (!empty($val)) {
            $dbValue = NULL;
            switch (strtolower($val))
            {
              case 'no':
                $dbValue = 0;
                break;
              case 'yes':
                $dbValue = 1;
                break;
              case 'opted out':
                $dbValue = 2;
                break;
              default:
                CRM_Contact_Import_Parser_Contact::addToErrorMsg('(Pension: Is Enrolled) should be "Yes", "No" or "opted out")', $errorMessage);
                break;
            }
            $params[$key] = $dbValue;
          }
          break;
        case 'HRJobHealth-plan_type':
          if (!empty($val)) {
            $optionID = $this->getStaticOptionKey($key, $val);
            if ($optionID !== FALSE) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Healthcare Plan Type is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobHealth-provider':
          $params[$key] = NULL;
          if (!empty($val)) {
            $result = CRM_Hrjobcontract_BAO_HRJobHealth::checkProvider($val, 'Health_Insurance_Provider');
            if ($result != 0)  {
              $params[$key] = $result;
            }
            else  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Health insurance Provider is not an existing provider', $errorMessage);
            }
          }
          break;
        case 'HRJobHealth-plan_type_life_insurance':
          if (!empty($val)) {
            $optionID = $this->getStaticOptionKey($key, $val);
            if ($optionID !== FALSE) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Life insurance Plan Type is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobHealth-provider_life_insurance':
          $params[$key] = NULL;
          if (!empty($val)) {
            $result = CRM_Hrjobcontract_BAO_HRJobHealth::checkProvider($val, 'Life_Insurance_Provider');
            if ($result != 0)  {
              $params[$key] = $result;
            }
            else  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Life insurance Provider is not an existing provider', $errorMessage);
            }
          }
          break;
        case 'HRJobPay-is_paid':
          if (!empty($val)) {
            $dbValue = NULL;
            switch (strtolower($val))
            {
              case 'no':
                $dbValue = 0;
                break;
              case 'yes':
                $dbValue = 1;
                break;
              default:
                CRM_Contact_Import_Parser_Contact::addToErrorMsg('Paid column value should be ("Yes" or "No")', $errorMessage);
                break;
            }
            $params[$key] = $dbValue;
          }
          break;
        case 'HRJobPay-pay_cycle':
          if (!empty($val)) {
            $optionID = $this->getOptionKey($key, $val, 'value');
            if ($optionID !== 0) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Pay Cycle is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobPay-pay_per_cycle_gross':
          if ( filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Pay Per Cycle Gross should be positive number', $errorMessage);
          }
          break;
        case 'HRJobPay-pay_per_cycle_net':
          if ( filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Pay Per Cycle Net  should be positive number', $errorMessage);
          }
          break;
        case 'HRJobPay-pay_scale':
          if (!empty($val)) {
            $optionID = $this->getOptionKey($key, $val);
            if ($optionID !== 0) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Pay Scale is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobPay-pay_currency':
          if (!empty($val)) {
            $optionID = $this->getOptionKey($key, $val);
            if ($optionID !== 0) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Pay currency is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobPay-pay_amount':
          if ( filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Pay Amount should be positive number', $errorMessage);
          }
          break;
        case 'HRJobPay-pay_unit':
          if (!empty($val)) {
            $optionID = $this->getStaticOptionKey($key, $val);
            if ($optionID !== FALSE) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Pay Unit is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobDetails-contract_type':
          if (!empty($val)) {
            $optionID = $this->getOptionKey($key, $val, 'label');
            if ($optionID !== 0) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Contract Type is not valid', $errorMessage);
            }
          }
          else {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Contract Type is required', $errorMessage);
          }
          break;
        case 'HRJobDetails-period_end_date':
          if ($val) {
            $dateValue = CRM_Utils_Date::formatDate($val, $dateType);
            if ($dateValue) {
              $params[$key] = $dateValue;
            }
            else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('contract end date is not a valid date', $errorMessage);
            }
          }
          break;
        case 'HRJobDetails-period_start_date':
          if (empty($val))  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('contract start date is required', $errorMessage);
          }
          else  {
            $dateValue = CRM_Utils_Date::formatDate($val, $dateType);
            if ($dateValue) {
              $params[$key] = $dateValue;
            }
            else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('contract start date is not a valid date', $errorMessage);
            }
          }
          break;
        case 'HRJobContractRevision-effective_date':
          if ($val) {
            $dateValue = CRM_Utils_Date::formatDate($val, $dateType);
            if ($dateValue) {
              $params[$key] = $dateValue;
            }
            else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('effective date is not a valid date', $errorMessage);
            }
          }
          break;
        case 'HRJobDetails-location':
          if (!empty($val)) {
            $optionID = $this->getOptionKey($key, $val, 'value');
            if ($optionID !== 0) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Normal Place of Work is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobDetails-notice_amount_employee':
          if ( filter_var($val, FILTER_VALIDATE_INT) === FALSE  || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Notice Period from Employee (Amount) should be positive integer', $errorMessage);
          }
          break;
        case 'HRJobDetails-notice_amount':
          if ( filter_var($val, FILTER_VALIDATE_INT) === FALSE  || $val < 0 )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('Notice Period from Employer (Amount) should be positive integer', $errorMessage);
          }
          break;
        case 'HRJobDetails-notice_unit_employee':
          if (!empty($val)) {
            $optionID = $this->getStaticOptionKey($key, $val);
            if ($optionID !== FALSE) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Notice Period from Employee (Unit) is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobDetails-notice_unit':
          if (!empty($val)) {
            $optionID = $this->getStaticOptionKey($key, $val);
            if ($optionID !== FALSE) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Notice Period from Employer (Unit) is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobDetails-position':
          if (empty($val)) {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('contract position is required', $errorMessage);
          }
          break;
        case 'HRJobDetails-title':
          if (empty($val)) {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('contract title is required', $errorMessage);
          }
          break;
        case 'HRJobDetails-end_reason':
          if (!empty($val)) {
            $optionID = $this->getOptionKey($key, $val);
            if ($optionID !== 0) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Contract end reason is not valid', $errorMessage);
            }
          }
          break;
        case 'HRJobContractRevision-change_reason':
          if (!empty($val)) {
            $optionID = $this->getOptionKey($key, $val);
            if ($optionID !== 0) {
              $params[$key] = $optionID;
            } else {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Contract Revision change reason is not valid', $errorMessage);
            }
          }
          break;
      }
    }

    if ($this->_importMode == CRM_Hrjobcontract_Import_Parser::IMPORT_REVISIONS)  {
      $contract_id = $params['HRJobContractRevision-jobcontract_id'];
      if (!empty($contract_id))  {
        $contractDetails = CRM_Hrjobcontract_BAO_HRJobContract::checkContract($contract_id);
        if ($contractDetails == 0)  {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('Contract ID is not found', $errorMessage);
        }
      }else {
        CRM_Contact_Import_Parser_Contact::addToErrorMsg('Contract ID is required', $errorMessage);
      }

      if (empty($params['HRJobContractRevision-effective_date'])) {
        CRM_Contact_Import_Parser_Contact::addToErrorMsg('effective date is required', $errorMessage);
      }
    }
    else  {
      try  {
        $params['HRJobContract-contact_id'] = $this->determineContactId($params);
      }
      catch (\RuntimeException $e) {
        CRM_Contact_Import_Parser_Contact::addToErrorMsg($e->getMessage(), $errorMessage);
      }

    }

    if ($errorMessage) {
      $tempMsg = "Invalid value for field(s) : $errorMessage";
      array_unshift($values, $tempMsg);
      $errorMessage = NULL;
      return CRM_Import_Parser::ERROR;
    }

    $this->_params = $params;
    //var_dump($params);exit;
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

    $this->_importMode = $values['importMode'];
    unset($values['importMode']);

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
}
