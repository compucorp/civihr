<?php

class CRM_Hrjobroles_BAO_Query extends CRM_Contact_BAO_Query_Interface {

  /**
   * @var array Keeps a list of loaded Cost Centre Options
   */
  private $costCentreOptions = array();

  /**
   * @var array Keeps a list of loaded Location options values
   */
  private $locationOptions = array();

  /**
   * @var array Keeps a list of loaded Regions options values
   */
  private $regionOptions = array();

  /**
   * @var array Keeps a list of loaded Departments options values
   */
  private $departmentOptions = array();

  /**
   * @var array Keeps a list of loaded Level Types options values
   */
  private $levelTypeOptions = array();

  /**
   * @var array Options values for the Funding and Cost Centres fields
   *            these are hardcoded here (and also in the js script that
   *            builds the UI) cause they're not stored in the database.
   */
  private $fundindAndCostCentresOptions = array(
    '0' => 'Fixed',
    '1' => '%'
  );

  /**
   * @var array A list of fields available while importing/exporting
   */
  private $hrjobRoleFields = array();


  public function &getFields() {
    if (empty($this->hrjobRoleFields)) {
      $this->hrjobRoleFields = CRM_Hrjobroles_DAO_HrJobRoles::export();

      $this->hrjobRoleFields['hrjobrole_id'] = [
        'name'  => 'role_id',
        'title' => 'Job Role ID',
        'type'  => CRM_Utils_Type::T_INT,
        'where' => 'civicrm_hrjobroles.id'
      ];
    }

    return $this->hrjobRoleFields;
  }

  /**
   * @param $fieldName
   * @param $mode
   * @param $side
   *
   * @return mixed
   */
  public function from($name, $mode, $side) {
    $from = '';
    switch ($name) {
      case 'civicrm_contact':
        $from .= " $side JOIN civicrm_hrjobroles ON hrjobcontract.id = civicrm_hrjobroles.job_contract_id ";
        break;
    }

    return $from;
  }

  public function where(&$query) {
    $grouping = NULL;
    foreach ($query->_params as $param) {
      if ($this->isAJobRoleParam($param)) {
        if ($query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS) {
          $query->_useDistinct = TRUE;
        }
        $this->whereClauseSingle($param, $query);
      }
    }
  }

  private function isAJobRoleParam($param) {
    $paramHasName = isset($param[0]) && !empty($param[0]);
    if ($paramHasName && substr($param[0], 0, 10) == 'hrjobroles') {
      return true;
    }

    return false;
  }

