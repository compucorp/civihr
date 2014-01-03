<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * This class gets the name of the file to upload
 */
class CRM_HRJob_Import_Form_MapFieldBaseClass extends CRM_Import_Form_MapField {
  protected $_highlightedFields = array();
  /**
   * Fields to remove from the field mapping if 'On Duplicate Update is selected
   * @var array
   */
  protected $_onDuplicateUpdateRemove = array();
  /**
   * Fields to highlight in the field mapping if 'On Duplicate Update is selected
   * @var array
   */
  protected $_onDuplicateUpdateHighlight = array();
  /**
   * Fields to highlight in the field mapping if 'On Duplicate Skip' or On Duplicate No Check is selected
   * @var array
   */
  protected $_onDuplicateSkipHighlight = array();

  /**
   * name of option value in mapping type group that holds possible option values
   * @var array
   */
  protected $_mappingType = '';

  /**
   * Function to set variables up before form is built
   *
   * @return void
   * @access public
   */
  public function preProcess() {
    $this->_mapperFields = $this->get('fields');
    $this->_entity = $this->get('_entity');

    $v = $this->_mapperFields;
    asort($this->_mapperFields);
    $this->_columnCount = $this->get('columnCount');
    $this->assign('columnCount', $this->_columnCount);
    $this->_dataValues = $this->get('dataValues');
    $this->assign('dataValues', $this->_dataValues);

    $skipColumnHeader   = $this->controller->exportValue('DataSource', 'skipColumnHeader');
    $this->_onDuplicate = $this->get('onDuplicate');
    if ($skipColumnHeader) {
      $this->assign('skipColumnHeader', $skipColumnHeader);
      $this->assign('rowDisplayCount', 3);
      /* if we had a column header to skip, stash it for later */
      $this->_columnHeaders = $this->_dataValues[0];
    }
    else {
      $this->assign('rowDisplayCount', 2);
    }
    $this->doDuplicateOptionHandling();
    $this->assign('highlightedFields', $this->_highlightedFields);
  }

  /**
   * Here we add or remove fields based on the selected duplicate option
   */
  function doDuplicateOptionHandling() {
    if ($this->_onDuplicate == CRM_Import_Parser::DUPLICATE_UPDATE) {
      foreach ($this->_onDuplicateUpdateRemove as $value) {
        unset($this->_mapperFields[$value]);
      }
      foreach ($this->__onDuplicateUpdateHighlight as $name) {
        $this->_highlightedFields[] = $name;
      }
    }
    elseif ($this->_onDuplicate == CRM_Import_Parser::DUPLICATE_SKIP ||
        $this->_onDuplicate == CRM_Import_Parser::DUPLICATE_NOCHECK
    ) {
      $this->_highlightedFields = $this->_highlightedFields + $this->_onDuplicateUpdateHighlight;
    }
  }
  /**
   * Function to actually build the form
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
    //to save the current mappings
    if (!$this->get('savedMapping')) {
      $saveDetailsName = ts('Save this field mapping');
      $this->applyFilter('saveMappingName', 'trim');
      $this->add('text', 'saveMappingName', ts('Name'));
      $this->add('text', 'saveMappingDesc', ts('Description'));
    }
    else {
      $savedMapping = $this->get('savedMapping');

      list($mappingName) = CRM_Core_BAO_Mapping::getMappingFields($savedMapping);

      $mappingName        = $mappingName[1];

      //mapping is to be loaded from database
      $params         = array('id' => $savedMapping);
      $temp           = array();
      $mappingDetails = CRM_Core_BAO_Mapping::retrieve($params, $temp);

      $this->assign('loadedMapping', $mappingDetails->name);
      $this->set('loadedMapping', $savedMapping);

      $getMappingName = new CRM_Core_DAO_Mapping();
      $getMappingName->id = $savedMapping;
      $getMappingName->mapping_type = $this->_mappingType;
      $getMappingName->find();
      while ($getMappingName->fetch()) {
        $mapperName = $getMappingName->name;
      }

      $this->assign('savedName', $mapperName);

      $this->add('hidden', 'mappingId', $savedMapping);

      $this->addElement('checkbox', 'updateMapping', ts('Update this field mapping'), NULL);
      $saveDetailsName = ts('Save as a new field mapping');
      $this->add('text', 'saveMappingName', ts('Name'));
      $this->add('text', 'saveMappingDesc', ts('Description'));
    }

    $this->addElement('checkbox', 'saveMapping', $saveDetailsName, NULL, array('onclick' => "showSaveDetails(this)"));
    $this->addFormRule(array('CRM_HRJob_Import_Form_MapFieldBaseClass', 'formRule'), $this);
    //-------- end of saved mapping stuff ---------


    $defaults         = array();
    $mapperKeys       = array_keys($this->_mapperFields);
    $hasHeaders       = !empty($this->_columnHeaders);
    $headerPatterns   = $this->get('headerPatterns');
    $dataPatterns     = $this->get('dataPatterns');
    $hasLocationTypes = $this->get('fieldTypes');

    /* Initialize all field usages to false */

