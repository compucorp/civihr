<?php

class CRM_HRCore_CMSData_PathsFactory {

  /**
   * Instantiates a Paths class based on the given CMS name
   *
   * @param string $cmsName
   * @param array $contactData
   *
   * @return object
   *
   * @throws Exception if the CMS is not recognized
   */
  public static function create($cmsName, $contactData) {
    $allowedCms = ['Drupal'];

    if (!in_array($cmsName, $allowedCms)) {
      throw new Exception("CMS \"{$cmsName}\" not recognized");
    }

    $className = "CRM_HRCore_CMSData_Paths_${cmsName}";

    return new $className($contactData);
  }
}