  private function whereClauseSingle($values, &$query) {
    list($name, $op, $value, $grouping, $wildcard) = $values;

    $fields = CRM_Hrjobroles_BAO_HrJobRoles::fields();
    $fieldsKeys = CRM_Hrjobroles_BAO_HrJobRoles::fieldKeys();
    $field = substr($name, 11);
    $fieldKey = isset($fieldsKeys[$field]) ? $fieldsKeys[$field] : '';
    $whereField = 'civicrm_hrjobroles.'.$field;
    if($fieldKey) {
      $fieldTitle = $fields[$fieldKey]['title'];
    } else {
      $fieldTitle = $field;
    }
    switch ($name) {
      case 'hrjobroles_title':
      case 'hrjobroles_description':
        $op = 'LIKE';
        $value = "%" . trim($value, '%') . "%";
        $query->_qill[$grouping][] = $value ? $fieldTitle . " $op '$value'" : $fieldTitle;
        $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause($whereField, $op, $value, "String");
        $query->_tables['civicrm_hrjobroles'] = $query->_whereTables['civicrm_hrjobroles'] = 1;
        return;
      case 'hrjobroles_start_date_low':
      case 'hrjobroles_start_date_high':
        $query->dateQueryBuilder($values,
          'civicrm_hrjobroles', 'hrjobroles_start_date', 'start_date', 'Job Role Start Date'
        );
        return;
      case 'hrjobroles_end_date_low':
      case 'hrjobroles_end_date_high':
        $query->dateQueryBuilder($values,
          'civicrm_hrjobroles', 'hrjobroles_end_date', 'end_date', 'Job Role End Date'
        );
        return;
      case 'hrjobroles_location':
        $locationOptions = $this->getLocationOptions();
        $this->buildMultiValueClause($query, $value, $locationOptions, $fieldTitle, $grouping, $whereField);
        return;
      case 'hrjobroles_region':
        $regionOptions = $this->getRegionOptions();
        $this->buildMultiValueClause($query, $value, $regionOptions, $fieldTitle, $grouping, $whereField);
        return;
      case 'hrjobroles_department':
        $departmentOptions = $this->getDepartmentOptions();
        $this->buildMultiValueClause($query, $value, $departmentOptions, $fieldTitle, $grouping, $whereField);
        return;
      case 'hrjobroles_level_type':
        $levelTypeOptions = $this->getLevelTypeOptions();
        $this->buildMultiValueClause($query, $value, $levelTypeOptions, $fieldTitle, $grouping, $whereField);
        return;
      case 'hrjobroles_funder':
        $fundersIDs = $this->getContactsIDsForName($value);
        $op = 'LIKE';
        $value = "%" . trim($value, '%') . "%";
        $fieldTitle = 'Job Role Funder';
        $query->_qill[$grouping][] = $value ? $fieldTitle . " $op '$value'" : $fieldTitle;
        $clauses = array();
        //The Funders IDs are store in single database field, with | between them
        foreach($fundersIDs as $funderID) {
          $clauses[] = CRM_Contact_BAO_Query::buildClause($whereField, $op, "%|$funderID|%", "String");
        }
        $query->_where[$grouping][] = '('. implode(' OR ', $clauses) . ')';
        $query->_tables['civicrm_hrjobroles'] = $query->_whereTables['civicrm_hrjobroles'] = 1;
        return;
      case 'hrjobroles_funder_val_type':
        $this->buildFundindAndCostCentreTypeClause($query, $value, $fieldTitle, $op, $grouping, $whereField);
        return;
      case 'hrjobroles_percent_pay_funder':
        $fieldTitle = 'Funder % Amount';
        $this->buildPercentOrAmountClause($query, $fieldTitle, $op, $value, $grouping, $whereField);
        return;
      case 'hrjobroles_amount_pay_funder':
        $fieldTitle = 'Funder Absolute Amount';
        $this->buildPercentOrAmountClause($query, $fieldTitle, $op, $value, $grouping, $whereField);
        return;
      case 'hrjobroles_cost_center':
        $costCenterOptions = $this->getCostCentreOptions();
        $selectedCostCentres = array();
        foreach($value as $id) {
          $selectedCostCentres[] = '"'.$costCenterOptions[$id].'"';
        }
        $fieldTitle = "Job Role $fieldTitle";
        $query->_qill[$grouping][] = $value ? $fieldTitle . ' IN ' . implode(', ', $selectedCostCentres) : $fieldTitle;
        $clauses = array();
        $op = 'LIKE';
        //As are the Funders IDs, the Cost Centres IDs are stored in single database field, with | between them
        foreach($value as $id) {
          $clauses[] = CRM_Contact_BAO_Query::buildClause($whereField, $op, "%|$id|%", "String");
        }
        $query->_where[$grouping][] = '('. implode(' OR ', $clauses) . ')';
        $query->_tables['civicrm_hrjobroles'] = $query->_whereTables['civicrm_hrjobroles'] = 1;
        return;
      case 'hrjobroles_cost_center_val_type':
        $this->buildFundindAndCostCentreTypeClause($query, $value, $fieldTitle, $op, $grouping, $whereField);
        return;
      case 'hrjobroles_percent_pay_cost_center':
        $fieldTitle = 'Cost Centre % Amount';
        $this->buildPercentOrAmountClause($query, $fieldTitle, $op, $value, $grouping, $whereField);
        return;
      case 'hrjobroles_amount_pay_cost_center':
        $fieldTitle = 'Cost Centre Absolute Amount';
        $this->buildPercentOrAmountClause($query, $fieldTitle, $op, $value, $grouping, $whereField);
        return;
    }
  }

  public function registerAdvancedSearchPane(&$panes) {
    $panes['Job Role'] = 'hrjobroles';
  }

  public function getPanesMapper(&$panes) {
    $panes['Job Role'] = 'civicrm_hrjobroles';
  }

  public function setAdvancedSearchPaneTemplatePath(&$paneTemplatePathArray, $type) {
    if ($type  == 'hrjobroles') {
      $paneTemplatePathArray['hrjobroles'] = 'CRM/Hrjobroles/Form/Search/Criteria/JobRole.tpl';
    }
  }

