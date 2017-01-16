<?php

abstract class CRM_HRSampleData_CSVProcessingVisitor
{
  /**
   * Operates on one CSV row data such
   * as (data insertion , deletion .. etc)
   *
   * @param array $row
   *   CSV file row data
   */
  public abstract function visit(array $row);

  /**
   * A wrapper for CiviCRM API.
   *
   * @param string $entity
   *   A valid CiviCRM API entity
   * @param string $action
   *   A valid entity action (e.g : create,delete,get..etc)
   * @param array $params
   *   Parameters to be passed to the API method.
   *
   * @return array
   */
  protected function callAPI($entity, $action, $params) {
    return civicrm_api3($entity, $action, $params);
  }

}
