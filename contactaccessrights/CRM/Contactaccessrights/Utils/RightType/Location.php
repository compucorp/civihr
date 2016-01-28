<?php

class CRM_Contactaccessrights_Utils_RightType_Location
  implements CRM_Contactaccessrights_Utils_RightType_RightTypeInterface {
  const TYPE = 'hrjc_location';

  public function getEntityType() {
    return self::TYPE;
  }
}