<?php

class CRM_Hrjobcontract_BAO_HRJobDetails extends CRM_Hrjobcontract_DAO_HRJobDetails {

    static $_importableFields = array();

    /**
     * Create a new HRJobDetails based on array-data
     *
     * @param array $params key-value pairs
     * @return CRM_Hrjobcontract_DAO_HRJobDetails|NULL
     *
     */
    public static function create($params) {
        $hook = empty($params['id']) ? 'create' : 'edit';
        $previousDetailsRevisionId = null;

        if ($hook == 'create') {
            $previousRevisionResult = civicrm_api3('HRJobContractRevision', 'getcurrentrevision', array(
              'sequential' => 1,
              'jobcontract_id' => $params['jobcontract_id'],
            ));
            if (!empty($previousRevisionResult['values']['details_revision_id'])) {
                $previousDetailsRevisionId = $previousRevisionResult['values']['details_revision_id'];
            }
        }

        $instance = parent::create($params);

        $revisionResult = civicrm_api3('HRJobContractRevision', 'get', array(
            'sequential' => 1,
            'id' => $instance->jobcontract_revision_id,
        ));
        $revision = CRM_Utils_Array::first($revisionResult['values']);

        $duplicate = CRM_Utils_Array::value('action', $params, $hook);
        if ($hook == 'create' && empty($revision['role_revision_id']) && $duplicate != 'duplicate' && empty($params['import'])) {
            //civicrm_api3('HRJobRole', 'create', array('jobcontract_id' => $revision['jobcontract_id'],'title' => $instance->title, 'location'=> $instance->location, 'percent_pay_role' => 100, 'jobcontract_revision_id' => $instance->jobcontract_revision_id));
            CRM_Hrjobcontract_BAO_HRJobRole::create(array('jobcontract_id' => $revision['jobcontract_id'],'title' => $instance->title, 'location'=> $instance->location, 'percent_pay_role' => 100, 'jobcontract_revision_id' => $instance->jobcontract_revision_id));
        }

        if ($previousDetailsRevisionId) {
            CRM_Core_BAO_File::copyEntityFile('civicrm_hrjobcontract_details', $previousDetailsRevisionId, 'civicrm_hrjobcontract_details', $revision['details_revision_id']);
        }

        $contract = new CRM_Hrjobcontract_DAO_HRJobContract();
        $contract->id = $revision['jobcontract_id'];
        $contract->find(true);
        CRM_Hrjobcontract_JobContractDates::setDates($contract->contact_id, $revision['jobcontract_id'], $instance->period_start_date, $instance->period_end_date);
        CRM_Hrjobcontract_BAO_HRJobContract::updateLengthOfService($contract->contact_id);

        return $instance;
    }

  /**
   * Get a count of records with the given property
   *
   * @param $params
   * @return int
   */
  public static function getRecordCount($params) {
    $dao = new CRM_Hrjobcontract_DAO_HRJobDetails();
    $dao->copyValues($params);
    return $dao->count();
  }

  /**
   * Check if given Contract start and end dates are available for given Contact.
   *
   * @param array $params key-value pairs
   * @return bool
   * @throws CiviCRM_API3_Exception
   */
  public static function validateDates(array $params) {
    if (empty($params['contact_id'])) {
      throw new CiviCRM_API3_Exception("Please specify 'contact_id' value.");
    }
    if (empty($params['period_start_date'])) {
      throw new CiviCRM_API3_Exception("Please specify 'period_start_date' value.");
    }
    $contactId = $params['contact_id'];
    $periodStartDate = date('Y-m-d', strtotime($params['period_start_date']));
    $periodEndDate = !empty($params['period_end_date']) ? date('Y-m-d', strtotime($params['period_end_date'])) : NULL;
    $jobContractId = !empty($params['jobcontract_id']) ? $params['jobcontract_id'] : null;
    $conflictingContracts = self::getConflictingContracts($contactId, $periodStartDate, $periodEndDate, $jobContractId);
    if (empty($conflictingContracts)) {
      return array(
        'success' => TRUE,
        'message' => NULL,
        'conflicting_contracts' => NULL,
      );
    }
    return array(
      'success' => FALSE,
      'message' => self::getConflictMessage($conflictingContracts),
      'conflicting_contracts' => $conflictingContracts,
    );
  }

