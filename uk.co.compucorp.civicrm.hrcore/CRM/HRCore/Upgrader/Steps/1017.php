<?php

trait CRM_HRCore_Upgrader_Steps_1017 {

  /**
   * Handles default location type settings and migrating existing data
   */
  public function upgrade_1017() {
    $this->up1017_migrateLocationTypes();
    $this->up1017_setDefaultLocationType('Work');
    $this->up1017_reserveLocationTypes(['Work', 'Personal']);
    $this->up1017_disableLocationType('Billing');
    $this->up1017_deleteLocationTypes(['Home', 'Correspondence']);

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
   * Sets the default location type
   *
   * @param string $locationType
   */
  private function up1017_setDefaultLocationType($locationType) {
    $locationTypeId = $this->up1017_getLocationTypeID($locationType);

    civicrm_api3('LocationType', 'create', [
      'id' => $locationTypeId,
      'is_default' => TRUE
    ]);
  }

  /**
   * Sets all given location types as reserved
   *
   * @param array $locationTypes
   */
  private function up1017_reserveLocationTypes($locationTypes) {
    foreach ($locationTypes as $locationType) {
      $locationTypeId = $this->up1017_getLocationTypeID($locationType);
      civicrm_api3('LocationType', 'create', [
        'id' => $locationTypeId,
        'is_reserved' => TRUE
      ]);
    }
  }

  /**
   * Sets the given location type to be not active
   *
   * @param string $locationType
   */
  private function up1017_disableLocationType($locationType) {
    $locationTypeId = $this->up1017_getLocationTypeID($locationType);
    civicrm_api3('LocationType', 'create', [
      'id' => $locationTypeId,
      'is_active' => FALSE
    ]);
  }

  /**
   * Deletes all given location types if they exist
   *
   * @param array $locationTypes
   */
  private function up1017_deleteLocationTypes($locationTypes) {
    foreach ($locationTypes as $locationType) {
      $locationTypeId = $this->up1017_getLocationTypeID($locationType);
      if ($locationTypeId) {
        civicrm_api3('LocationType', 'delete', ['id' => $locationTypeId]);
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
    $locationTypeIds = $this->up1017_getLocationTypeIds();

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

  /**
   * Looks up the location type ID for a given location type name
   *
   * @param $locationTypeName
   *
   * @return int
   */
  private function up1017_getLocationTypeID($locationTypeName) {
    $locationIds = $this->up1017_getLocationTypeIds();

    return (int) CRM_Utils_Array::value($locationTypeName, $locationIds);
  }

  /**
   * Gets a list of location type names mapped to their database ID
   *
   * @return array
   */
  private function up1017_getLocationTypeIds() {
    static $locationTypeIds = [];

    if (empty($locationTypeIds)) {
      $locationTypeIds = civicrm_api3('LocationType', 'get')['values'];
      $locationTypeIds = array_column($locationTypeIds, 'id', 'name');
    }

    return $locationTypeIds;
  }

}
