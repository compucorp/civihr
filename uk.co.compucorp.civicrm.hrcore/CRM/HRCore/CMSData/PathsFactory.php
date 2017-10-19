<?php
  class CRM_HRCore_CMSData_PathsFactory {

    public static function create($cmsName, $contactData) {
      $allowedCms = ['Drupal', 'Wordpress', 'Joomla'];

      if (!in_array($cmsName, $allowedCms)) {
        throw new Exception("CMS \"{$cmsName}\" not recognized", 1);
      }

      $className = "CRM_HRCore_CMSData_Paths${cmsName}";

      return new $className($contactData);
    }
  }
