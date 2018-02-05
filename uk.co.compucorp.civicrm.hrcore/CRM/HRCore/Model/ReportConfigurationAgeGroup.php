<?php

/**
 * Contains properties to represent a report configuration age group.
 */
class CRM_HRCore_Model_ReportConfigurationAgeGroup {

  /**
   * @var int
   *   The ID of the age group on the host machine
   */
  protected $id;


  /**
   * @var int
   *   The age this age group starts from
   */
  protected $ageFrom;

  /**
   * @var int
   *   The age this age group extends to
   */
  protected $ageTo;

  /**
   * @var string
   *   The label for this age group
   */
  protected $label;

  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param int $id
   *
   * @return $this
   */
  public function setId($id) {
    $this->id = $id;

    return $this;
  }

  /**
   * @return int
   */
  public function getAgeFrom() {
    return $this->ageFrom;
  }

  /**
   * @param int $ageFrom
   *
   * @return $this
   */
  public function setAgeFrom($ageFrom) {
    $this->ageFrom = $ageFrom;

    return $this;
  }

  /**
   * @return int
   */
  public function getAgeTo() {
    return $this->ageTo;
  }

  /**
   * @param int $ageTo
   *
   * @return $this
   */
  public function setAgeTo($ageTo) {
    $this->ageTo = $ageTo;

    return $this;
  }

  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * @param string $label
   *
   * @return $this
   */
  public function setLabel($label) {
    $this->label = $label;

    return $this;
  }

}
