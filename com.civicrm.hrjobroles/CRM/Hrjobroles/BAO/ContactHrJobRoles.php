<?php

use CRM_Hrjobroles_DAO_HrJobRoles as HrJobRoles;

/**
 * This is a virtual entity. Its main objective is to serve as the underlying
 * entity for the ContactJobRole API.
 *
 * Originally, there's no direct connection between a Job Role and a Contact.
 * A Job Role is linked to a Contract, which finally is the entity connect to
 * a Contact. This means that, if we want to know which contacts work in the
 * IT department, we need to:
 * 1. Fetch all the Job Roles where the department == IT
 * 2. Fetch all the Contracts linked to these Job Roles
 * 3. Fetch all the Contacts linked to these Contracts
 *
 * The idea of this virtual entity is to expose both the Contact and Job Role
 * information under a single place and reduce the number of API calls to get
 * the data as exemplified above. The ContactJobRole.get custom API was created
 * to create the query necessary for this. This virtual BAO is necessary to
 * exposed which field will be available on the API.
 *
 * @see civicrm_api3_contact_job_role_get()
 * @see CRM_Hrjobroles_API_Query_ContactHrJobRolesSelect
 */
class CRM_Hrjobroles_BAO_ContactHrJobRoles extends HrJobRoles {

  /**
   * @var array
   *  To avoid exposing confidential information via de API, this entity exposes
   *  just small set of all the HrJobRoles fields. Should a user have access to
   *  all the fields, the HrJobRoles.get API should be used instead.
   *  This is the list of fields exposed by the ContactJobRole.API
   */
  private static $allowedFields = [
    'id',
    'title',
    'region',
    'department',
    'level_type',
    'location'
  ];

  /**
   * Returns the same information as HrJobRoles::fields(), but only for the
   * fields listed on $allowedFields. Additionally, it returns a "fake"
   * contact_id, which will actually come from the Job Contract.
   *
   * @return array
   */
  public static function &fields() {
    $fields = [];
    foreach(HrJobRoles::fields() as $key => $field) {
      if(!empty($field['name']) && in_array($field['name'], self::$allowedFields)) {
        $fields[$key] = $field;
      }
    }

    $fields['contact_id'] = [
      'name' => 'contact_id',
      'type' => CRM_Utils_Type::T_INT,
      'title' => ts('Contact ID'),
    ];

    return $fields;
  }

}
