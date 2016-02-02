<?php

abstract class CRM_Hrjobcontract_Import_FieldsProvider {
  private $entityName;

  protected function __construct($entityName) {
    $this->entityName = $entityName;
  }

  protected function getEntityName() {
    return $this->entityName;
  }

  protected function createDivider($title) {
    return array(
      $this->getEntityName().'-divider' => array(
        'title' => $title
      )
    );
  }

  protected function convertImportableFields($importableFields) {
    unset($importableFields['do_not_import']);

    $result = array();
    foreach($importableFields as $key => $field) {
      $result[$this->getEntityName().'-'.$field['name']] = $field;
    }

    uasort($result, function ($a, $b) { return strcasecmp($a['title'], $b['title']); });

    return $result;
  }

  public abstract function provide();
}
