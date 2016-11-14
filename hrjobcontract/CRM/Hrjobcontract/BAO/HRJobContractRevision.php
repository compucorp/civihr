<?php

class CRM_Hrjobcontract_BAO_HRJobContractRevision extends CRM_Hrjobcontract_DAO_HRJobContractRevision {

  static $_importableFields = array();

  /**
   * Create a new HRJobContractRevision based on array-data
   *
   * @param array $params key-value pairs
   *
   * @return CRM_Hrjobcontract_DAO_HRJobContractRevision|NULL
   *
   */
  public static function create($params) {
    global $user;

    if (!empty($user->uid)) {
        $params['editor_uid'] = $user->uid;
    }
    $className = 'CRM_Hrjobcontract_DAO_HRJobContractRevision';
    $entityName = 'HRJobContractRevision';
    $hook = empty($params['id']) ? 'create' : 'edit';

    $now = CRM_Utils_Date::currentDBDate();
    if ($hook === 'create')
    {
        $params['created_date'] = $now;
        $params['deleted'] = 0;
    }
    else
    {
        $params['modified_date'] = $now;
    }

    if (empty($params['jobcontract_id']) && $hook === 'edit') {
        $revision = new $className();
        $revision->id = $params['id'];
        $revision->find(TRUE);
        $params['jobcontract_id'] = $revision->jobcontract_id;
    }

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    if (!empty($instance->jobcontract_id)) {
      self::updateEffectiveEndDates($instance->jobcontract_id);
    }
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Builds and executes a query that return all the details of a given revision
   *
   * Note: as there could be multiple leave records, the leave data must be
   * fetched and processed separately
   *
   * @param  array $revision
   *   an assoc array representing an instance of HRJobContractRevision entity
   * @return array
   */
  public static function fullDetails($revision) {
    $query1 = self::buildFullDetailsQuery(['details', 'hour', 'pay', 'health', 'pension'], $revision);
    $query2 = self::buildFullDetailsQuery(['leave'], $revision);

    $result1 = CRM_Core_DAO::executeQuery($query1);
    $result2 = CRM_Core_DAO::executeQuery($query2);

    $result1->fetch();
    $fullDetails = self::normalizeFullDetailsResult($result1);

    while ($result2->fetch()) {
      $fullDetails['leave'][] = self::normalizeFullDetailsResult($result2)['leave'];
    }

    return $fullDetails;
  }

  /**
   * Update Revision's effective end dates for given Job Contract ID.
   *
   * @param int $jobcontractId
   */
  public static function updateEffectiveEndDates($jobcontractId) {
    $query = "SELECT id, effective_date FROM civicrm_hrjobcontract_revision " .
             "WHERE jobcontract_id = %1 " .
             "AND deleted = 0 " .
             "ORDER BY effective_date DESC, id DESC";
    $params = array(
      1 => array($jobcontractId, 'Integer'),
    );
    $revisions = CRM_Core_DAO::executeQuery($query, $params);
    $previousEffectiveDate = null;
    // Updating 'effective_end_date' of each revision by 'effective_date' - 1 DAY
    // of the next (newer) revision.
    while ($revisions->fetch()) {
      if ($previousEffectiveDate) {
        self::updateRevisionByEffectiveDates($revisions->id, $previousEffectiveDate, $revisions->effective_date);
      } else {
        self::clearEffectiveEndDate($revisions->id);
      }
      $previousEffectiveDate = $revisions->effective_date;
    }
  }

  /**
   * Validate if a given effective date isn't the equal
   * to any other contract revision effective date
   * for a given contact
   *
   * @param int $contactID Contact ID to validate against
   * @param string $effectiveDate Date in Y-m-d format
   *
   * @return array array in the following format :
   *   ['success' => bool, 'message' => string] Where :
   *     success : True when their is not revision with the same effective date otherwise FALSE.
   *     message : Error message if there is a conflicting revision.
   */
  public static function validateEffectiveDate($contactID, $effectiveDate) {
    $query =
      "SELECT hrjd.title FROM civicrm_hrjobcontract hrjc
      INNER JOIN civicrm_hrjobcontract_revision hrjr ON hrjc.id = hrjr.jobcontract_id
      INNER JOIN civicrm_hrjobcontract_details hrjd ON hrjd.jobcontract_revision_id = hrjr.details_revision_id
      WHERE hrjc.contact_id = %1
      AND hrjr.effective_date = %2
      AND hrjc.deleted = 0
      AND hrjr.deleted = 0
      LIMIT 1";
    $params = array(
      1 => array($contactID, 'Integer'),
      2 => array($effectiveDate, 'String'),
    );
    $revision = CRM_Core_DAO::executeQuery($query, $params);

    $conflictRevision['success'] = TRUE;
    $conflictRevision['message'] = '';

    if ($revision->fetch()) {
      $conflictRevision['success'] = FALSE;
      $conflictRevision['message'] =
        ts('A contract With the following title contain a revision with the same effective date : ')
        . '(' . $revision->title . ')';
    }

    return $conflictRevision;
  }

  static function importableFields($contactType = 'HRJobContractRevision',
    $status          = FALSE,
    $showAll         = FALSE,
    $isProfile       = FALSE,
    $checkPermission = TRUE,
    $withMultiCustomFields = FALSE
  ) {
    if (empty($contactType)) {
      $contactType = 'HRJobContractRevision';
    }

    $cacheKeyString = "";
    $cacheKeyString .= $status ? '_1' : '_0';
    $cacheKeyString .= $showAll ? '_1' : '_0';
    $cacheKeyString .= $isProfile ? '_1' : '_0';
    $cacheKeyString .= $checkPermission ? '_1' : '_0';

    $fields = CRM_Utils_Array::value($cacheKeyString, self::$_importableFields);

    if (!$fields) {
      $fields = CRM_Hrjobcontract_DAO_HRJobContractRevision::import();
      $fields = array_merge($fields, CRM_Hrjobcontract_DAO_HRJobContractRevision::import());

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

  /**
   * Helper function used in updateEffectiveEndDates() loop.
   * It updates revision entry with calculated 'effective_end_date'
   * and 'overrided' values basing on specified date of previously iterated
   * revision and date of currenly iterated revision.
   *
   * @param int $revisionId
   * @param string $previousEffectiveDate
   * @param string $currentEffectiveDate
   */
  protected static function updateRevisionByEffectiveDates($revisionId, $previousEffectiveDate, $currentEffectiveDate) {
    $overrided = 0;
    $effectiveEndDate = $currentEffectiveDate;
    if ($previousEffectiveDate === $currentEffectiveDate) {
      // If 'effective_date' is equal to previously iterated revision's
      // effective date we mark this revision as overrided by the one newly
      // created.
      $overrided = 1;
    } else {
      // Else, we set current's revision effective day to a day before the
      // previously iterated (newer) one.
      $effectiveEndDate = (new Datetime($previousEffectiveDate))->modify('-1 day')->format('Y-m-d');
    }
    $updateQuery = "UPDATE civicrm_hrjobcontract_revision SET " .
                   "effective_end_date = %1, " .
                   "overrided = %2 " .
                   "WHERE id = %3";
    $updateParams = array(
      1 => array($effectiveEndDate, 'String'),
      2 => array($overrided, 'Integer'),
      3 => array($revisionId, 'Integer'),
    );
    CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
  }

  /**
   * Set effective_end_date to NULL for given revision ID.
   *
   * @param int $revisionId
   */
  protected static function clearEffectiveEndDate($revisionId) {
    $clearEffectiveEndDateQuery = "UPDATE civicrm_hrjobcontract_revision SET " .
                                  "effective_end_date = NULL " .
                                  "WHERE id = %1";
    $clearEffectiveEndDateParams = array(
      1 => array($revisionId, 'Integer'),
    );
    CRM_Core_DAO::executeQuery($clearEffectiveEndDateQuery, $clearEffectiveEndDateParams);
  }

  /**
   * Builds the SQL query for fetching the revision details of the given entities
   *
   * The fields of each entities are dynamically listed in the SELECT clause
   * and are aliased with a prefix ({entity}__) so that it is possible to know
   * which entity they belong to (civicrm_hrjobcontract_pay.id -> pay__id)
   *
   * @param array $entities
   * @param array $revision
   */
  protected static function buildFullDetailsQuery($entities, $revision) {
    $query = ['select' => [], 'from' => [], 'where' => []];

    foreach ($entities as $entity) {
      $class = "CRM_Hrjobcontract_BAO_HRJob" . ucfirst($entity);
      $table = "civicrm_hrjobcontract_{$entity}";
      $fields = array_column($class::fields(), 'name');

      foreach ($fields as $field) {
        $query['select'][] = "{$table}.{$field} as {$entity}__{$field}";
      }

      $query['from'][] = $table;
      $query['where'][] = "{$table}.jobcontract_revision_id = " . $revision["{$entity}_revision_id"];
    }

    return sprintf(
      "SELECT %s FROM %s WHERE %s ",
      implode(', ', $query['select']),
      implode(', ', $query['from']),
      implode(' AND ', $query['where'])
    );
  }

  /**
   * Takes a flat list of revision details and rearranges it, by grouping
   * each field by the entity it belongs to
   *
   * The field's entity is inferred by the prefix ({entity}__) in the field's name
   *
   * @param  array $result
   * @return array
   */
  protected static function normalizeFullDetailsResult($result) {
    $normalized = [];

    foreach ($result as $key => $value) {
      if ($key[0] == '_' || $key == 'N') { continue; } // ignores "internal" fields

      list($entity, $field) = explode('__', $key);

      // This is necessary because some fields are stored in the DB as strings
      // although the content is actually a JSON. It is done automatically when
      // using the API, but when building the query manually the fields have to
      // be converted manually
      $normalized[$entity][$field] = self::isJSON($value) ? json_decode($value) : $value;
    }

    return $normalized;
  }

  /**
   * Determines if the given string is a JSON or not
   *
   * @param  string  $string
   * @return boolean
   */
  protected static function isJSON($string){
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
  }
}
