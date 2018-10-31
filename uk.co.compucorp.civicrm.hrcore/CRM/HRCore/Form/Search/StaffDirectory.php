<?php


class CRM_HRCore_Form_Search_StaffDirectory implements CRM_Contact_Form_Search_Interface {

  /**
   * @var array
   *  An array of form field as key and its table
   *  alias as the value.
   */
  protected $filters = [];

  /**
   * @var array
   */
  protected $where = [];

  /**
   * @var array
   */
  protected $from =[];

  /**
   * @var string
   */
  protected $selectClause = NULL;

  /**
   * @var string
   */
  protected $whereClause = NULL;

  /**
   * @var string
   */
  protected $fromClause = NULL;

  /**
   * @var array
   *   Stores the form values in query compatible
   *   format.
   */
  protected $params = [];

  /**
   * @var array
   */
  protected $formValues = [];

  /**
   * @var array
   */
  protected $columns = [];

  /**
   * @var array
   */
  protected $selectStaffFixedOptions = [
    'all' => 'All Staff',
    'current' => 'Current Staff',
    'future' => 'Future Staff',
    'past' => 'Past Staff'
  ];

  /**
   * Class constructor.
   *
   * @param array $formValues
   */
  public function __construct(&$formValues) {
    $this->formValues = $formValues;

    $this->columns = [
      ts('Display Name') => 'display_name',
      ts('Work Phone') => 'work_phone',
      ts('Work Email') => 'work_email',
      ts('Manager') => 'manager',
      ts('Location') => 'location',
      ts('Department') => 'department',
      ts('Job Title') => 'job_title',
    ];

    $this->filters = [
      'select_staff' => '',
    ];

    $allParameters = array_merge($this->formValues, $this->getAdditionalParameters());
    $this->params = CRM_Contact_BAO_Query::convertFormValues($allParameters);
    $this->generateQueryClause();
  }

  /**
   * Builds the select part of the query.
   */
  private function buildSelect() {
    $this->selectClause = "SELECT contact_a.id as contact_id,
      contact_a.display_name as display_name,
      GROUP_CONCAT(DISTINCT CASE WHEN phone_location.name = 'Work' THEN CONCAT(c_phone.phone, IF (c_phone.phone_ext, CONCAT(' + ', c_phone.phone_ext), '')) END) AS work_phone,
      GROUP_CONCAT(DISTINCT CASE WHEN email_location.name = 'Work' THEN c_email.email END) AS work_email,
      GROUP_CONCAT(DISTINCT CASE WHEN civicrm_relationship_type.is_active = '1' THEN manager_contact.display_name END) AS manager,
      GROUP_CONCAT(DISTINCT ov_location.name) AS location,
      GROUP_CONCAT(DISTINCT ov_department.name) AS department,
      contract_details.title AS job_title ";
  }

  /**
   * Builds the where part of the overrall sql query.
   */
  private function buildWhere() {
    foreach ($this->params as $param) {
      list($name, $op, $value, $grouping, $wildcard) = $param;
      if (!in_array($name, array_keys($this->filters))) {
        continue;
      }

      $alias =  $this->filters[$name];
      switch ($name) {
        case 'select_staff':
          $this->setQueryConditionForSelectStaff($value);
          break;
        default:
          $this->where[] = CRM_Contact_BAO_Query::buildClause($alias, $op, $value);
      }
    }

    $this->where[] = "contact_a.contact_type = 'Individual' AND contact_a.is_deleted = 0";
  }

