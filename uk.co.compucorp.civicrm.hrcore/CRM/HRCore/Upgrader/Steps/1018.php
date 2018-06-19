<?php

trait CRM_HRCore_Upgrader_Steps_1018 {

  /**
   * @var array
   */
  private $up1018_locationTypeIds = [];

  /**
   * Handles default location type settings and migrating existing data
   */
  public function upgrade_1018() {
    $this->up1018_createPersonalLocationType();
    $this->up1018_migrateLocationTypes();
    $this->up1018_setDefaultLocationType('Work');
    $this->up1018_reserveLocationTypes(['Work', 'Personal']);
    $this->up1018_disableLocationType('Billing');
    $this->up1018_deleteLocationTypes(['Home', 'Correspondence']);

    return TRUE;
  }

  /**
   * The "Personal" location type should be created by the CiviHR installer
   * script, but it could have been deleted so we need to ensure it exists
   * before using it and making it reserved.
   */
  private function up1018_createPersonalLocationType() {
    $params = [
      'name' => 'Personal',
      'description' => 'Place of Residence',
      'display_name' => 'Personal',
      'vcard_name' => 'PERSONAL',
    ];

    $existingId = $this->up1018_getLocationTypeID('Personal');
    if ($existingId) {
      $params['id'] = $existingId;
    }

    $result = civicrm_api3('LocationType', 'create', $params);

    // update the cached IDs
    $this->up1018_locationTypeIds['Personal'] = $result['id'];
  }

  /**
   * Migrates location type data from one type to another
   */
  private function up1018_migrateLocationTypes() {
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
    $locationTypeMap = $this->up1018_convertLocationNamesToIds($locationTypeMap);

    foreach ($locationTypeMap as $oldLocationId => $newLocationId) {
      foreach ($entityTypesToMigrate as $entityName) {
        $this->up1018_updateLocationTypeForEntities(
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
  private function up1018_setDefaultLocationType($locationType) {
    $locationTypeId = $this->up1018_getLocationTypeID($locationType);

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
  private function up1018_reserveLocationTypes($locationTypes) {
    foreach ($locationTypes as $locationType) {
      $locationTypeId = $this->up1018_getLocationTypeID($locationType);
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
  private function up1018_disableLocationType($locationType) {
    $locationTypeId = $this->up1018_getLocationTypeID($locationType);
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
  private function up1018_deleteLocationTypes($locationTypes) {
    foreach ($locationTypes as $locationType) {
      $locationTypeId = $this->up1018_getLocationTypeID($locationType);
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
  private function up1018_updateLocationTypeForEntities(
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
  private function up1018_convertLocationNamesToIds($locationTypeMap) {
    $locationTypeIds = $this->up1018_getLocationTypeIds();

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
   * @param string $locationTypeName
   *
   * @return int|null
   */
  private function up1018_getLocationTypeID($locationTypeName) {
    $locationIds = $this->up1018_getLocationTypeIds();
    $existing = CRM_Utils_Array::value($locationTypeName, $locationIds);

    return $existing ? (int) $existing : NULL;
  }

  /**
   * Gets a list of location type names mapped to their database ID
   *
   * @return array
   */
  private function up1018_getLocationTypeIds() {
    if (empty($this->up1018_locationTypeIds)) {
      $locationTypeIds = civicrm_api3('LocationType', 'get')['values'];
      $this->up1018_locationTypeIds = array_column($locationTypeIds, 'id', 'name');
    }

    return $this->up1018_locationTypeIds;
  }

}
