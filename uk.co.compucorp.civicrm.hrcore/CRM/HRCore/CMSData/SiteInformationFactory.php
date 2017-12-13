<?php

use CRM_HRCore_CMSData_SiteInformation_SiteInformationInterface as SiteInformationInterface;
use CRM_HRCore_CMSData_SiteInformation_DrupalSiteInformation as DrupalSiteInformation;

/**
 * Responsible for creating a service to fetch site info depending on the CMS.
 */
class CRM_HRCore_CMSData_SiteInformationFactory {

  /**
   * Returns a service to fetch CMS site information
   *
   * @return SiteInformationInterface
   */
  public static function create() {
    $userSystem = CRM_Core_Config::singleton()->userSystem;

    switch (get_class($userSystem)) {
      case CRM_Utils_System_Drupal::class:
        return new DrupalSiteInformation();

      default:
        $msg = sprintf('Unrecognized system "%s"', get_class($userSystem));
        throw new \Exception($msg);
    }
  }

}