    foreach ($mapperKeys as $key) {
      $this->_fieldUsed[$key] = FALSE;
    }
    $sel1 = $this->_mapperFields;

    $sel2[''] = NULL;
    $js       = "<script type='text/javascript'>\n";
    $formName = 'document.forms.' . $this->_name;
    // this next section used to warn for mismatch column count or mismatch mapping
    $warning = 0;
    for ($i = 0; $i < $this->_columnCount; $i++) {
      $sel = &$this->addElement('hierselect', "mapper[$i]", ts('Mapper for Field %1', array(1 => $i)), NULL);
      $jsSet = FALSE;
      if ($this->get('savedMapping')) {
        if (isset($mappingName[$i])) {
            $defaults["mapper[$i]"] = array();
        }
        else {
          // this load section to help mapping if we ran out of saved columns when doing Load Mapping
          if ($hasHeaders) {
            $defaults["mapper[$i]"] = array($this->defaultFromHeader($this->_columnHeaders[$i], $headerPatterns));
          }
          else {
            $defaults["mapper[$i]"] = array($this->defaultFromData($dataPatterns, $i));
          }
        }
        //end of load mapping
      }
      else {
        if ($hasHeaders) {
          // Infer the default from the skipped headers if we have them
          $defaults["mapper[$i]"] = array(
            $this->defaultFromHeader($this->_columnHeaders[$i],
              $headerPatterns
            ), 0,
          );
        }
        else {
          // Otherwise guess the default from the form of the data
          $defaults["mapper[$i]"] = array(
            $this->defaultFromData($dataPatterns, $i), 0,
          );
        }
      }
      $sel->setOptions(array($sel1));
    }
    $js .= "</script>\n";
    $this->assign('initHideBoxes', $js);

    //set warning if mismatch in more than
    if (isset($mappingName)) {
      if (($this->_columnCount != count($mappingName))) {
        $warning++;
      }
    }

    if ($warning != 0 && $this->get('savedMapping')) {
      $session = CRM_Core_Session::singleton();
      $session->setStatus(ts('The data columns in this import file appear to be different from the saved mapping. Please verify that you have selected the correct saved mapping before continuing.'));
    }
    else {
      $session = CRM_Core_Session::singleton();
      $session->setStatus(NULL);
    }

