<?php

/**
 * The TypeLegend associates a list of absence-types (activity-types) with a list of colors.
 *
 * The colors are not determined directly - but with a slight indirection. So, for example,
 * activity type #10 maps to color #0; activity type #13 maps to color #1; activity type #17
 * maps to color #2; etc.
 *
 * In the presentation layer, we define the precise meaning of colors #0, #1, #2, etc.
 */
class CRM_HRAbsence_TypeLegend {
  private $absenceTypes;
  private $activityTypes;
  private $paletteCount;
  private $paletteSize;

  /**
   * @var array (int $actTypeId => array('label' => $string, 'cssClass' => $string))
   */
  private $map;

  function __construct($paletteSize, $absenceTypes, $activityTypes = NULL) {
    $this->paletteSize = $paletteSize;
    $this->setAbsenceTypes($absenceTypes);
    $this->setActivityTypes($activityTypes);
    $this->build();
  }

  public function build() {
    $this->paletteCount = 0;

    $map = array();
    foreach ($this->absenceTypes as $absenceType) {
      $color = $this->nextColor();
      if (!empty($absenceType['debit_activity_type_id'])) {
        $map[$absenceType['debit_activity_type_id']] = array(
          'label' => $this->activityTypes[$absenceType['debit_activity_type_id']],
          'cssClass' => 'hrabsence-bg-' . $color . '-debit',
        );
      }
      if (!empty($absenceType['credit_activity_type_id'])) {
        $map[$absenceType['credit_activity_type_id']] = array(
          'label' => $this->activityTypes[$absenceType['credit_activity_type_id']],
          'cssClass' => 'hrabsence-bg-' . $color . '-credit',
        );
      }
    }
    $map['mixed'] = array(
      'label' => ts('Multiple'),
      'cssClass' => 'hrabsence-bg-mixed',
    );
    $this->map = $map;
  }

  /**
   * Get the next available color ID
   *
   * @return int the id of the next available color
   */
  protected function nextColor() {
    $r = $this->paletteCount;
    $this->paletteCount = ($this->paletteCount + 1) % $this->paletteSize;
    return $r;
  }

  public function setAbsenceTypes($absenceTypes) {
    $this->absenceTypes = $absenceTypes;
  }

  public function getAbsenceTypes() {
    return $this->absenceTypes;
  }

  public function setActivityTypes($activityTypes) {
    $this->activityTypes = $activityTypes;
  }

  public function getActivityTypes() {
    return $this->activityTypes;
  }

  /**
   * @return array (int $actTypeId => array('label' => $string, 'cssClass' => $string))
   */
  public function getMap() {
    return $this->map;
  }

}