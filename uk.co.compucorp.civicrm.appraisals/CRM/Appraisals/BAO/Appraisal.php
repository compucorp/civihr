<?php

class CRM_Appraisals_BAO_Appraisal extends CRM_Appraisals_DAO_Appraisal
{
    static $_importableFields = array();
    
    /**
     * Create a new Appraisal based on array-data
     *
     * @param array $params key-value pairs
     * @return CRM_Appraisals_DAO_Appraisal|NULL
     */
    public static function create(&$params) {
        $className = 'CRM_Appraisals_DAO_Appraisal';
        $entityName = 'Appraisal';
        $hook = empty($params['id']) ? 'create' : 'edit';
        $now = CRM_Utils_Date::currentDBDate();
        
        if ($hook === 'create') {
            self::validateNewAppraisal($params);

            $appraisalCycle = civicrm_api3('AppraisalCycle', 'getsingle', array(
                'sequential' => 1,
                'id' => $params['appraisal_cycle_id'],
            ));
            if (empty($params['self_appraisal_due'])) {
                $params['self_appraisal_due'] = $appraisalCycle['cycle_self_appraisal_due'];
            }
            if (empty($params['manager_appraisal_due'])) {
                $params['manager_appraisal_due'] = $appraisalCycle['cycle_manager_appraisal_due'];
            }
            if (empty($params['grade_due'])) {
                $params['grade_due'] = $appraisalCycle['cycle_grade_due'];
            }
            
            if (empty($params['status_id'])) {
                $params['status_id'] = 1;
            }
        } else {
            $instance = new $className();
            $instance->id = (int)$params['id'];
            if (!$instance->find()) {
                throw new Exception("Cannot find Appraisal with 'id' = " . (int)$params['id'] . '.');
            }
            
            $instance->fetch();
            
            $copy = CRM_Appraisals_DAO_Appraisal::copyGeneric($className, array('id' => (int)$params['id']));
            $copy->is_current = 0;
            $copy->save();
            
            $dueChanged = false;
            if (!empty($params['self_appraisal_due']) && $params['self_appraisal_due'] != $instance->self_appraisal_due) {
                $dueChanged = true;
            }
            if (!empty($params['manager_appraisal_due']) && $params['manager_appraisal_due'] != $instance->manager_appraisal_due) {
                $dueChanged = true;
            }
            if (!empty($params['grade_due']) && $params['grade_due'] != $instance->grade_due) {
                $dueChanged = true;
            }
            
            if ($dueChanged) {
                $instance->due_changed = 1;
                $instance->save();
            }
        }
        
        CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
        $instance = new $className();
        $instance->copyValues($params);
        $instance->save();
        if (empty($params['original_id'])) {
            $instance->original_id = $instance->id;
        }
        if (empty($params['created_date'])) {
            $instance->created_date = $now;
        }
        $instance->save();
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        ////TODO: trigger on post: CRM_Tasksassignments_Reminder::sendReminder((int)$instance->id);
        
        return $instance;
    }

