<?php

abstract class CRM_Hrjobcontract_Test_Fabricator_BaseAPIFabricator {

  protected static $defaultParams = [
    'sequential' => 1
  ];

  /**
   * Using the civicrm API and the create endpoint, fabricates a new entity
   * with the given params.
   *
   * @param array $params
   *  An array of params that will be passed to the civicrm API

   * @return array
   *  The entity values as they are returned by the API call
   *
   * @throws \Exception
   */
  public static function fabricate($params) {
    if (!isset($params['jobcontract_id'])) {
      throw new Exception('Specify jobcontract_id value');
    }

    $result = civicrm_api3(
      static::getEntityName(),
      'create',
      array_merge(self::$defaultParams, $params)
    );

    return array_shift($result['values']);
  }

  /**
   * Gets the Entity name from the Fabricator class name. It assumes the last
   * part of the class name will be the name of the entity.
   *
   * If the name of a child class doesn't match the standard, then it should
   * override this method to return the right Entity Name.
   *
   * @return string
   */
  protected static function getEntityName() {
    $namespaceParts = explode('_', static::class);
    return end($namespaceParts);
  }
}
