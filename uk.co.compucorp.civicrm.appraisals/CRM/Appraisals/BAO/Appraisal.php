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
        
        if ($hook === 'create') {
            if (empty($params['appraisal_cycle_id'])) {
                throw new Exception("Please specify 'appraisal_cycle_id' value to create Appraisal.");
            }
            
            $appraisalCycle = civicrm_api3('AppraisalCycle', 'getsingle', array(
                'sequential' => 1,
                'id' => (int)$params['appraisal_cycle_id'],
            ));
            if (!empty($appraisalCycle['is_error']) && (int)$appraisalCycle['is_error']) {
                throw new Exception("Cannot find Appraisal Cycle with 'id' = " . (int)$params['appraisal_cycle_id'] . '.');
            }
            
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
        CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
        ////TODO: trigger on post: CRM_Tasksassignments_Reminder::sendReminder((int)$instance->id);
        
        return $instance;
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
}
