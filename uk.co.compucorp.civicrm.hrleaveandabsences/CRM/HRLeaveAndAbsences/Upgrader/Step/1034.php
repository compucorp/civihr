<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1034 {

  /**
   * Adds the category field to the Absence Type table
   * and setup option value for absence type categories
   *
   * @return bool
   */
  public function upgrade_1034() {
    $this->up1034_createCategoryColumn();
    $this->up1034_createCategoryOptionValues();

    return TRUE;
  }

  /**
   * Creates the category field in Absence Type table if not existing
   */
  private function up1034_createCategoryColumn() {
    $absenceTypeTable = AbsenceType::getTableName();

    if (!SchemaHandler::checkIfFieldExists($absenceTypeTable, 'category')) {
      CRM_Core_DAO::executeQuery("
        ALTER TABLE {$absenceTypeTable}
        ADD category int unsigned NOT NULL COMMENT 'This is used for grouping leave types.'
      ");
    }
  }

  /**
   * Ensures absence type category option group and its values exist, creating one if not
   */
  private function up1034_createCategoryOptionValues() {
    CRM_Core_BAO_OptionGroup::ensureOptionGroupExists([
      'name' => 'hrleaveandabsences_absence_type_category',
      'title' => ts('Category'),
      'is_reserved' => 1,
    ]);

    $options = [
      ['name' => 'leave', 'label' => ts('Leave')],
      ['name' => 'sickness', 'label' => ts('Sickness')],
      ['name' => 'toil', 'label' => ts('TOIL')],
      ['name' => 'custom', 'label' => ts('Custom')],
    ];
    foreach ($options as $option) {
      CRM_Core_BAO_OptionValue::ensureOptionValueExists([
        'option_group_id' => 'hrleaveandabsences_absence_type_category',
        'name' => $option['name'],
        'label' => $option['label'],
        'is_active' => TRUE,
      ]);
    }
  }
}
