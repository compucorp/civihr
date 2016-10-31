<?php

/**
 * Class CRM_StyleGuide_StyleGuides
 *
 * This class manages the list of available style-guides.
 */
class CRM_StyleGuide_StyleGuides {

  /**
   * @var array|NULL
   *   A list of style-guides, indexed by name.
   *   For data-structure, see `add()`.
   *
   * @see CRM_StyleGuide_StyleGuides::add
   */
  private $all = NULL;

  /**
   * Register another style-guide.
   *
   * @param array $styleGuide
   *   Include properties:
   *     - name: string, short machine name
   *     - label: string, translated string
   *     - path: string, local folder
   * @return CRM_StyleGuide_StyleGuides
   * @throws \CRM_Core_Exception
   */
  public function add($styleGuide) {
    if (empty($styleGuide['name']) || empty($styleGuide['label']) || empty($styleGuide['path'])) {
      throw new \CRM_Core_Exception("Malformed style-guide");
    }
    if (!preg_match('/^[a-z-9\_\-]+$/', $styleGuide['name'])) {
      throw new \CRM_Core_Exception("Malformed name in style-guide");
    }
    $this->all[$styleGuide['name']] = $styleGuide;
    return $this;
  }

  /**
   * Get the definition of a specific style-guide by name.
   *
   * @param string $name
   *
   * @return array|NULL
   *   If the style-guide exists, it is an array with properties:
   *     - name: string, short machine name
   *     - label: string, translated string
   *     - path: string, local folder
   */
  public function get($name) {
    $all = $this->getAll();
    return isset($all[$name]) ? $all[$name] : NULL;
  }

  /**
   * Get a list of all style-guides.
   *
   * @return array
   *   A list of style-guides, indexed by name.
   */
  public function getAll() {
    if ($this->all === NULL) {
      $this->init();
    }
    return $this->all;
  }

  /**
   * Get the definition of a specific style-guide by name.
   *
   * @param string $name
   *
   * @return CRM_StyleGuide_StyleGuides
   */
  public function remove($name) {
    if ($this->all) {
      unset($this->all[$name]);
    }
    return $this;
  }

  private function init() {
    $this->all = array();

    $extPath = CRM_Core_Resources::singleton()->getPath('org.civicrm.styleguide');

    $this->add(array(
      'name' => 'crm-star',
      'label' => ts('crm-*'),
      'path' => "{$extPath}/guides/crm-star",
    ));
    $this->add(array(
      'name' => 'bootstrap',
      'label' => ts('Bootstrap'),
      'path' => "{$extPath}/guides/bootstrap",
    ));
    $this->add(array(
      'name' => 'bootstrap-civicrm',
      'label' => ts('Bootstrap-CiviCRM'),
      'path' => "{$extPath}/guides/bootstrap-civicrm",
    ));
    // FIXME: Consider moving declaration to another extension.
    $this->add(array(
      'name' => 'bootstrap-civihr',
      'label' => ts('Bootstrap-CiviHR'),
      'path' => "{$extPath}/guides/bootstrap-civihr",
    ));

    CRM_Utils_Hook::singleton()->invoke(1, $this,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      'civicrm_styleGuides'
    );
  }

}
