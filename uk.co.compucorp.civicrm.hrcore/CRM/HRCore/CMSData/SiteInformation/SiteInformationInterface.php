<?php

/**
 * Implement this interface when providing a class to fetch CMS site info
 */
interface CRM_HRCore_CMSData_SiteInformation_SiteInformationInterface {

  /**
   * Gets the site name.
   *
   * @return string
   */
  public function getSiteName();

}
