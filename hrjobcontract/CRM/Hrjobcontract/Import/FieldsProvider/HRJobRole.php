<?php

class CRM_Hrjobcontract_Import_FieldsProvider_HRJobRole extends CRM_Hrjobcontract_Import_FieldsProvider {
  public function __construct() {
    parent::__construct('HRJobRole');
  }

  public function provide() {
    $importableFields = CRM_Hrjobroles_BAO_HrJobRoles::importableFields();

    return array_merge(
      $this->createDivider('- job role fields -'),
      $this->convertImportableFields($importableFields)
    );
  }
}
