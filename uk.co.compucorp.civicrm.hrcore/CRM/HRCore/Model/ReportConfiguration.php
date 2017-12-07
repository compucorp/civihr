<?php

/**
 * Contains properties to represent a report configuration.
 */
class CRM_HRCore_Model_ReportConfiguration {

  /**
   * @var int
   *   The report configuration ID on the host machine
   */
  protected $id;

  /**
   * @var string
   *   The type of name of the report, e.g. "People" or "Leave and Absence"
   */
  protected $name;

  /**
   * @var string
   *   The label to identify the report
   */
  protected $label;

  /**
   * @var string
   *   A JSON string containing all configuration for the report
   */
  protected $jsonConfig;

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
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return $this
   */
  public function setName($name) {
    $this->name = $name;

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

  /**
   * @return string
   */
  public function getJsonConfig() {
    return $this->jsonConfig;
  }

  /**
   * @param string $jsonConfig
   *
   * @return $this
   */
  public function setJsonConfig($jsonConfig) {
    $this->jsonConfig = $jsonConfig;

    return $this;
  }

}
