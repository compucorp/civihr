<?php

use Civi\API\Api3SelectQuery;

/**
 * This is a specialization of the Api3SelectQuery, created to allow an entity
 * to return fields that are not part of an entity's table.
 *
 * The original implementation of the buildSelectFields() on the Api3SelectQuery
 * class makes sure that only fields of the entity's table will be returned by
 * the query. That means that, even if this is a query with multiple joins, it
 * won't be possible to return fields from any of the joined tables. This class
 * works around this by overriding that method and making it filter out fields
 * based on a list of "allowed" fields passed to the constructor.
 */
class CRM_Hrjobroles_API_Query_VirtualEntitySelectQuery extends Api3SelectQuery {

  /**
   * @var array|bool
   *   The list of fields that can be selected/returned by the query
   *   Format: 'field name' => 'field name on the select clause (e.g. contract.contact_id)'
   */
  protected $virtualEntityFields = [];

  /**
   * CRM_Hrjobroles_API_Query_VirtualEntitySelectQuery constructor.
   *
   * @param string $entity
   * @param array $virtualEntityFields
   *   The list of fields that can be selected/returned
   * @param bool $checkPermissions
   */
  public function __construct($entity, $virtualEntityFields, $checkPermissions) {
    parent::__construct($entity, $checkPermissions);
    $this->virtualEntityFields = $virtualEntityFields;
  }

  /**
   * Builds the list of fields that can be selected by the query, based on the
   * list of fields passed to the constructor
   */
  protected function buildSelectFields() {
    $returnAllFields = (empty($this->select) || !is_array($this->select));
    $return = $returnAllFields ? $this->entityFieldNames : $this->select;
    if ($returnAllFields) {
      foreach (array_keys($this->apiFieldSpec) as $fieldName) {
        $return[] = $fieldName;
      }
    }

    // Always select the ID to keep some consistency with the API3 behavior
    $this->selectFields[self::MAIN_TABLE_ALIAS . '.id'] = 'id';

    foreach ($return as $fieldName) {
      $field = $this->getField($fieldName);
      if ($field && in_array($field['name'], $this->entityFieldNames)) {
        $this->selectFields[$this->virtualEntityFields[$fieldName]] = $field['name'];
      }
    }
  }
}
