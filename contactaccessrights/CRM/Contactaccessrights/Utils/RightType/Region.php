<?php

class CRM_Contactaccessrights_Utils_RightType_Region
  implements CRM_Contactaccessrights_Utils_RightType_RightTypeInterface {
  const TYPE = 'hrjc_region';

  public function getEntityType() {
    return self::TYPE;
  }
}