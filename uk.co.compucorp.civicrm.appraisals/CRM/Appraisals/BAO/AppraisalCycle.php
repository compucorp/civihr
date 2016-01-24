<?php

class CRM_Appraisals_BAO_AppraisalCycle extends CRM_Appraisals_DAO_AppraisalCycle
{
    static $_importableFields = array();
    
    /**
     * Create a new AppraisalCycle based on array-data
     *
     * @param array $params key-value pairs
     * @return CRM_Appraisals_DAO_AppraisalCycle|NULL
     */
    public static function create(&$params) {
        $className = 'CRM_Appraisals_DAO_AppraisalCycle';
        $entityName = 'AppraisalCycle';
        $hook = empty($params['id']) ? 'create' : 'edit';
        
        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
        $instance = new $className();
        $instance->copyValues($params);
        $instance->save();
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        
        if ($hook === 'edit') {
            self::populateDueDates($params);
        }
        
        return $instance;
    }
    
    /**
     * Populate change of any due date (self_appraisal_due, manager_appraisal_due, grade_due)
     * to all Appraisals of this Appraisal Cycle which have 'due_changed' = 0.
     */
    public static function populateDueDates(array $params) {
        $populateData = array();
        
        if (empty($params['id'])) {
            throw new Exception("Cannot populate Appraisal due dates with no Appraisal Cycle 'id' given.");
        }
        
        if (isset($params['self_appraisal_due'])) {
            $populateData['self_appraisal_due'] = $params['self_appraisal_due'];
        }
        if (isset($params['manager_appraisal_due'])) {
            $populateData['manager_appraisal_due'] = $params['manager_appraisal_due'];
        }
        if (isset($params['grade_due'])) {
            $populateData['grade_due'] = $params['grade_due'];
        }
        
        if (empty($populateData)) {
            return false;
        }

        $queryParams = array();
        $queryFieldSet = array();
        $i = 1;
        foreach ($populateData as $field => $value) {
            $queryFieldSet[] = $field . ' = %' . $i;
            $queryParams[$i++] = array($value, 'String');
        }
        $query = 'UPDATE civicrm_appraisal SET ' . implode(', ', $queryFieldSet) . ' WHERE appraisal_cycle_id = %' . $i . ' AND due_changed = 0';
        $queryParams[$i] = array($params['id'], 'Integer');
        CRM_Core_DAO::executeQuery($query, $queryParams);

        return true;
    }
    
    /**
     * Returns previous Cycle ID for given Manager ID
     * 
     * @param type $managerId
     * 
     * @return int
     */
    public static function getPreviousCycleId($managerId) {
        $query = 'SELECT ac.id FROM `civicrm_appraisal_cycle` ac
        INNER JOIN civicrm_appraisal a ON a.appraisal_cycle_id = ac.id
        WHERE ac.cycle_end_date < NOW() AND a.manager_id = %1
        ORDER BY ac.cycle_end_date DESC
        LIMIT 1';
        $params = array(
            1 => array($managerId, 'Integer'),
        );
        $result = CRM_Core_DAO::executeQuery($query, $params);
        if ($result->fetch()) {
            return $result->id;
        }
        return null;
    }
    
    /**
     * Returns current Cycle ID for given Manager ID
     * 
     * @param type $managerId
     * 
     * @return int
     */
    public static function getCurrentCycleId($managerId) {
        $query = 'SELECT ac.id FROM `civicrm_appraisal_cycle` ac
        INNER JOIN civicrm_appraisal a ON a.appraisal_cycle_id = ac.id
        WHERE ac.cycle_end_date > NOW() AND ac.cycle_start_date < NOW() AND a.manager_id = %1
        ORDER BY ac.cycle_start_date DESC
        LIMIT 1';
        $params = array(
            1 => array($managerId, 'Integer'),
        );
        $result = CRM_Core_DAO::executeQuery($query, $params);
        if ($result->fetch()) {
            return $result->id;
        }
        return null;
    }
    
    /**
     * Returns an array with all past and current Cycle IDs for given Manager ID and/or Contact ID
     * 
     * @param type $managerId
     * 
     * @return array
     */
    public static function getAllCycleIds($managerId = null, $contactId = null) {
        $params = array();
        $query = 'SELECT ac.id FROM `civicrm_appraisal_cycle` ac
        INNER JOIN civicrm_appraisal a ON a.appraisal_cycle_id = ac.id
        WHERE ac.cycle_start_date < NOW() ';
        if ($managerId) {
            $query .= ' AND a.manager_id = %1 ';
            $params[1] = array($managerId, 'Integer');
        }
        if ($contactId) {
            $query .= ' AND a.contact_id = %2 ';
            $params[2] = array($contactId, 'Integer');
        }
        $query .= ' GROUP BY ac.id ORDER BY ac.cycle_start_date ASC ';

        $data = array();
        $result = CRM_Core_DAO::executeQuery($query, $params);
        while ($result->fetch()) {
            $data[] = $result->id;
        }
        return $data;
    }
    