  /**
   * Builds the FROM part of the overral SQL query.
   *
   * @return string
   */
  private function buildFrom() {
    $this->from['before_contract'] = "civicrm_contact contact_a
    LEFT JOIN civicrm_phone c_phone ON c_phone.contact_id = contact_a.id
    LEFT JOIN civicrm_location_type phone_location ON  c_phone.location_type_id = phone_location.id
    LEFT JOIN civicrm_email c_email ON c_email.contact_id = contact_a.id
    LEFT JOIN civicrm_location_type email_location ON c_email.location_type_id = email_location.id";

    $this->from['contract_join'] = $this->getJobContractJoin($this->getJobDetailsConditionForSpecificStaff());

    $today = date('Y-m-d');
    $this->from['after_contract'] = "LEFT JOIN civicrm_hrjobroles hrjobroles ON contract_details.id = hrjobroles.job_contract_id
    LEFT JOIN civicrm_option_group og_department ON og_department.name = 'hrjc_department'
    LEFT JOIN civicrm_option_value ov_department ON og_department.id = ov_department.option_group_id AND ov_department.value = hrjobroles.department
    LEFT JOIN civicrm_option_group og_location ON og_location.name = 'hrjc_location'
    LEFT JOIN civicrm_option_value ov_location ON og_location.id = ov_location.option_group_id AND ov_location.value = hrjobroles.location
    LEFT JOIN civicrm_relationship 
      ON (contact_a.id = civicrm_relationship.contact_id_a AND civicrm_relationship.is_active = '1' 
      AND ((civicrm_relationship.start_date IS NULL OR civicrm_relationship.start_date <= '{$today}') 
      AND (civicrm_relationship.end_date IS NULL OR civicrm_relationship.end_date >= '{$today}')))
    LEFT JOIN civicrm_relationship_type ON civicrm_relationship.relationship_type_id = civicrm_relationship_type.id
    LEFT JOIN civicrm_contact manager_contact ON  civicrm_relationship.contact_id_b = manager_contact.id";
  }

  /**
   * Sets the Select, From and Where clauses of the overall SQL query.
   */
  private function generateQueryClause() {
    $this->buildSelect();
    $this->buildFrom();
    $this->buildWhere();

    if (!empty($this->where)) {
      $this->whereClause =  ' WHERE ' . implode(' AND ', $this->where);
    }

    $this->fromClause = ' FROM ' . implode(' ', $this->from);
  }

  /**
   * Returns the GROUP BY part of the sql.
   *
   * @return string
   */
  private function getGroupBy() {
    return ' GROUP BY contact_a.id, contract_details.title';
  }