  /**
   * Return an array of Job Contracts conflicting with given Contact ID,
   * Period Start Date, Period End Date and optional Job Contract Id.
   *
   * @param int $contactId
   * @param string $periodStartDate
   * @param string $periodEndDate
   * @param int $jobContractId
   * @return array
   */
  private static function getConflictingContracts($contactId, $periodStartDate, $periodEndDate, $jobContractId = null) {
    $conflictingContracts = array();
    $conflictingContractsQuery = "
      SELECT jc.id, jcd.title, jcd.period_start_date, jcd.period_end_date, jcd.jobcontract_revision_id, jcr.effective_date, jcr.effective_end_date
      FROM civicrm_hrjobcontract jc
      LEFT JOIN civicrm_hrjobcontract_revision jcr ON jcr.jobcontract_id = jc.id
      LEFT JOIN civicrm_hrjobcontract_details jcd ON jcd.jobcontract_revision_id = jcr.details_revision_id
      WHERE jc.contact_id = %1
      AND jc.deleted = 0
      AND jcr.deleted = 0
      AND jcr.overrided = 0
    ";
    if ($periodEndDate === NULL) {
      $conflictingContractsQuery .= "
        AND
        (
          (
            %2 <= jcd.period_start_date
          )
          OR
          (
            %2 > jcd.period_start_date
            AND
            (
              jcd.period_end_date IS NULL
              OR
              jcd.period_end_date >= %2
            )
            AND
            (
              jcr.effective_end_date IS NULL
              OR
              jcr.effective_end_date >= %2
            )
          )
        )
      ";
    } else {
      $conflictingContractsQuery .= "
        AND
        (
          (
            (jcd.period_start_date BETWEEN %2 AND %3)
            OR (jcd.period_end_date IS NOT NULL AND (jcd.period_end_date BETWEEN %2 AND %3))
            OR (%2 <= jcd.period_start_date AND (jcd.period_end_date IS NOT NULL AND %3 >= jcd.period_end_date))
            OR (%2 >= jcd.period_start_date AND (jcd.period_end_date IS NOT NULL AND %3 <= jcd.period_end_date))
            OR (jcd.period_end_date IS NULL AND jcd.period_start_date <= %3)
          )
          AND
          (
            (jcr.effective_date BETWEEN %2 AND %3)
            OR (jcr.effective_end_date IS NOT NULL AND (jcr.effective_end_date BETWEEN %2 AND %3))
            OR (%2 <= jcr.effective_date AND (jcr.effective_end_date IS NOT NULL AND %3 >= jcr.effective_end_date))
            OR (%2 >= jcr.effective_date AND (jcr.effective_end_date IS NOT NULL AND %3 <= jcr.effective_end_date))
            OR (jcr.effective_end_date IS NULL AND jcr.effective_date <= %3)
          )
        )
      ";
    }
    $conflictingContractsParams = array(
      1 => array($contactId, 'Integer'),
      2 => array($periodStartDate, 'String'),
      3 => array($periodEndDate . '', 'String'),
    );
    if ($jobContractId) {
      $conflictingContractsQuery .= " AND jc.id <> %4 ";
      $conflictingContractsParams[4] = array($jobContractId, 'Integer');
    }
    $conflictingContractsResult = CRM_Core_DAO::executeQuery($conflictingContractsQuery, $conflictingContractsParams);
    while ($conflictingContractsResult->fetch()) {
      $conflictingContracts[] = array(
        'contract_id' => $conflictingContractsResult->id,
        'title' => $conflictingContractsResult->title,
        'period_start_date' => $conflictingContractsResult->period_start_date,
        'period_end_date' => $conflictingContractsResult->period_end_date,
        'jobcontract_revision_id' => $conflictingContractsResult->jobcontract_revision_id,
        'effective_date' => $conflictingContractsResult->effective_date,
        'effective_end_date' => $conflictingContractsResult->effective_end_date,
      );
    }
    return $conflictingContracts;
  }

  /**
   * Return string containing Job Contract(s) conflict with listed details
   * about conflicted Job Contract titles and Revision effective dates.
   *
   * @param array $conflictingContracts
   * @return string
   */
  private static function getConflictMessage(array $conflictingContracts) {
    $message = "Unable to save. Staff can only have one current contract and the start or end date of this contract overlaps another contract:";
    $conflictLines = array();
    foreach ($conflictingContracts as $conflict) {
      $conflictLines[] = "Contract entitled \"{$conflict['title']}\", revision with {$conflict['effective_date']} effective date";
    }
    $message .= '<br/>' . implode(';<br/>', $conflictLines);
    return $message;
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
  static function importableFields($contactType = 'HRJobDetails',
    $status          = FALSE,
    $showAll         = FALSE,
    $isProfile       = FALSE,
    $checkPermission = TRUE,
    $withMultiCustomFields = FALSE
  ) {
    if (empty($contactType)) {
      $contactType = 'HRJobDetails';
    }

    $cacheKeyString = "";
    $cacheKeyString .= $status ? '_1' : '_0';
    $cacheKeyString .= $showAll ? '_1' : '_0';
    $cacheKeyString .= $isProfile ? '_1' : '_0';
    $cacheKeyString .= $checkPermission ? '_1' : '_0';

    $fields = CRM_Utils_Array::value($cacheKeyString, self::$_importableFields);
    $fields = null;

    if (!$fields) {
      $fields = CRM_Hrjobcontract_DAO_HRJobDetails::import();

      $fields = array_merge($fields, CRM_Hrjobcontract_DAO_HRJobDetails::import());

      //Sorting fields in alphabetical order(CRM-1507)
      $fields = CRM_Utils_Array::crmArraySortByField($fields, 'title');
      $fields = CRM_Utils_Array::index(array('name'), $fields);
      CRM_Core_BAO_Cache::setItem($fields, 'contact fields', $cacheKeyString);
     }

    self::$_importableFields[$cacheKeyString] = $fields;

    if (!$isProfile) {
        $fields = array_merge(array('do_not_import' => array('title' => ts('- do not import -'))),
          self::$_importableFields[$cacheKeyString]
        );
    }
    return $fields;
  }

}
