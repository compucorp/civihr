<?php
/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
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
class CRM_Hrjobcontract_Import_Form_DataSource extends CRM_Import_Form_DataSource {
  const PATH = 'civicrm/job/import';
  const IMPORT_ENTITY = 'Import Contracts';

  /**
   * Function to actually build the form - this appears to be entirely code that should be in a shared baseclass in core
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    parent::buildQuickForm();

    $importModeOptions = array();
    $importModeOptions[] = $this->createElement('radio',
      NULL, NULL, ts('Import Contracts'), CRM_Hrjobcontract_Import_Parser::IMPORT_CONTRACTS
    );
    $importModeOptions[] = $this->createElement('radio',
      NULL, NULL, ts('Import Contracts Revision'), CRM_Hrjobcontract_Import_Parser::IMPORT_REVISIONS
    );

    $importModeOptions[] = $this->createElement('radio',
      NULL, NULL, ts('Update Current Contract Entitlements'), CRM_Hrjobcontract_Import_Parser::UPDATE_ENTITLEMENTS
    );

    $this->addGroup($importModeOptions, 'importMode',
      ts('Import Mode')
    );

    $this->setDefaults(array(
      'importMode' =>
        CRM_Hrjobcontract_Import_Parser::IMPORT_CONTRACTS,
    ));

    $this->setDefaults(array(
      'onDuplicate' =>
        CRM_Import_Parser::DUPLICATE_SKIP,
    ));

    $this->setDefaults(array(
        'contactType' =>
          CRM_Import_Parser::CONTACT_INDIVIDUAL,
      )
    );
  }

  /**
   * Process the uploaded file
   *
   * @return void
   * @access public
   */
  public function postProcess() {
    $this->storeFormValues(array(
      'onDuplicate',
      'dateFormats',
      'savedMapping',
      'importMode',
      'contactType',
    ));

    $this->_importMode = $this->get('importMode');

    if($this->_importMode == CRM_Hrjobcontract_Import_Parser::UPDATE_ENTITLEMENTS) {
      $this->submitFileForMapping(CRM_Hrjobcontract_Import_Parser_EntitlementUpdate::class);
    }
    else{
      $this->submitFileForMapping(CRM_Hrjobcontract_Import_Parser_Api::class);
    }

  }
}