  public function buildAdvancedSearchPaneForm(&$form, $type) {
    if($type == 'hrjobroles') {
      $form->add('hidden', 'hidden_hrjobroles', 1);
      $form->addElement('text', 'hrjobroles_title', ts('Title'), CRM_Core_DAO::getAttribute('CRM_Hrjobroles_DAO_HRJobRoles', 'title'));
      //Getting the attributes from title so this field has the same appeareance as title
      $form->addElement('text', 'hrjobroles_description', ts('Description'), CRM_Core_DAO::getAttribute('CRM_Hrjobroles_DAO_HRJobRoles', 'title'));

      CRM_Core_Form_Date::buildDateRange($form, 'hrjobroles_start_date', 1, '_low', '_high', ts('From:'), FALSE, FALSE);
      CRM_Core_Form_Date::buildDateRange($form, 'hrjobroles_end_date', 1, '_low', '_high', ts('From:'), FALSE, FALSE);

      $locations = $this->getLocationOptions();
      $form->add('select', 'hrjobroles_location', ts('Location'), $locations, FALSE,
        array('id' => 'hrjobroles_location', 'multiple' => true)
      );

      $regions = $this->getRegionOptions();
      $form->add('select', 'hrjobroles_region', ts('Region'), $regions, FALSE,
        array('id' => 'hrjobroles_region', 'multiple' => true)
      );

      $departments = $this->getDepartmentOptions();
      $form->add('select', 'hrjobroles_department', ts('Department'), $departments, FALSE,
        array('id' => 'hrjobroles_department', 'multiple' => true)
      );

      $levels = $this->getLevelTypeOptions();
      $form->add('select', 'hrjobroles_level_type', ts('Level'), $levels, FALSE,
        array('id' => 'hrjobroles_level_type', 'multiple' => true)
      );

      $form->addElement('text', 'hrjobroles_funder', ts('Funder (Complete OR Partial Name)'), CRM_Core_DAO::getAttribute('CRM_Hrjobroles_DAO_HRJobRoles', 'funder'));

      $form->add('select', 'hrjobroles_funder_val_type', ts('Funder Value Type'), $this->fundindAndCostCentresOptions, FALSE,
        array('id' => 'hrjobroles_funder_val_type', 'placeholder' => ts('- select - '))
      );

      $form->addElement('text', 'hrjobroles_percent_pay_funder', ts('Funder % Amount'), CRM_Core_DAO::getAttribute('CRM_Hrjobroles_DAO_HRJobRoles', 'percent_pay_funder'));
      $form->addElement('text', 'hrjobroles_amount_pay_funder', ts('Funder Absolute Amount'), CRM_Core_DAO::getAttribute('CRM_Hrjobroles_DAO_HRJobRoles', 'amount_pay_funder'));

      $costCentreOptions = $this->getCostCentreOptions();
      $form->add('select', 'hrjobroles_cost_center', ts('Cost Centre'), $costCentreOptions, FALSE,
        array('id' => 'hrjobroles_cost_center', 'multiple' => true)
      );

      $form->add('select', 'hrjobroles_cost_center_val_type', ts('Cost Centre Value Type'), $this->fundindAndCostCentresOptions, FALSE,
        array('id' => 'hrjobroles_cost_center_val_type', 'placeholder' => ts('- select - '))
      );

      $form->addElement('text', 'hrjobroles_percent_pay_cost_center', ts('Cost Centre % Amount'), CRM_Core_DAO::getAttribute('CRM_Hrjobroles_DAO_HRJobRoles', 'percent_pay_cost_center'));
      $form->addElement('text', 'hrjobroles_amount_pay_cost_center', ts('Cost Centre Absolute Amount'), CRM_Core_DAO::getAttribute('CRM_Hrjobroles_DAO_HRJobRoles', 'amount_pay_cost_center'));
    }
  }

  private function getCostCentreOptions() {
    if(empty($this->costCentreOptions)) {
      try {
        $result = civicrm_api3('OptionGroup', 'get', array(
          'sequential' => 1,
          'name' => "cost_centres",
          'options' => array('limit' => 1000),
          'api.OptionValue.get' => array('options' => array('limit' => 1000)),
        ));

        $hasOptionGroup = $result['is_error'] == 0 && $result['count'] == 1;
        $hasOptionValues = $hasOptionGroup && $result['values'][0]['api.OptionValue.get']['count'] > 0;

        if($hasOptionValues) {

          foreach($result['values'][0]['api.OptionValue.get']['values'] as $optionValue) {
            $this->costCentreOptions[$optionValue['id']] = $optionValue['label'];
          }
        }
      } catch(Exception $e) {

      }
    }

    return $this->costCentreOptions;
  }

