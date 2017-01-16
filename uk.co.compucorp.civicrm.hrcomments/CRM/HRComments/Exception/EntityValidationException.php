<?php

/**
 * Class CRM_HRComments_Exception_EntityValidationException
 */
class CRM_HRComments_Exception_EntityValidationException extends Exception {
  /**
   * @var string
   *   The field associated with the thrown exception
   */
  private $field;

  /**
   * @var string
   *   The Exception code associated with the thrown exception
   */
  private $exceptionCode;

  /**
   * CRM_HRComments_Exception_EntityValidationException constructor.
   *
   * @param string $message
   * @param string $code
   * @param string $field
   *   The field associated with the thrown exception
   */
  public function __construct($message, $code, $field) {
    $this->field = $field;
    $this->exceptionCode = $code;

    parent::__construct($message);
  }

  /**
   * Getter function for the field property
   *
   * @return string
   */
  public function getField() {
    return $this->field;
  }

  /**
   * Getter function for the exceptionCode property
   *
   * @return string
   */
  public function getExceptionCode() {
    return $this->exceptionCode;
  }

}