  /**
   * {@inheritdoc}
   */
  public function contactIDs($offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = FALSE) {
    $sql = "SELECT contact_a.id as contact_id " .
      $this->fromClause . $this->whereClause . $this->getGroupBy();

    if ($rowcount > 0 && $offset >= 0) {
      $offset = CRM_Utils_Type::escape($offset, 'Int');
      $rowcount = CRM_Utils_Type::escape($rowcount, 'Int');

      $sql .= " LIMIT $offset, $rowcount";
    }

    return CRM_Core_DAO::composeQuery($sql, CRM_Core_DAO::$_nullArray);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(&$form) {
    $options = $this->selectStaffFixedOptions + ['choose_date' => 'Select Dates'];
    $form->add('select', 'select_staff', ts('Select Staff'), $options, FALSE,
      ['class' => 'crm-select2', 'multiple' => FALSE]
    );

    CRM_Core_Form_Date::buildDateRange($form, 'contract_start_date', 1, '_low', '_high', ts('From:'), FALSE, FALSE);
    CRM_Core_Form_Date::buildDateRange($form, 'contract_end_date', 1, '_low', '_high', ts('To:'), FALSE, FALSE);

    CRM_Utils_System::setTitle(ts('Staff Directory'));
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    $sql = "SELECT COUNT(DISTINCT(contact_a.id)) as total_count " . $this->fromClause . $this->whereClause;
    $count = 0;
    $result = CRM_Core_DAO::executeQuery($sql);
    if ($result->fetch()) {
      $count = $result->total_count;
    }

    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function all(
    $offset = 0,
    $rowCount = 0,
    $sort = NULL,
    $includeContactIDs = FALSE,
    $justIDs = FALSE
  ) {

    $orderBy = '';

    if ($sort) {
      if ($sort instanceof CRM_Utils_Sort) {
        $sort = trim($sort->orderBy());
      }
      else{
        $sort = trim($sort);
      }

      $orderBy = " ORDER BY " . trim($sort);
    }

    if ($includeContactIDs) {
      $this->includeContactIDs();
    }

    $sql = $this->selectClause .  $this->fromClause . $this->whereClause . $this->getGroupBy() . $orderBy ;

    if ($offset || $rowCount) {
      $sql .= " LIMIT $offset, $rowCount";
    }

    return $sql;
  }

  /**
   * Logic for including contact Ids and rebuilding the Where clause
   * when contact ids are selected via checkboxes in the UI.
   */
  public function includeContactIDs() {
    $contactIds = [];
    foreach ($this->formValues as $id => $value) {
      if (substr($id, 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX) {
        $contactIds[] = substr($id, CRM_Core_Form::CB_PREFIX_LEN);
      }
    }

    CRM_Utils_Type::validateAll($contactIds, 'Positive');
    if (!empty($contactIds)) {
      $this->where[] = " ( contact_a.id IN (" . implode(',', $contactIds) . " ) ) ";
      $this->whereClause =  ' WHERE ' . implode(' AND ', $this->where);
    }
  }

  /**
   * The part of the query that returns the JOIN to the job contracts table.
   * We need to create a derived job contract details table so as to ensure
   * that the job contract record with the latest start date for the contact
   * is the record fetched as regards the contract period dates condition.
   *
   * @param string $jobDetailsCondition
   *
   * @return string
   */
  private function getJobContractJoin($jobDetailsCondition) {
    $sql = "LEFT JOIN
    (SELECT civicrm_hrjobcontract.contact_id,
    MAX(contract_details.period_start_date) as period_start_date 
    FROM {$this->getJobContractDetailsDerivedTable($jobDetailsCondition)} 
    GROUP BY civicrm_hrjobcontract.contact_id) contract_details_aggregate
      ON contract_details_aggregate.contact_id = contact_a.id
    LEFT JOIN
    (SELECT contract_details.title, civicrm_hrjobcontract.id, civicrm_hrjobcontract.contact_id,
    contract_details.period_start_date, contract_details.period_end_date 
    FROM {$this->getJobContractDetailsDerivedTable($jobDetailsCondition)}) contract_details
      ON (contract_details.contact_id = contract_details_aggregate.contact_id 
    AND contract_details.period_start_date = contract_details_aggregate.period_start_date)";

    return $sql;
  }

  /**
   * Returns the Job contract details derived table.
   *
   * @param string $jobDetailsCondition
   *
   * @return string
   */
  private function getJobContractDetailsDerivedTable($jobDetailsCondition) {
    $sql = "civicrm_hrjobcontract 
    INNER JOIN civicrm_hrjobcontract_revision rev 
      ON rev.id = (SELECT id
                   FROM civicrm_hrjobcontract_revision jcr2
                   WHERE jcr2.jobcontract_id = civicrm_hrjobcontract.id
                   ORDER BY jcr2.effective_date DESC LIMIT 1)
    INNER JOIN civicrm_hrjobcontract_details contract_details
      ON rev.details_revision_id = contract_details.jobcontract_revision_id 
    WHERE civicrm_hrjobcontract.deleted = 0";

    if ($jobDetailsCondition) {
      $sql .=  " AND " . $jobDetailsCondition;
    }

    return $sql;
  }

  /**
   * Returns the SQL query condition for the period start and end dates
   * depending on the staff type.
   *
   * @param string $staffType
   *
   * @return string
   */
  private function getJobDetailsConditionForSpecificStaff($staffType = 'all') {
    $sql = '';

    if($staffType == 'all') {
      return $sql;
    }

    $today = date('Y-m-d');

    if ($staffType == 'current') {
      $sql .= "contract_details.period_start_date <= '{$today}'
               AND (contract_details.period_end_date >= '{$today}' OR contract_details.period_end_date IS NULL)";
    }

    if ($staffType == 'past') {
      $sql .= "contract_details.period_end_date < '{$today}'";
    }

    if ($staffType == 'future') {
      $sql .= "contract_details.period_start_date > '{$today}'";
    }

    return $sql;
  }

  /**
   * Returns the SQL query condition for the period start and end dates
   * depending on the given period start and end dates.
   *
   * @param \DateTime|NULL $periodStartDate
   * @param \DateTime|NULL $periodEndDate
   *
   * @return string
   */
  private function getJobDetailsConditionForSpecificDates(
    DateTime $periodStartDate = NULL,
    DateTime $periodEndDate = NULL)
  {

    $conditions = [];
    if (!$periodStartDate && !$periodEndDate) {
      return $this->getJobDetailsConditionForSpecificStaff();
    }

    if ($periodStartDate) {
      $conditions[] = "(contract_details.period_end_date >= '" .
        $periodStartDate->format('Y-m-d') . "' OR contract_details.period_end_date IS NULL)";
    }

    if ($periodEndDate) {
      $conditions[] = "contract_details.period_start_date <= '". $periodEndDate->format('Y-m-d') . "'";
    }

    return implode(' AND ', $conditions);
  }

  /**
   * Returns additional parameters set via the URL when force = 1
   * Only form fields that are included in the filters are returned
   * from the URL parameters.
   *
   * @return array
   */
  private function getAdditionalParameters() {
    $additionalParameters = [];
    if (!empty($this->formValues['force']) && $this->formValues['force'] == 1) {
      foreach($this->filters as $filter) {
        $additionalParameters['filter'] = filter_input(
          INPUT_GET,
          $filter,
          FILTER_SANITIZE_STRING);
      }
    }

    return $additionalParameters;
  }

  /**
   * Sets the query condition for 'select_staff' field
   *
   * @param string $value
   */
  private function setQueryConditionForSelectStaff($value) {
    if (in_array($value, array_keys($this->selectStaffFixedOptions))) {
      $jobDetailsCondition = $this->getJobDetailsConditionForSpecificStaff($value);
    }
    elseif ($value == 'choose_date') {
      $jobDetailsCondition = $this->getWhereClauseWhenSelectStaffIsChooseDate();
    }
    else {
      $jobDetailsCondition = $this->getWhereClauseWhenSelectStaffIsRelativeDate($value);
    }

    if ($jobDetailsCondition) {
      $this->from['contract_join'] = $this->getJobContractJoin($jobDetailsCondition);
      $this->where[] = $jobDetailsCondition;
    }
  }

  /**
   * Gets the WHERE condition when select_staff field has value 'choose_date'
   *
   * @return string
   */
  private function getWhereClauseWhenSelectStaffIsChooseDate() {
    $fromDate = $this->formValues['contract_start_date'] ? new DateTime($this->formValues['contract_start_date']) : '';
    $toDate = $this->formValues['contract_end_date'] ? new DateTime($this->formValues['contract_end_date']) : '';

    return $this->getJobDetailsConditionForSpecificDates($fromDate, $toDate);
  }

  /**
   * Gets the WHERE condition when select_staff field has relative date
   * value, e.g 'this.year'.
   *
   * @param string $value
   *
   * @return string
   */
  private function getWhereClauseWhenSelectStaffIsRelativeDate($value) {
    list($term, $unit) = explode('.', $value, 2);
    $dateRange = CRM_Utils_Date::relativeToAbsolute($term, $unit);
    $from = substr($dateRange['from'], 0, 8);
    $to = substr($dateRange['to'], 0, 8);

    return $this->getJobDetailsConditionForSpecificDates(new DateTime($from), new DateTime($to));
  }

  /**
   * {@inheritdoc}
   */
  public function where($includeContactIDs = FALSE) {}

  /**
   * {@inheritdoc}
   */
  public function from() {}

  /**
   * {@inheritdoc}
   */
  public function templateFile() {
    return 'CRM/HRCore/Form/Search/StaffDirectory.tpl';
  }

  /**
   * {@inheritdoc}
   */
  public function &columns() {
    return $this->columns;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildTaskList(CRM_Core_Form_Search $form) {
    $newTaskList = [];
    $taskListLabelMapping = [
      'Create User Accounts(s)' => 'Create User Accounts(s)',
      'Delete contacts' => 'Delete Staff',
      'Delete permanently' => 'Delete Staff Permanently',
      'Export contacts' => 'Export Staff',
      'Print/merge document' => 'Print/merge document'
    ];
    $oldTaskLists = $form->getVar('_taskList');

    foreach($taskListLabelMapping as $oldLabel => $newLabel) {
      $key = array_search($oldLabel, $oldTaskLists);
      if ($key !== FALSE) {
        $newTaskList[$key] = $newLabel;
      }
    }

    return $newTaskList;
  }

  /**
   * Validate form input.
   *
   * @param array $fields
   * @param array $files
   * @param CRM_Core_Form $self
   *
   * @return array
   *   Input errors from the form.
   */
  public function formRule($fields, $files, $self) {
    return [];
  }

}