    /**
     * Returns an array of current Cycle status counters
     * 
     * @param type $managerId
     * 
     * @return int
     */
    public static function getCurrentCycleStatus($managerId) {
        $currentCycleId = self::getCurrentCycleId($managerId);
        if (!$currentCycleId) { // No current Cycle for given Manager ID.
            return null;
        }

        $data = array();
        $query = 'SELECT COUNT(id) AS appraisals_count, status_id FROM `civicrm_appraisal` 
        WHERE appraisal_cycle_id = %1 AND manager_id = %2 AND is_current = 1 
        GROUP BY status_id';
        $params = array(
            1 => array($currentCycleId, 'Integer'),
            2 => array($managerId, 'Integer'),
        );
        $result = CRM_Core_DAO::executeQuery($query, $params);
        while ($result->fetch()) {
            $data[$result->status_id] = $result->appraisals_count;
        }
        return $data;
    }
    
    /**
     * Returns average grade for for specific Cycle ID and optionally Manager ID
     * 
     * @param type $cycleId
     * @param type $managerId
     * @return type
     */
    public static function getCycleAverageGrade($cycleId, $managerId = null) {
        if (!$cycleId) {
            return null;
        }
        $params = array();
        $query = 'SELECT SUM(grade) / COUNT(id) AS average_grade FROM `civicrm_appraisal` 
        WHERE appraisal_cycle_id = %1 ';
        $params[1] = array($cycleId, 'Integer');
        if ($managerId) {
            $query .= ' AND manager_id = %2 ';
            $params[2] = array($managerId, 'Integer');
        }
        $query .= ' AND is_current = 1 AND grade IS NOT NULL';
        $result = CRM_Core_DAO::executeQuery($query, $params);
        if ($result->fetch()) {
            return $result->average_grade;
        }
        return null;
    }
    
    public static function getCurrentCycleAverageGrade($managerId) {
        $currentCycleId = self::getCurrentCycleId($managerId);
        return self::getCycleAverageGrade($currentCycleId, $managerId);
    }
    
    public static function getPreviousCycleAverageGrade($managerId) {
        $previousCycleId = self::getPreviousCycleId($managerId);
        return self::getCycleAverageGrade($previousCycleId, $managerId);
    }
    
    public static function getAllCyclesAverageGrade($managerId) {
        $cycleIds = self::getAllCycleIds($managerId);
        $averageGrades = array();
        foreach ($cycleIds as $cycleId) {
            $averageGrades[] = self::getCycleAverageGrade($cycleId, $managerId);
        }
        return array_sum($averageGrades) / count($averageGrades);
    }
    
    /**
     * Returns the Appraisal Cycle status overview
     * 
     * @param type $managerId
     * 
     * @return int
     */
    public static function getStatusOverview($currentDate, $cyclesIds, $startDate, $endDate) {
        $statuses = CRM_Core_OptionGroup::values('appraisal_status');
        $data = array();

        foreach ($statuses as $key => $value) {
            $data[$key] = array(
                'status_id' => $key,
                'status_name' => $value,
                'contacts_count' => array(
                    'due' => 0,
                    'overdue' => 0,
                ),
            );
        }
        $query = 'SELECT status_id, SUM(total) AS total, SUM(overdue) AS overdue FROM
        (
            SELECT a.status_id, COUNT(a.id) AS total,
                (
                    SELECT COUNT(a_overdue.id)
                    FROM civicrm_appraisal a_overdue
                    WHERE
                        a_overdue.appraisal_cycle_id = a.appraisal_cycle_id
                        AND a_overdue.status_id = a.status_id
                        %overdue_criteria
                ) AS overdue
            FROM civicrm_appraisal a
                INNER JOIN civicrm_appraisal_cycle ac ON ac.id = a.appraisal_cycle_id
            WHERE
                ac.cycle_is_active = 1
                %cycles_ids
                %period_criteria
            GROUP BY a.status_id, a.appraisal_cycle_id
            ORDER BY a.status_id ASC
        ) r
        GROUP BY status_id';

        $params = array();

        self::fillQueryPlaceholder($query, $params, array(
            'name' => 'cycles_ids',
            'type' => 'Text',
            'string' => 'AND ac.id IN (%paramIndex_1)',
            'values' => array($cyclesIds)
        ));
        self::fillQueryPlaceholder($query, $params, array(
            'name' => 'overdue_criteria',
            'type' => $currentDate === 'NOW()' ? 'Text' : 'String',
            'string' => self::dueDatesClause('a_overdue', $currentDate, '< %paramIndex_1'),
            'values' => array($currentDate)
        ));
        self::fillQueryPlaceholder($query, $params, array(
            'name' => 'period_criteria',
            'type' => 'String',
            'string' => self::dueDatesClause('a', $startDate, 'BETWEEN %paramIndex_1 AND %paramIndex_2'),
            'values' => array($startDate, $endDate)
        ));

        $result = CRM_Core_DAO::executeQuery($query, $params);

        while ($result->fetch()) {
            $data[$result->status_id]['contacts_count']['due'] = (int)$result->total - (int)$result->overdue;
            $data[$result->status_id]['contacts_count']['overdue'] = (int)$result->overdue;
        }

        return $data;
    }

