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
    
    $deleted = isset($params['deleted']) ? $params['deleted'] : 0;
    if ($deleted)
    {
        CRM_Hrjobcontract_JobContractDates::removeDates($instance->id);
    }
    
    if (function_exists('module_exists') && module_exists('rules')) {
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
   * Change primary contract, update all other contracts for given contract to not be primary
   * @param int $id
   */
  public static function changePrimary($id) {
    $bao = static::findById($id);
    $otherContracts = new static();
    $otherContracts->contact_id = $bao->contact_id;

    $otherContracts->find();
    while($otherContracts->fetch()) {
      static::setAsNotPrimary($otherContracts->id);
    }

    $bao->is_primary = 1;
    $bao->save();
  }

  /**
   * Set given contract as NOT primary, without checking other contracts for the contact
   *
   * @param int $id
   */
  private static function setAsNotPrimary($id) {
    $bao = static::findById($id);
    $bao->is_primary = 0;
    $bao->save();
  }

  /**
   * Find a contract by ID
   *
   * @param int $id
   * @return \CRM_Hrjobcontract_BAO_HRJobContract
   */
  public static function findById($id) {
    $bao = new CRM_Hrjobcontract_BAO_HRJobContract();
    $bao->id = $id;
    $bao->find(TRUE);

    return $bao;
  }

  /**
   * Return 'length_of_service' in days for given Contact ID, and optionally
   * Date and Break (allowed number of days between Contracts).
   * 
   * @param int     $contactId  CiviCRM Contact ID
   * @param string  $date       Y-m-d format of a date for which we calculate the result
   * @param int     $break      Allowed number of days between Contracts
   * 
   * @throws Exception
   * @return int
   */
  public static function getLengthOfService($contactId, $date = null, $break = 14) {
    if (empty($contactId))
    {
      throw new Exception("Cannot update Length of Service: no Contact ID provided.");
    }

    // Setting $date to today if it's not specified.
    if (!$date) {
      $date = date('Y-m-d');
    }

    // Getting all Job Contracts for given Contact ID.
    $contracts = civicrm_api3('HRJobContract', 'get', array(
      'sequential' => 1,
      'contact_id' => (int)$contactId,
      'deleted' => 0,
      'options' => array('limit' => 0),
    ));

    return self::calculateLength(
      self::getServiceDates(
        self::getContractDates($contracts),
        $break
      ),
      $date,
      $break
    );
  }

  /**
   * Return an associative array with Contracts for specific contact.
   * @param int $cuid
   * @return array
   */
  public static function getContactContracts($cuid)  {
    $contracts = civicrm_api3('HRJobContract', 'get', array(
      'sequential' => 1,
      'contact_id' => $cuid,
    ));
    $contracts = $contracts['values'];
    $contractsList = array();
    $counter = 0;
    foreach ($contracts as $contract)  {
      $contractDetails = civicrm_api3('HRJobDetails', 'get', array(
        'sequential' => 1,
        'jobcontract_id' => $contract['id'],
      ));
      $contractDetails = $contractDetails['values'][0];
      $contractDetails['contract_id'] = $contract['id'];
      $contractsList[$counter++] = $contractDetails;
    }

    return $contractsList;
  }

  /**
   * Return an assotiative array with Contracts dates.
   * 
   * @param array $contracts
   * 
   * @return array
   */
  protected static function getContractDates($contracts) {
    $dates = array();
    // Fill $dates array with the Contract Start and End dates
    // to get the data structure as below:
    // $dates = [
    //   'start_date1' => 'end_date1',
    //   'start_date2' => 'end_date2',
    //   'start_date3' => 'end_date3',
    // ];
    // If there are two (or more) Contracts starting on the same day
    // then we pick only the one with latest End date.
    foreach ($contracts['values'] as $contract) {
      $details = civicrm_api3('HRJobDetails', 'getsingle', array(
        'sequential' => 1,
        'jobcontract_id' => (int)$contract['id'],
      ));
      if (empty($details['period_end_date'])) {
        $dates[$details['period_start_date']] = null;
        break;
      }
      if (!empty($dates[$details['period_start_date']])) {
        if ($details['period_end_date'] > $dates[$details['period_end_date']]) {
          $dates[$details['period_start_date']] = $details['period_end_date'];
        }
        continue;
      }
      $dates[$details['period_start_date']] = $details['period_end_date'];
    }

    // Sorting $dates array by keys.
    ksort($dates);

    return $dates;
  }

  /**
   * Return an array with calculated Service Start Date and Service End Date.
   * 
   * @param array   $dates
   * @param int     $break  Number of Break days
   * 
   * @return array
   */
  protected static function getServiceDates($dates, $break) {
    $serviceStartDate = null;
    $serviceEndDate = null;
    // Calculate Service Start Date and Service End Date.
    foreach ($dates as $startDate => $endDate) {
      if (!$serviceStartDate) {
        $serviceStartDate = $startDate;
      }
      if (!$serviceEndDate) {
        $serviceEndDate = $endDate;
      }
      if ($startDate <= self::sumDateAndBreak($serviceEndDate, $break)) {
        $serviceEndDate = $endDate;
      } else {
        $serviceStartDate = $startDate;
        $serviceEndDate = $endDate;
      }
      if (!$serviceEndDate) {
        break;
      }
    }

    return array(
      'startDate' => $serviceStartDate,
      'endDate' => $serviceEndDate,
    );
  }

  /**
   * Return a difference of Service dates in days (including break days).
   * 
   * @param array   $serviceDates   Array containing 'startDate' and 'endDate' keys
   * @param string   $date          Date in Y-m-d format for which we calculate the result
   * @param int      $break         Allowed number of days between Contracts
   * 
   * @return int
   */
  protected static function calculateLength($serviceDates, $date, $break) {
    // Restrict $serviceEndDate to the specified date,
    // so we won't get an infinite Service length.
    if (!$serviceDates['endDate'] || $serviceDates['endDate'] > $date) {
      $serviceDates['endDate'] = $date;
    }
    // If the latest Contract has ended more than $break days ago, we return 0.
    if ($date > self::sumDateAndBreak($serviceDates['endDate'], $break)) {
      return 0;
    }

    $dateTimeStart = new DateTime($serviceDates['startDate']);
    $dateTimeEnd  = new DateTime($serviceDates['endDate']);
    $diff = $dateTimeStart->diff($dateTimeEnd);
    return $diff->days;
  }

  /**
   * Calculate a new Date which is sum of given Date and number of Break days.
   * Returns null if given date is null.
   * 
   * @param string  $date   Date in Y-m-d format
   * @param int     $break  Number of Break days
   * 
   * @return string|null
   */
  protected static function sumDateAndBreak($date, $break) {
    if (!$date) {
      return null;
    }
    $newDate = new DateTime($date);
    $newDate->add(new DateInterval('P' . $break . 'D'));
    return $newDate->format('Y-m-d');
  }
  
  /**
   * Update Length of Service for specific Contact.
   * 
   * @return bool
   */
  public static function updateLengthOfService($contactId) {
    // Get Length of Service's Custom Field ID.
    $customGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Contact_Length_Of_Service', 'id', 'name');
    $customField = civicrm_api3(
      'CustomField',
      'getsingle',
      array(
        'custom_group_id' => $customGroupID,
        'name' => 'Length_Of_Service'
      )
    );
    $customFieldID = $customField['id'];
    // Get Length of Service for the Contact.
    $lengthOfService = self::getLengthOfService($contactId);
    // Update the Length of Service for the Contact.
    civicrm_api3('Contact', 'create', array(
      'id' => $contactId,
      'custom_' . $customFieldID => $lengthOfService,
    ));
    return TRUE;
  }

  /**
   * Update Length of Service for all Individual Contacts.
   * 
   * @return bool
   */
  public static function updateLengthOfServiceAllContacts() {
    // Get all Individual Contacts.
    $contacts = civicrm_api3('Contact', 'get', array(
      'sequential' => 1,
      'contact_type' => 'Individual',
      'options' => array('limit' => 0),
    ));
    foreach ($contacts['values'] as $contact) {
      // Update the Length of Service of the Contact.
      self::updateLengthOfService($contact['id']);
    }
    return TRUE;

  }

  /**
   * Check Job Contract if exist   .
   *
   * @param Integer $contractID
   * @return array|0 ( return 0 if not exist or an array contain some contract details if exist )
   */
  public static function checkContract($contractID) {
    if ( !CRM_Utils_Rule::positiveInteger($contractID))  {
      return 0;
    }
    $queryParam = array(1 => array($contractID, 'Integer'));
    $query = "SELECT chrjcd.period_start_date, chrjcd.period_end_date from civicrm_hrjobcontract_revision chrjcr
              left join civicrm_hrjobcontract_details chrjcd on chrjcr.id=chrjcd.jobcontract_revision_id
              where  chrjcr.jobcontract_id = %1 order by chrjcr.id desc limit 1";
    $result = CRM_Core_DAO::executeQuery($query, $queryParam);
    return $result->fetch() ? $result : 0;
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

      $contactId = CRM_Utils_Array::value('contact_id', $fields);
      if($contactId) {
        $fields['contact_id'] = $contactId;
        $fields['contact_id']['title'] = CRM_Utils_Array::value('title', $contactId) . ' (match to contact)';
      }

      $fields = array_merge($fields, $tmpContactField);

      self::$_importableFields = $fields;
    return self::$_importableFields;//$fields;
  }
}
