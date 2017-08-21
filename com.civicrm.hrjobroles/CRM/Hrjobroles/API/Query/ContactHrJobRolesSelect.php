<?php

use CRM_Hrjobroles_API_Query_VirtualEntitySelectQuery as VirtualEntitySelectQuery;
use CRM_Hrjobroles_BAO_HrJobRoles as HRJobRoles;
use CRM_Hrjobcontract_BAO_HRJobContract as HRJobContract;

/**
 * This is a special class created mainly to support the ContactHrJobRoles.get
 * API. Its main objective is to return a list of Job Roles, but with a reduced
 * set of properties, and also include the id of the contact the role belongs to,
 * which is an information that is not stored together with the Job Role. It does
 * this by creating a query that joins the Job Roles table with the Contract
 * table, where the contact ID exists.
 *
 * Internally it uses a child class of the Api3SelectQuery class, which means
 * it can support everything that a normal .get query can like:
 * - Different operators (IN, LIKE, <>, etc)
 * - Limit and Offset operations
 * - Sorting
 */
class CRM_Hrjobroles_API_Query_ContactHrJobRolesSelect {

  /**
   * @var array
   *   An array of params passed to an API endpoint
   */
  private $params;

  /**
   * @var \CRM_HRLeaveAndAbsences_API_Query_Select
   *  The SelectQuery instance wrapped by this class
   */
  private $query;

  /**
   * @var array
   *  A list of fields that are supported by the entity + their names as they
   *  should be added to the SELECT query. Usually the internal classes can
   *  build this by themselves, but here we need to do it manually because not
   *  all the fields are from the same database table
   */
  private $selectableFields = [
    'id' => 'a.id',
    'title' => 'a.title',
    'description' => 'a.description',
    'region' => 'a.region',
    'department' => 'a.department',
    'level_type' => 'a.level_type',
    'functional_area' => 'a.functional_area',
    'location' => 'a.location',
    'contact_id' => 'jc.contact_id'
  ];

  public function __construct($params) {
    $this->params = $params;
    $this->buildCustomQuery();
  }

  /**
   * Build the custom query.
   *
   * Here is basically where we get the given $params and build the entire query,
   * including the JOIN with the Contracts table
   */
  private function buildCustomQuery() {
    $customQuery = CRM_Utils_SQL_Select::from(HRJobRoles::getTableName() . ' as a');

    $this->addJoins($customQuery);

    $this->query = $this->buildSelectQuery('ContactHrJobRoles');
    $this->query->merge($customQuery);
  }

  /**
   * This method parses the $params array passed to the API and build an instance
   * of VirtualEntitySelectQuery, which is a child class of Api3SelectQuery and
   * it's the class that will actually build the SQL query.
   *
   * @param string $entity
   *
   * @return \CRM_Hrjobroles_API_Query_VirtualEntitySelectQuery
   */
  private function buildSelectQuery($entity) {
    $checkPermissions = !empty($this->params['check_permissions']);
    $query = new VirtualEntitySelectQuery($entity, $this->selectableFields, $checkPermissions);

    $query->where = $this->params;

    $options = _civicrm_api3_get_options_from_params($this->params);

    if ($options['is_count']) {
      $query->select = ['count_rows'];
    }
    else {
      $query->select  = array_keys(array_filter($options['return']));
      $query->orderBy = $options['sort'];
    }

    $query->limit = $options['limit'];
    $query->offset = $options['offset'];

    return $query;
  }

  /**
   * Add the clauses to JOIN the Job Roles table with other tables
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addJoins(CRM_Utils_SQL_Select $query) {
    $joins[] = 'INNER JOIN ' . HRJobContract::getTableName() . ' jc ON jc.id = a.job_contract_id';

    $query->join(null, $joins);
  }

  /**
   * Executes the query
   *
   * @return array|int
   */
  public function run() {
    return $this->query->run();
  }
}

