<?php

class CRM_Hrjobcontract_BAO_HRJobContract extends CRM_Hrjobcontract_DAO_HRJobContract {

    static $_importableFields = array();

  /**
   * Create a new HRJobContract based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Hrjobcontract_DAO_HRJobContract|NULL
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
    $instance->find(true);
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    if ((is_numeric(CRM_Utils_Array::value('is_primary', $params)) || $hook === 'create') && empty($params['import'])) {
        CRM_Hrjobcontract_DAO_HRJobContract::handlePrimary($instance, $params);
    }

    $deleted = isset($params['deleted']) ? $params['deleted'] : 0;
    if ($deleted)
    {
        CRM_Hrjobcontract_JobContractDates::removeDates($instance->id);
        self::updateLengthOfService($instance->contact_id);
    }

    if (function_exists('module_exists') && module_exists('rules')) {
        rules_invoke_event('hrjobcontract_after_create', $instance);
    }

    return $instance;
  }

  /**
   * Delete current HRJobContract based on array-data
   *
   * @param array|boolean $useWhere
   * @return CRM_Hrjobcontract_DAO_HRJobContract|NULL
   */
  public function delete($useWhere = false) {
      $id = $this->id;
      $result = parent::delete($useWhere);
      if ($result !== false && is_callable('module_exists') && module_exists('rules')) {
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
   * Calculate 'length_of_service' in days for given Contact ID, and optionally
   * Date and Break (allowed number of days between Contracts).
   *
   * @param int     $contactId  CiviCRM Contact ID
   * @param string  $date       Y-m-d format of a date for which we calculate the result
   * @param int     $break      Allowed number of days between Contracts
   *
   * @throws Exception
   * @return int
   */
  public static function calculateLengthOfService($contactId, $date = null, $break = 14) {
    if (empty($contactId))
    {
      throw new Exception("Cannot get Length of Service: no Contact ID provided.");
    }

    // Setting $date to today if it's not specified.
    if (!$date) {
      $date = date('Y-m-d');
    }

    // Getting all Job Contracts for given Contact ID.
    $contracts = self::getContactContracts($contactId);

    return self::calculateLength(
      self::getServiceDates(
        self::getContractDates($contracts, $date),
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
      'deleted' => 0,
      'options' => array('limit' => 0),
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
   * Return an associative array with Contracts dates in format below:
   * [
   *   'start_date1' => 'end_date1',
   *   'start_date2' => 'end_date2',
   *   'start_date3' => 'end_date3',
   * ];
   *
   * @param array $contracts
   * @param string $date Y-m-d format of a date for which we calculate the result 
   *
   * @return array
   */
  protected static function getContractDates($contracts, $date) {
    $dates = array();
    foreach ($contracts as $contract) {
      if ($contract['period_start_date'] <= $date) {
        $dates[$contract['period_start_date']] = !empty($contract['period_end_date']) ? $contract['period_end_date']  : null;
      }
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
    if (empty($serviceDates['startDate'])) {
      return 0;
    }
    // Restrict $serviceEndDate to the specified date,
    // so we won't get an infinite Service length.
    if (!$serviceDates['endDate'] || $serviceDates['endDate'] > $date) {
      $serviceDates['endDate'] = $date;
    }

    $dateTimeStart = new DateTime($serviceDates['startDate']);
    $dateTimeEnd  = new DateTime($serviceDates['endDate']);
    $diff = $dateTimeStart->diff($dateTimeEnd);
    return $diff->days + 1;
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
   * @param int $contactId
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
    $lengthOfService = self::calculateLengthOfService($contactId);
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
   * Get Length of Service value in days for specific Contact ID.
   * 
   * @param type $contactId
   * @return int
   */
  public static function getLengthOfService($contactId) {
    $result = CRM_Core_DAO::executeQuery(
      'SELECT length_of_service FROM `civicrm_value_length_of_service_11` WHERE entity_id = %1 LIMIT 1',
      array(
        1 => array($contactId, 'Integer'),
      )
    );
    if ($result->fetch()) {
      return (int)$result->length_of_service;
    }
    return 0;
  }

  /**
   * Get an assotiative array of days, months and years counted for
   * specific Contact ID.
   * 
   * @param int contactId
   * @return array
   */
  public static function getLengthOfServiceYmd($contactId) {
    $lengthOfService = self::getLengthOfService($contactId);
    $today = new DateTime();
    $past = (new DateTime())->sub(new DateInterval('P' . $lengthOfService . 'D'));
    $interval = $today->diff($past);
    return array(
      'days' => (int)$interval->format('%d'),
      'months' => (int)$interval->format('%m'),
      'years' => (int)$interval->format('%y'),
    );
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
   * Get the total number of staff (Individual contacts with active contracts).
   *
   * @return int
   */
  public static function getStaffCount() {

    $currentDate = date('Y-m-d');

    $query = "
      SELECT COUNT(DISTINCT c.id) count
      FROM civicrm_contact c
      LEFT JOIN civicrm_hrjobcontract hrjc ON (c.id = hrjc.contact_id)
      LEFT JOIN civicrm_hrjobcontract_revision hrjr ON hrjr.jobcontract_id = hrjc.id
      LEFT JOIN civicrm_hrjobcontract_details hrjd
      ON hrjr.details_revision_id = hrjd.jobcontract_revision_id
      WHERE c.contact_type = 'Individual'
      AND hrjd.period_start_date <= '{$currentDate}'
      AND ( hrjd.period_end_date >= '{$currentDate}' OR hrjd.period_end_date IS NULL)
      AND c.is_deleted = 0
      AND hrjc.deleted = 0
      AND hrjr.deleted = 0";

    $total = 0;

    $dao = CRM_Core_DAO::executeQuery($query);
    if ($dao->fetch()) {
      $total = $dao->count;
    }

    return $total;
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

  /**
   * Returns a list of active contracts.
   *
   * If no startDate is given, the current date will be used. If no endDate is
   * given, the startDate will be used for it.
   *
   * A contract is active if:
   * - The effective date of current revision is less or equal the startDate OR
   *   it is starts someday between the start and end dates
   * - The contract start date and end dates overlaps with the start and end
   *   dates passed to the period OR
   *   it doesn't have an end date and starts before the given start date OR
   *   it doesn't have an end date and starts between the given start and end
   *   dates
   * - The contract is not deleted
   *
   * @param null $startDate
   * @param null $endDate
   *
   * @return array
   */
  public static function getActiveContracts($startDate = null, $endDate = null)
  {
    if($startDate) {
      $startDate = CRM_Utils_Date::processDate($startDate, null, false, 'Y-m-d');
    } else {
      $startDate = date('Y-m-d');
    }

    if($endDate) {
      $endDate = CRM_Utils_Date::processDate($endDate, null, false, 'Y-m-d');
    } else {
      $endDate = $startDate;
    }

    $query = "
      SELECT c.*
      FROM civicrm_hrjobcontract c
        INNER JOIN civicrm_hrjobcontract_revision r
          ON r.id = (SELECT id
                     FROM civicrm_hrjobcontract_revision r2
                     WHERE
                      r2.jobcontract_id = c.id AND
                      (
                        r2.effective_date <= '{$startDate}'
                         OR
                        ( r2.effective_date >= '{$startDate}' AND
                          r2.effective_date <= '{$endDate}'
                        )
                      )
                     ORDER BY r2.effective_date DESC, r2.id DESC
                     LIMIT 1
        )
        INNER JOIN civicrm_hrjobcontract_details d ON d.jobcontract_revision_id = r.id
      WHERE c.deleted = 0 AND
        (
          (d.period_end_date IS NOT NULL AND d.period_start_date <= '{$endDate}' AND d.period_end_date >= '{$startDate}')
            OR
          (d.period_end_date IS NULL
            AND
            (
              (d.period_start_date >= '{$startDate}' AND d.period_start_date <= '{$endDate}')
              OR
              d.period_start_date <= '{$endDate}'
            )
          )
        );
    ";

    $dao = CRM_Core_DAO::executeQuery($query);
    $contracts = [];
    while($dao->fetch()) {
      $contracts[] = [
        'id' => $dao->id,
        'contact_id' => $dao->contact_id,
        'id_primary' => $dao->is_primary,
        'deleted' => $dao->deleted
      ];
    }

    return $contracts;
  }

  /**
   * Return the current revision for current contract for the contact if exist.
   * which is the contract with current revision start date is
   * on or before the current date and end date
   * (is more than or equal the current date) or (null/empty).
   *
   * also note :
   * 1) two contracts can't overlap.
   * 2) two revision can't have the same effective date (not implement yet -
   *  but when it does ( TODO: remove create_date DESC from the query ) ).
   *
   * @param int $contactID
   * @return array|null
   */
  public static function getCurrentContract($contactID)  {
    $currentDate = date('Y-m-d');
    $queryParam = array(1 => array($contactID, 'Integer'));
    $query = "SELECT hrjc.id as contract_id , hrjd.*
      FROM civicrm_hrjobcontract hrjc
      LEFT JOIN civicrm_hrjobcontract_revision hrjr
      ON hrjr.jobcontract_id = hrjc.id
      LEFT JOIN civicrm_hrjobcontract_details hrjd
      ON hrjr.details_revision_id = hrjd.jobcontract_revision_id
      WHERE hrjc.contact_id = %1
      AND hrjr.effective_date <= '{$currentDate}'
      AND hrjc.deleted = 0
      AND hrjr.deleted = 0
      ORDER BY hrjr.effective_date DESC , created_date DESC
      LIMIT 1";
    $response = CRM_Core_DAO::executeQuery($query, $queryParam);
    if ($response->fetch())  {
      if (empty($response->period_end_date) || $response->period_end_date >= $currentDate )  {
        $baoName = 'CRM_Hrjobcontract_BAO_HRJobDetails';
        $response->location = CRM_Core_Pseudoconstant::getLabel($baoName, 'location', $response->location);
        return $response;
      }
    }
    return null;
  }

  /**
   * Permanently delete all contracts for given contact ID.
   *
   * @param int $contactId
   *
   * @return boolean
   * @throw Exception
   */
  public static function deleteAllContractsPermanently($contactId) {

    if (empty($contactId)) {
      throw new Exception('Please specify contact ID.');
    }

    $contract = new self();
    $contract->contact_id = $contactId;
    $contract->find();
    while ($contract->fetch()) {
      self::deleteContractPermanently($contract->id);
    }

    return TRUE;
  }

  /**
   * Permanently delete whole contract with its all revisions and entities.
   *
   * @param int $contractId
   *
   * @return bool
   * @throw Exception
   */
  private static function deleteContractPermanently($contractId) {

    if (empty($contractId)) {
      throw new Exception('Please specify contract ID to delete.');
    }

    $transaction = new CRM_Core_Transaction();
    try {
      $contract = new self();
      $contract->id = $contractId;
      if (!$contract->find(TRUE)) {
        throw new Exception('Cannot find Job Contract with specified ID.');
      }

      $contactId = $contract->contact_id;
      $revision = new CRM_Hrjobcontract_BAO_HRJobContractRevision();
      $revision->jobcontract_id = $contract->id;
      $revision->find();

      while ($revision->fetch()) {
        self::deleteRevisionPermanently($revision);
      }

      $contract->delete();
      CRM_Hrjobcontract_JobContractDates::removeDates($contractId);
      self::updateLengthOfService($contactId);
    } catch(Exception $e) {
      $transaction->rollback();
      throw new Exception($e);
    }

    return TRUE;
  }

  /**
   * Delete all contract entities of given revision and the revision itself.
   *
   * @param CRM_Hrjobcontract_BAO_HRJobContractRevision $revision
   */
  private static function deleteRevisionPermanently(CRM_Hrjobcontract_BAO_HRJobContractRevision $revision) {

    $entityNames = [
      'HRJobDetails' => 'details',
      'HRJobHealth' => 'health',
      'HRJobHour' => 'hour',
      'HRJobLeave' => 'leave',
      'HRJobPay' => 'pay',
      'HRJobPension' => 'pension',
      'HRJobRole' => 'role',
    ];

    foreach ($entityNames as $entityName => $prefix) {
      self::deleteEntityRevisionPermanently('CRM_Hrjobcontract_BAO_' . $entityName, $revision->{$prefix . '_revision_id'});
    }

    $deleteRevision = new CRM_Hrjobcontract_BAO_HRJobContractRevision();
    $deleteRevision->id = $revision->id;
    $deleteRevision->delete();
  }

  /**
   * Delete each entity entry of specified revision ID.
   *
   * @param string $className
   * @param int $revisionId
   */
  private static function deleteEntityRevisionPermanently($className, $revisionId) {

    $entity = new $className();
    $entity->jobcontract_revision_id = $revisionId;
    $entity->find();

    while ($entity->fetch()) {
      self::deleteEntityPermanently($className, $entity->id);
    }
  }

  /**
   * Delete a single entity entry of given class and ID.
   *
   * @param string $className
   * @param int $entityId
   */
  private static function deleteEntityPermanently($className, $entityId) {
    $deleteEntity = new $className();
    $deleteEntity->id = $entityId;
    $deleteEntity->delete();
  }
}
