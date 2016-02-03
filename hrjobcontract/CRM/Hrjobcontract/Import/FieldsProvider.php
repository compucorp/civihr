<?php

abstract class CRM_Hrjobcontract_Import_FieldsProvider {
  private $entityName;

  /**
   * @param string $entityName The name of the entity (e.g. HRJobPay)
   */
  protected function __construct($entityName) {
    $this->entityName = $entityName;
  }

  /**
   * @return string
   */
  protected function getEntityName() {
    return $this->entityName;
  }

  /**
   * Create a divider (i.e. a header in select element)
   *
   * @param string $title
   * @return array
   */
  protected function createDivider($title) {
    return array(
      $this->getEntityName().'-divider' => array(
        'title' => $title
      )
    );
  }

  /**
   * Convert importable fields from entity to a format that can be used by import code.
   *
   * @param array $importableFields
   * @return array
   */
  protected function convertImportableFields(array $importableFields) {
    unset($importableFields['do_not_import']);

    $result = array();
    foreach($importableFields as $key => $field) {
      $result[$this->getEntityName().'-'.$field['name']] = $field;
    }

    uasort($result, function ($a, $b) { return strcasecmp($a['title'], $b['title']); });

    return $result;
  }

  /**
   * Get a list of importable fields.
   *
   * @return array
   */
  public abstract function provide();
}
