<?php

trait CRM_HRCore_Upgrader_Steps_1017 {

  /**
   * Handles migration of location types
   */
  public function upgrade_1017() {
    $this->up1017_migrateLocationTypes();

    return TRUE;
  }

  /**
   * Migrates location type data from one type to another
   */
  private function up1017_migrateLocationTypes() {
    // All these entities have a reference to location_type_id
    $entityTypesToMigrate = [
      'Address',
      'Email',
      'IM',
      'Mailing',
      'OpenID',
      'MappingField',
      'Phone',
      'UFField'
    ];
    // This is the map of old location type names to new ones
    $locationTypeMap = [
      'Billing' => 'Personal',
      'Home' => 'Personal',
      'Main' => 'Personal',
      'Other' => 'Personal',
    ];
    $locationTypeMap = $this->up1017_convertLocationNamesToIds($locationTypeMap);

    foreach ($locationTypeMap as $oldLocationId => $newLocationId) {
      foreach ($entityTypesToMigrate as $entityName) {
        $this->up1017_updateLocationTypeForEntities(
          $entityName,
          $oldLocationId,
          $newLocationId
        );
      }
    }
  }

  /**
   * Fetch all entities of a given type with a certain location type, then
   * update them to have the new location type
   *
   * @param string $entityName
   * @param int $oldLocationId
   * @param int $newLocationId
   */
  private function up1017_updateLocationTypeForEntities(
    $entityName,
    $oldLocationId,
    $newLocationId
  ) {
    $params = ['location_type_id' => $oldLocationId, 'return' => ['id']];

    // UFField is wacky for updates. It complains about missing fields and
    // does a dupe check that disregards whether you're trying to update, so
    // we return all the existing params for use in the update
    if ($entityName === 'UFField') {
      unset($params['return']);
    }

    $entitiesToMigrate = civicrm_api3($entityName, 'get', $params);

    foreach ($entitiesToMigrate['values'] as $entity) {
      $entity['location_type_id'] = $newLocationId;
      civicrm_api3($entityName, 'create', $entity);
    }
  }

  /**
   * Convert location type names to their IDs. This is required for now since
   * not all API endpoints support sending a string location type
   *
   * @param array $locationTypeMap
   *
   * @return array
   */
  private function up1017_convertLocationNamesToIds($locationTypeMap) {
    $locationTypeIds = civicrm_api3('LocationType', 'get')['values'];
    $locationTypeIds = array_column($locationTypeIds, 'id', 'name');

    foreach ($locationTypeMap as $oldLocationName => $newLocationName) {

      unset($locationTypeMap[$oldLocationName]);
      $oldLocationId = CRM_Utils_Array::value($oldLocationName, $locationTypeIds);
      $newLocationId = CRM_Utils_Array::value($newLocationName, $locationTypeIds);

      if ($oldLocationId && $newLocationId) {
        $locationTypeMap[$oldLocationId] = $newLocationId;
      }
    }

    return $locationTypeMap;
  }

}