    /**
     * Fills a placeholder in the given query string using the options passed
     *
     * @param {string} $query - The query containing the placeholder
     * @param {Array} $params - The array used by CRM_Core_DAO::executeQuery to
     *     interpolate the values in the query before executing it
     * @param {Array} $options - An array with following properties:
     *     name - The name of the placeholder that needs to be replaced
     *     string - The string that replaces the placeholder. The string must
     *       contain a placeholder %paramIndex_<index> (i.e.: $paramIndex_1)
     *       that will be used by CRM_Core_DAO::executeQuery to fill in the real value
     *     values - An array of value that CRM_Core_DAO::executeQuery will
     *       use in the final query
     *     type - The type of the values used by CRM_Core_DAO::executeQuery
     */
    private function fillQueryPlaceholder(&$query, &$params, $options) {
        $replacement = '';

        if (!empty(array_filter($options['values']))) {
            $replacement = $options['string'];

            foreach ($options['values'] as $key => $value) {
                $paramIndex = count($params) + 1;
                $params[$paramIndex] = array($value, $options['type']);

                $replacement = str_replace("%paramIndex_" . ($key + 1), "%$paramIndex", $replacement);
            }
        }

        $query = str_replace("%$options[name]", $replacement, $query);
    }

    /**
     * Returns a string with the WHERE clause related to all due dates
     *
     * @param {string} $appraisalAlias - The alias to use for the appraisals column
     * @param {string} $targetDate - The date to use for the clause. If null, no clause is returned
     * @param {string} $comparison - The comparison to do against the $targetDate
     * @return {string}
     */
    private function dueDatesClause($appraisalAlias, $targetDate, $comparison) {
        if (!$targetDate) {
            return null;
        }

        return "AND (($appraisalAlias.status_id = 1 AND $appraisalAlias.self_appraisal_due $comparison) OR " .
          "($appraisalAlias.status_id = 2 AND $appraisalAlias.manager_appraisal_due $comparison) OR " .
          "($appraisalAlias.status_id = 3 AND $appraisalAlias.grade_due $comparison))";
    }

    public static function getAppraisalsPerStep($appraisalCycleId, $includeAppraisals = false) {
        $statuses = CRM_Core_OptionGroup::values('appraisal_status');
        $data = array();
        $query = 'SELECT a.status_id, COUNT(a.id) AS counter FROM civicrm_appraisal a
        WHERE a.appraisal_cycle_id = %1
        AND a.is_current = 1
        GROUP BY a.status_id
        ORDER BY a.status_id';
        $params = array(
            1 => array($appraisalCycleId, 'Integer'),
        );
        $result = CRM_Core_DAO::executeQuery($query, $params);

        // Sets up default values for each available step
        foreach ($statuses as $id => $name) {
            $data[$id] = array(
              'status_id' => (string)$id,
              'status_name' => $name,
              'appraisals_count' => 0,
              'appraisals' => array()
            );
        }

        while ($result->fetch()) {
            // Merges actual values to override default ones
            $data[$result->status_id] = array_merge($data[$result->status_id], array(
                'status_id' => $result->status_id,
                'status_name' => $statuses[$result->status_id],
                'appraisals_count' => $result->counter,
            ));

            if ($includeAppraisals) {
                $appraisalsResult = civicrm_api3('Appraisal', 'get', array(
                    'sequential' => 1,
                    'appraisal_cycle_id' => $appraisalCycleId,
                    'status_id' => $result->status_id,
                    'is_current' => 1,
                ));
                $data[$result->status_id]['appraisals'] = $appraisalsResult['values'];
            }
        }
        return $data;
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
  static function importableFields($contactType = 'Individual',
    $status          = FALSE,
    $showAll         = FALSE,
    $isProfile       = FALSE,
    $checkPermission = TRUE,
    $withMultiCustomFields = FALSE
  ) {
    $cacheKeyString = "";
    $cacheKeyString .= $status ? '_1' : '_0';
    $cacheKeyString .= $showAll ? '_1' : '_0';
    $cacheKeyString .= $isProfile ? '_1' : '_0';
    $cacheKeyString .= $checkPermission ? '_1' : '_0';
    
    $contactType = 'Individual';

    $fields = CRM_Utils_Array::value($cacheKeyString, self::$_importableFields);
    
    if (!$fields) {
      $fields = CRM_Appraisals_DAO_AppraisalCycle::import();

      $tmpContactField = $contactFields = array();
      
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
