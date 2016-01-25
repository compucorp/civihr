<?php

class CRM_Appraisals_Form_Report_Report extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array('Membership');
  protected $_customGroupGroupBy = FALSE; function __construct() {
    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'appraisal_contact_id' => array(
            'name' => 'id',
            'default' => true,
          ),
          'external_identifier' => array(
            'default' => true,
          ),
          'sort_name' => array(
            'title' => ts('Contact Name'),
            'default' => TRUE,
            'no_repeat' => FALSE,
          ),
          'first_name' => array(
            'title' => ts('First Name'),
            'default' => true,
          ),
          'last_name' => array(
            'title' => ts('Last Name'),
            'default' => true,
          ),
        ),
        'filters' => array(
          'sort_name' => array(
            'title' => ts('Contact Name'),
            'operator' => 'like',
          ),
          'id' => array(
            //'no_display' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),

      'civicrm_email' => array(
        'dao' => 'CRM_Core_DAO_Email',
        'fields' => array(
            'email' => array(
                'default' => true,
            ),
        ),
        'grouping' => 'contact-fields',
      ),
        
      'civicrm_appraisal_cycle' => array(
        'dao' => 'CRM_Appraisals_DAO_AppraisalCycle',
        'fields' => array(
            'cycle_id' => array(
                'name' => 'id',
            ),
            'cycle_name' => array(
                'name' => 'cycle_name',
            ),
            'cycle_start_date' => array(
                'name' => 'cycle_start_date',
            ),
            'cycle_end_date' => array(
                'name' => 'cycle_end_date',
            ),
            'cycle_self_appraisal_due' => array(
                'name' => 'cycle_self_appraisal_due',
            ),
            'cycle_manager_appraisal_due' => array(
                'name' => 'cycle_manager_appraisal_due',
            ),
            'cycle_grade_due' => array(
                'name' => 'cycle_grade_due',
            ),
            'cycle_type_id' => array(
                'name' => 'cycle_type_id',
            ),
            'cycle_is_active' => array(
                'name' => 'cycle_is_active',
            ),
        ),
        'filters' => array(
          //'id' => array(),
        ),
        'grouping' => 'appraisal-cycle-fields',
      ),

      'civicrm_appraisal' => array(
        'dao' => 'CRM_Appraisals_DAO_Appraisal',
        'fields' => array(
            'id' => array(),
            'manager_id' => array(),
            'self_appraisal_file_id' => array(),
            'manager_appraisal_file_id' => array(),
            'self_appraisal_due' => array(),
            'manager_appraisal_due' => array(),
            'grade_due' => array(),
            'due_changed' => array(),
            'meeting_date' => array(),
            'meeting_completed' => array(),
            'approved_by_employee' => array(),
            'grade' => array(),
            'notes' => array(),
            'status_id' => array(),
            'original_id' => array(),
            'created_date' => array(),
            'is_current' => array(),
        ),
        'filters' => array(
          'id' => array('default' => null),
            //'appraisal_cycle_id' => array(),
        ),
        'grouping' => 'appraisal-fields',
      ),
    );
    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Appraisals Report'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) ||
            CRM_Utils_Array::value($fieldName, $this->_params['fields'])
          ) {
            if ($tableName == 'civicrm_address') {
              $this->_addressField = TRUE;
            }
            elseif ($tableName == 'civicrm_email') {
              $this->_emailField = TRUE;
            }
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
          }
        }
      }
    }

    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  function from() {
    $this->_from = "
         FROM  civicrm_appraisal_cycle {$this->_aliases['civicrm_appraisal_cycle']} {$this->_aclFrom}
               LEFT JOIN civicrm_appraisal {$this->_aliases['civicrm_appraisal']}
                          ON {$this->_aliases['civicrm_appraisal_cycle']}.id =
                             {$this->_aliases['civicrm_appraisal']}.appraisal_cycle_id 
               LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']}
                          ON {$this->_aliases['civicrm_appraisal']}.contact_id =
                             {$this->_aliases['civicrm_contact']}.id ";
                             
    //used when email field is selected
    if ($this->_emailField) {
      $this->_from .= "
              LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']}
                        ON {$this->_aliases['civicrm_contact']}.id =
                           {$this->_aliases['civicrm_email']}.contact_id AND
                           {$this->_aliases['civicrm_email']}.is_primary = 1 ";
    }
  }

  function where() {
    $clauses = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('operatorType', $field) & CRM_Utils_Type::T_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from     = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to       = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($field['name'], $relative, $from, $to, $field['type']);
          }
          else {
            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }
          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }

    if (empty($clauses)) {
      $this->_where = "WHERE ( 1 ) ";
    }
    else {
      $this->_where = "WHERE " . implode(' AND ', $clauses);
    }

    /*if ($this->_aclWhere) {
      $this->_where .= " AND {$this->_aclWhere} ";
    }*/
  }

  function groupBy() {
  }

  function orderBy() {
    $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_appraisal']}.original_id, {$this->_aliases['civicrm_appraisal']}.is_current DESC, {$this->_aliases['civicrm_appraisal']}.created_date ";
  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);
    $sql = $this->buildQuery(TRUE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function alterDisplay(&$rows) {
    // custom code to alter rows
    $entryFound = FALSE;
    $checkList = array();
    
    $convertTables = array('civicrm_appraisal_cycle', 'civicrm_appraisal');
    $ei = CRM_Appraisals_ExportImportValuesConverter::singleton();
    
    foreach ($rows as $rowNum => $row) {
      if (!empty($this->_noRepeats) && $this->_outputMode != 'csv') {
        // not repeat contact display names if it matches with the one
        // in previous row
        $repeatFound = FALSE;
        foreach ($row as $colName => $colVal) {
          if (CRM_Utils_Array::value($colName, $checkList) &&
            is_array($checkList[$colName]) &&
            in_array($colVal, $checkList[$colName])
          ) {
            $rows[$rowNum][$colName] = "";
            $repeatFound = TRUE;
          }
          if (in_array($colName, $this->_noRepeats)) {
            $checkList[$colName][] = $colVal;
          }
        }
      }

      if (array_key_exists('civicrm_membership_membership_type_id', $row)) {
        if ($value = $row['civicrm_membership_membership_type_id']) {
          $rows[$rowNum]['civicrm_membership_membership_type_id'] = CRM_Member_PseudoConstant::membershipType($value, FALSE);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_address_state_province_id', $row)) {
        if ($value = $row['civicrm_address_state_province_id']) {
          $rows[$rowNum]['civicrm_address_state_province_id'] = CRM_Core_PseudoConstant::stateProvince($value, FALSE);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_address_country_id', $row)) {
        if ($value = $row['civicrm_address_country_id']) {
          $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country($value, FALSE);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        $rows[$rowNum]['civicrm_contact_sort_name'] &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact Summary for this Contact.");
        $entryFound = TRUE;
      }

      if (!$entryFound) {
        //break;
      }
      
      // Convert values for export
      foreach ($convertTables as $tableName) {
        $fields = $this->_columns[$tableName]['fields'];
        //TODO: remove: var_dump($fields);
        foreach ($fields as $key => $value) {
            $rowKey = $tableName . '_' . $key;
            if (isset($row[$rowKey])) {
                $rows[$rowNum][$rowKey] = $ei->export($tableName, $key, $row[$rowKey]);
            }
        }
      }//TODO: remove: die;
    }
  }
}
