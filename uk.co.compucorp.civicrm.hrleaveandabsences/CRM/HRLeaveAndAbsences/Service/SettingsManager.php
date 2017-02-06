<?php

interface CRM_HRLeaveAndAbsences_Service_SettingsManager {
  public function get($setting);
  public function set($setting, $value);
}
