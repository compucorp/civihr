<?php

/**
 * Class to set 'Home' address as primary address always.
 */
class CRM_HRCore_Hook_Pre_PrimaryAddressSetter {

  /**
   * If address being saved has a 'Home' location type, it should be set as
   * primary, by altering the given $params array.
   *
   * @param string $op
   *   Operation being done
   * @param string $objectName
   *   Name of the object on which the operation is being done
   * @param int $objectId
   *   ID of the record the object instantiates
   * @param array $params
   *   Parameter array being used to call the operation
   */
  public function handle($op, $objectName, $objectId, &$params) {

    if (!$this->shouldHandle($op, $objectName)) {
      return;
    }

    $homeLocation = civicrm_api3('LocationType', 'getsingle', [
      'name' => 'Home',
    ]);

    if ($params['location_type_id'] == $homeLocation['id']) {
      $params['is_primary'] = 1;
    }
  }

  /**
   * Checks if the hook should be handled.  Only calls to create or edit an
   * address should be.
   *
   * @param string $op
   *   Operation being done
   * @param string $objectName
   *   Name of the object on which the operation is being done
   *
   * @return bool
   *   True if the hook should be handled, false otherwise.
   */
  private function shouldHandle($op, $objectName) {

    if ($objectName === 'Address' && ($op === 'edit' || $op === 'create')) {
      return true;
    }

    return false;
  }
}
