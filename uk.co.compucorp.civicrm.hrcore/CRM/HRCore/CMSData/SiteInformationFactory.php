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
    $userFramework = CRM_Core_Config::singleton()->userFramework;

    switch ($userFramework) {
      case 'Drupal':
        return new DrupalSiteInformation();

      default:
        $msg = sprintf('Unrecognized system "%s"', $userFramework);
        throw new \Exception($msg);
    }
  }

}
