<?php

use CRM_HRCore_CMSData_PathsFactory as CMSPathsFactory;

class CRM_HRCore_UserMenuMarkup {

  /**
   * The contact data used to build the menu
   *
   * @var array
   */
  private $contactData = [];

  /**
   * An instance of a class implementing the CRM_HRCore_CMSData_PathsInterface
   *
   * @var CRM_HRCore_CMSData_PathsInterface
   */
  private $cmsPaths;

  /**
   * An instance of Smarty
   *
   * @var CRM_Core_Smarty
   */
  protected $smarty;

  /**
   * The path of the template owned by this class
   *
   * @var string
   */
  protected $tplPath;

  public function __construct() {
    $this->smarty = CRM_Core_Smarty::singleton();

    $this->instantiateCmsPaths();
    $this->setTemplatePath();
  }

  /**
   *
   *
   */
  public function getMarkup() {
    $this->smarty->assign('username', $this->contactData()['display_name']);
    $this->smarty->assign('image', $this->getUserImagePath());
    $this->smarty->assign('editLink', $this->cmsPaths->getEditAccountPath());
    $this->smarty->assign('logoutLink', $this->cmsPaths->getLogoutPath());

    return $this->smarty->fetch($this->tplPath);
  }

  /**
   * Instantiates the paths class of the current CMS
   */
  private function instantiateCmsPaths() {
    $cmsName = CRM_Core_Config::singleton()->userFramework;

    $this->cmsPaths = CMSPathsFactory::create($cmsName, $this->contactData());
  }

  /**
   * Sets the location the template by using the name
   * of this class to build the path
   */
  private function setTemplatePath() {
    $this->tplPath = strtr(
      CRM_Utils_System::getClassName($this),
      array(
        '_' => DIRECTORY_SEPARATOR,
        '\\' => DIRECTORY_SEPARATOR,
      )
    ) . '.tpl';
  }

  /**
   * Returns the contact data, or fetches it from the api if
   * it's not yet available
   *
   * @return array
   */
  private function contactData() {
    if (empty($this->contactData)) {
      $this->contactData = $this->getContactDataFromApi();
    }

    return $this->contactData;
  }

  /**
   * Fetches the contact data from the API and then
   * normalizes the response
   *
   * @return array
   */
  private function getContactDataFromApi() {
    $rawContactData = civicrm_api3('Contact', 'getsingle', [
      'return' => ['id', 'display_name', 'image_URL'],
      'id' => CRM_Core_Session::getLoggedInContactID(),
      'api.User.getsingle' => ['contact_id' => '$value.contact_id']
    ]);

    return $this->normalizeContactDataAPIResponse($rawContactData);
  }

  /**
   * Normalizes the given contact data, removing any odd structure
   * related to the API response
   *
   * @param array $rawData
   *
   * @return array
   */
  private function normalizeContactDataAPIResponse($rawData) {
    $rawData['cmsId'] = $rawData['api.User.getsingle']['id'];
    unset($rawData['api.User.getsingle']);

    return $rawData;
  }

  /**
   * Returns the path of the user's image, falling back to the CMS's default
   * image if the user doesn't have one
   *
   * @return string
   */
  private function getUserImagePath() {
    if (!empty($this->contactData()['image_URL'])) {
      return $this->contactData()['image_URL'];
    }

    return $this->cmsPaths->getDefaultImagePath();
  }
}