    $this->setDefaults($defaults);
    $this->addButtons(array(
        array(
          'type' => 'back',
          'name' => ts('<< Previous'),
        ),
        array(
          'type' => 'next',
          'name' => ts('Continue >>'),
          'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      )
    );
  }

  /**
   * global validation rules for the form
   *
   * @param array $fields posted values of the form
   *
   * @return array list of errors to be posted back to the form
   * @static
   * @access public
   */
  static function formRule($fields, $files, $self) {
    $errors = array();
    $fieldMessage = NULL;
    if (!array_key_exists('savedMapping', $fields)) {
      $importKeys = array();
      foreach ($fields['mapper'] as $mapperPart) {
        $importKeys[] = $mapperPart[0];
      }
      $requiredFields = array(
        'contact_id' => ts('Contact ID'),
      );
      foreach ($requiredFields as $field => $title) {
        if (!in_array($field, $importKeys)) {
          if (!isset($errors['_qf_default'])) {
            $errors['_qf_default'] = '';
          }
          $errors['_qf_default'] .= ts('Missing required field: %1', array(1 => $title));
        }
      }
    }

    if (CRM_Utils_Array::value('saveMapping', $fields)) {
      $nameField = CRM_Utils_Array::value('saveMappingName', $fields);
      if (empty($nameField)) {
        $errors['saveMappingName'] = ts('Name is required to save Import Mapping');
      }
      else {
        $mappingTypeId = CRM_Core_OptionGroup::getValue('mapping_type', $self->_mappingType, 'name');
        if (CRM_Core_BAO_Mapping::checkMapping($nameField, $mappingTypeId)) {
          $errors['saveMappingName'] = ts('Duplicate ' . $self->_mappingType . 'Mapping Name');
        }
      }
    }

    //display Error if loaded mapping is not selected
    if (array_key_exists('loadMapping', $fields)) {
      $getMapName = CRM_Utils_Array::value('savedMapping', $fields);
      if (empty($getMapName)) {
        $errors['savedMapping'] = ts('Select saved mapping');
      }
    }
    if (!empty($errors)) {
      if (!empty($errors['saveMappingName'])) {
        $_flag = 1;       
        $assignError = new CRM_Core_Page();
        $assignError->assign('mappingDetailsError', $_flag);
      }
      return $errors;    
    }
    return TRUE;
  }

  /**
   * Process the mapped fields and map it into the uploaded file
   * preview the file and extract some summary statistics
   *
   * @return void
   * @access public
   */
  public function postProcess() {
    $params = $this->controller->exportValues('MapField');
    //reload the mapfield if load mapping is pressed
    if (!empty($params['savedMapping'])) {
      $this->set('savedMapping', $params['savedMapping']);
      $this->controller->resetPage($this->_name);
      return;
    }

    $fileName = $this->controller->exportValue('DataSource', 'uploadFile');
    $skipColumnHeader = $this->controller->exportValue('DataSource', 'skipColumnHeader');

    $config = CRM_Core_Config::singleton();
    $separator = $config->fieldSeparator;

    $mapperKeys     = array();
    $mapper         = array();
    $mapperKeys     = $this->controller->exportValue($this->_name, 'mapper');
    $mapperKeysMain = array();

    for ($i = 0; $i < $this->_columnCount; $i++) {
      $mapper[$i] = $this->_mapperFields[$mapperKeys[$i][0]];
      $mapperKeysMain[$i] = $mapperKeys[$i][0];
    }

    $this->set('mapper', $mapper);

    // store mapping Id to display it in the preview page
    $this->set('loadMappingId', CRM_Utils_Array::value('mappingId', $params));

    //Updating Mapping Records
    if (CRM_Utils_Array::value('updateMapping', $params)) {

      $mappingFields = new CRM_Core_DAO_MappingField();
      $mappingFields->mapping_id = $params['mappingId'];
      $mappingFields->find();

      $mappingFieldsId = array();
      while ($mappingFields->fetch()) {
        if ($mappingFields->id) {
          $mappingFieldsId[$mappingFields->column_number] = $mappingFields->id;
        }
      }

      for ($i = 0; $i < $this->_columnCount; $i++) {
        $updateMappingFields = new CRM_Core_DAO_MappingField();
        $updateMappingFields->id = $mappingFieldsId[$i];
        $updateMappingFields->mapping_id = $params['mappingId'];
        $updateMappingFields->column_number = $i;

        $explodedValues = explode('_', $mapperKeys[$i][0]);
        $id             = CRM_Utils_Array::value(0, $explodedValues);
        $first          = CRM_Utils_Array::value(1, $explodedValues);
        $second         = CRM_Utils_Array::value(2, $explodedValues);

        $updateMappingFields->name = $mapper[$i];
        $updateMappingFields->save();
      }
    }

    //Saving Mapping Details and Records
    if (CRM_Utils_Array::value('saveMapping', $params)) {
      $mappingParams = array(
        'name' => $params['saveMappingName'],
        'description' => $params['saveMappingDesc'],
        'mapping_type_id' => CRM_Core_OptionGroup::getValue('mapping_type',
          $this->_mappingType,
          'name'
        ),
      );
      $saveMapping = CRM_Core_BAO_Mapping::add($mappingParams);

      for ($i = 0; $i < $this->_columnCount; $i++) {
        $saveMappingFields = new CRM_Core_DAO_MappingField();
        $saveMappingFields->mapping_id = $saveMapping->id;
        $saveMappingFields->column_number = $i;

        $explodedValues = explode('_', $mapperKeys[$i][0]);
        $id             = CRM_Utils_Array::value(0, $explodedValues);
        $first          = CRM_Utils_Array::value(1, $explodedValues);
        $second         = CRM_Utils_Array::value(2, $explodedValues);

        $saveMappingFields->name = $mapper[$i];
        $saveMappingFields->save();
      }
      $this->set('savedMapping', $saveMappingFields->mapping_id);
    }

    $this->set('_entity', $this->_entity);

    $parser = new $this->_parser($mapperKeysMain);
    $parser->setEntity($this->_entity);
    $parser->run($fileName, $separator, $mapper, $skipColumnHeader,
      CRM_Import_Parser::MODE_PREVIEW, $this->get('contactType')
    );
    // add all the necessary variables to the form
    $parser->set($this);
  }
}