    /**
     * Check if a new Appraisal can be created with given parameters.
     * Return TRUE or throw an exception containing error info.
     * 
     * @param array $params
     * @return boolean
     * @throws Exception
     */
    protected static function validateNewAppraisal(array $params) {
      // Checking for Appraisal Cycle ID parameter.
      if (empty($params['appraisal_cycle_id'])) {
        throw new Exception("Please specify 'appraisal_cycle_id' value to create Appraisal.");
      }
      // Checking if Appraisal Cycle exists with given Cycle ID.
      $appraisalCycle = civicrm_api3('AppraisalCycle', 'getsingle', array(
        'sequential' => 1,
        'id' => $params['appraisal_cycle_id'],
      ));
      if (!empty($appraisalCycle['is_error']) && (int)$appraisalCycle['is_error']) {
        throw new Exception("Cannot find Appraisal Cycle with 'id' = {$params['appraisal_cycle_id']}.");
      }
      // Checking if there is current Appraisal already existing for given Cycle ID and Contact ID.
      if (!empty($params['contact_id'])) {
        $appraisal = civicrm_api3('Appraisal', 'getcount', array(
          'appraisal_cycle_id' => $params['appraisal_cycle_id'],
          'contact_id' => $params['contact_id'],
          'is_current' => 1,
        ));
        if ((int)$appraisal) {
          throw new Exception("Specified Contact already has an Appraisal with given Appraisal Cycle ID.");
        }
      }
      // If any requirements are met then return TRUE.
      return TRUE;
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
      $fields = CRM_Appraisals_DAO_Appraisal::import();

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
  
  public static function filter($params) {
      $roleContactsIds = array();
      if (CRM_Core_DAO::checkTableExists('civicrm_hrjobroles') && CRM_Core_DAO::checkTableExists('civicrm_hrjobcontract')) {
        $availableTags = array('department', 'level_type', 'region', 'location');
        $rolesContactsWhere = array();
        foreach ($availableTags as $tag) {
          if (!empty($params['tags'][$tag])) {
              $rolesContactsWhere[] = "r.{$tag} IN (" . implode(', ', array_map('intval', $params['tags'][$tag])) . ")";
          }
        }
        
        if (!empty($rolesContactsWhere)) {
            $rolesContactsWhere[] = "hrjc.deleted = 0";
            $rolesContactsQuery = "SELECT c.id, c.sort_name FROM civicrm_contact c "
              . "INNER JOIN civicrm_hrjobcontract hrjc ON hrjc.contact_id = c.id "
              . "INNER JOIN civicrm_hrjobroles r ON r.job_contract_id = hrjc.id "
              . "WHERE " . implode(" AND ", $rolesContactsWhere);
            
            $rolesContactsResult = CRM_Core_DAO::executeQuery($rolesContactsQuery);

            while ($rolesContactsResult->fetch()) {
                $roleContactsIds[] = $rolesContactsResult->id;
            }
            
            if (!empty($params['contact_id'])) {
                if (!in_array((int)$params['contact_id'], $roleContactsIds)) {
                    return array();
                }
                $roleContactsIds = array((int)$params['contact_id']);
            }
        }
      }
      
      if (!empty($params['contact_id']) && empty($roleContactsIds)) {
          $roleContactsIds = array((int)$params['contact_id']);
      }
      
      $getParams = array();
      if (!empty($params['appraisal_cycle_id'])) {
          $getParams['appraisal_cycle_id'] = (int)$params['appraisal_cycle_id'];
      }
      if (!empty($roleContactsIds)) {
          $getParams['contact_id'] = array('IN' => $roleContactsIds);
      }
      if (!empty($params['status_id'])) {
          $getParams['status_id'] = array('IN' => $params['status_id']);
      }
      $getParams['self_appraisal_due'] = array(
          'BETWEEN' => array(
              empty($params['self_appraisal_due_from']) ? '0000-00-00' : $params['self_appraisal_due_from'],
              empty($params['self_appraisal_due_to']) ? '9999-00-00' : $params['self_appraisal_due_to'],
          ),
      );
      $getParams['manager_appraisal_due'] = array(
          'BETWEEN' => array(
              empty($params['manager_appraisal_due_from']) ? '0000-00-00' : $params['manager_appraisal_due_from'],
              empty($params['manager_appraisal_due_to']) ? '9999-00-00' : $params['manager_appraisal_due_to'],
          ),
      );
      $getParams['grade_due'] = array(
          'BETWEEN' => array(
              empty($params['grade_due_from']) ? '0000-00-00' : $params['grade_due_from'],
              empty($params['grade_due_to']) ? '9999-00-00' : $params['grade_due_to'],
          ),
      );
      if (!empty($params['sequential'])) {
          $getParams['sequential'] = $params['sequential'];
      }
      if (!empty($params['options'])) {
          $getParams['options'] = $params['options'];
      }
      
      $result = civicrm_api3('Appraisal', 'get', $getParams);
      return $result;
  }
}
