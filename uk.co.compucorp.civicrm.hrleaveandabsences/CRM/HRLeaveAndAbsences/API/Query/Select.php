<?php

use Civi\API\Api3SelectQuery;

/**
 * This class is just a wrapper around Civi\API\Api3SelectQuery.
 *
 * It introduces a new constructor which accepts the API params and then uses it
 * to prepare the internal query properties.
 */
class CRM_HRLeaveAndAbsences_API_Query_Select extends Api3SelectQuery {

  /**
   * CRM_HRLeaveAndAbsences_API_Query_Select constructor.
   *
   * @param string $entity
   *   An entity name like Contact, LeaveRequest, etc
   * @param array $params
   *   The API params array
   */
  public function __construct($entity, $params) {
    $checkPermissions = !empty($params['check_permissions']);
    parent::__construct($entity, $checkPermissions);
    $this->setupQueryFromParams($params);
  }

  /**
   * Setup things like limit, order, offset and fields to return
   *
   * @param array $params
   */
  private function setupQueryFromParams($params) {
    $this->where = $params;

    $options = _civicrm_api3_get_options_from_params($params);

    if ($options['is_count']) {
      $this->select = ['count_rows'];
    }
    else {
      $this->select  = array_keys(array_filter($options['return']));
      $this->orderBy = $options['sort'];
    }

    $this->limit = $options['limit'];
    $this->offset = $options['offset'];
  }

}
