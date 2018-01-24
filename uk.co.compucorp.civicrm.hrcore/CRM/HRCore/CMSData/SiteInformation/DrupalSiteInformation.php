<?php

use CRM_HRCore_CMSData_SiteInformation_SiteInformationInterface as SiteInformationInterface;

/**
 * Fetches site information using Drupal's functions.
 */
class CRM_HRCore_CMSData_SiteInformation_DrupalSiteInformation implements SiteInformationInterface {

  /**
   * @inheritdoc
   */
  public function getSiteName() {
    return variable_get('site_name');
  }

}
