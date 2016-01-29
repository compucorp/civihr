<?php

abstract class CRM_Hrjobcontract_Import_EntityHandler
{
  private $entityName;

  protected function __construct($entityName)
  {
    $this->entityName = $entityName;
  }

  protected function getEntityName() {
    return $this->entityName;
  }

  protected function extractFields(array $params) {
    $entityParams = array();

    foreach($params as $key => $value) {
      if(strpos($key, $this->getEntityName() . '-') !== 0) {
        continue;
      }

      $entityParams[$this->keyToFieldName($key)] = $value;
    }

    return $entityParams;
  }

  /**
   * @param string $key
   * @return string
   */
  private function keyToFieldName($key) {
    return str_replace($this->getEntityName().'-', '', $key);
  }

  /**
   * @param array $params
   * @param \CRM_Hrjobcontract_DAO_HRJobContractRevision $contractRevision
   * @param array $previousRevision
   *
   * @return \CRM_Hrjobcontract_DAO_Base[]
   */
  public abstract function handle(array $params, CRM_Hrjobcontract_DAO_HRJobContractRevision $contractRevision, array &$previousRevision);
}