  /**
   * @return array returns a list of location options, indexed by ID
   */
  private function getLocationOptions() {
    if(empty($this->locationOptions)) {
      $this->locationOptions = $this->getOptionsWithIDAsKeyFor('CRM_Hrjobroles_DAO_HrJobRoles', 'location');
    }

    return $this->locationOptions;
  }

  /**
   * @return array returns a list of location options, indexed by ID
   */
  private function getRegionOptions() {
    if(empty($this->regionOptions)) {
      $this->regionOptions = $this->getOptionsWithIDAsKeyFor('CRM_Hrjobroles_DAO_HrJobRoles', 'region');
    }

    return $this->regionOptions;
  }

  /**
   * @return array returns a list of departments options, indexed by ID
   */
  private function getDepartmentOptions() {
    if(empty($this->departmentOptions)) {
      $this->departmentOptions = $this->getOptionsWithIDAsKeyFor('CRM_Hrjobroles_DAO_HrJobRoles', 'department');
    }

    return $this->departmentOptions;
  }

  /**
   * @return array returns a list of departments options, indexed by ID
   */
  private function getLevelTypeOptions() {
    if(empty($this->levelTypeOptions)) {
      $this->levelTypeOptions = $this->getOptionsWithIDAsKeyFor('CRM_Hrjobroles_DAO_HrJobRoles', 'level_type');
    }

    return $this->levelTypeOptions;
  }

  private function getOptionsWithIDAsKeyFor($daoName, $field) {
    return CRM_Core_PseudoConstant::get($daoName, $field, array('keyColumn' => 'id'));
  }

  private function buildMultiValueClause(&$query, $value, $options, $fieldTitle, $grouping, $whereField) {
    if (!is_array($value)) {
      $value = array($value);
    }

    $qillValues = array();
    foreach ($value as $id) {
      $qillValues[] = '"' . $options[$id] . '"';
    }
    $op = 'IN';
    $query->_qill[$grouping][] = "Job Role $fieldTitle IN " . implode(' or ', $qillValues);
    $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause($whereField, $op, $value, "Integer");
    $query->_tables['civicrm_hrjobroles'] = $query->_whereTables['civicrm_hrjobroles'] = 1;
  }

  /**
   * Retrieves a list of Contacts IDs having the $contactName in its
   * display_name
   *
   * We're not using the API here because of this CiviCRM bug:
   * https://issues.civicrm.org/jira/browse/CRM-17042
   *
   * @param $contactName
   *
   * @return array
   */
  private function getContactsIDsForName($contactName) {
    $ids = array();
    $query = "
SELECT c.id
FROM civicrm_contact c
WHERE c.display_name LIKE %1 AND c.is_deleted = '0'";
    $params = array(
      1 => array("%$contactName%", 'String'),
    );
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    while($dao->fetch()) {
      $ids[] = $dao->id;
    }

    return $ids;
  }

  /**
   * Builds a simple LIKE clause for fields that store percents or amounts
   * for funder or cost centre pay
   *
   * @param $query
   * @param $fieldTitle
   * @param $op
   * @param $value
   * @param $grouping
   * @param $whereField
   */
  private function buildPercentOrAmountClause(&$query, $fieldTitle, $op, $value, $grouping, $whereField) {
    $query->_qill[$grouping][] = 'Job Role ' . $fieldTitle . " $op '$value'";
    $op = 'LIKE';
    $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause($whereField, $op, "%|$value|%", "String");
    $query->_tables['civicrm_hrjobroles'] = $query->_whereTables['civicrm_hrjobroles'] = 1;
  }

  private function buildFundindAndCostCentreTypeClause(&$query, $value, $fieldTitle, $op, $grouping, $whereField) {
    $type = $this->fundindAndCostCentresOptions[$value];
    $query->_qill[$grouping][] = 'Job Role ' . $fieldTitle . " $op '$type'";
    $op = 'LIKE';
    $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause($whereField, $op, "%|$value|%", "String");
    $query->_tables['civicrm_hrjobroles'] = $query->_whereTables['civicrm_hrjobroles'] = 1;
  }
}
