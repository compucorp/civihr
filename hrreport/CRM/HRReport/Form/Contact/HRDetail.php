<?php
// $Id$

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
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
class CRM_HRReport_Form_Contact_HRDetail extends CRM_Report_Form {
  //FIXME: extend should be a subtype suitable for CiviHR applicants
  protected $_customGroupExtends = array('Individual');

  function __construct() {
    $this->_exposeContactID = $this->_emailField = FALSE;
    $this->_customGroupJoin = 'INNER JOIN';

    $this->_columns = array(
      'civicrm_contact' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
        array(
          'sort_name' =>
          array('title' => ts('Applicant Name'),
            'required' => TRUE,
          ),
          'id' =>
          array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'gender_id' =>
          array(
            'title' => ts('Gender'),
          ),
        ),
        'filters' =>
        array(
          'sort_name' =>
          array('title' => ts('Applicant Name'),
            'operator' => 'like',
          ),
          'id' =>
          array('title' => ts('Contact ID'),
            'no_display' => TRUE,
            'type' => CRM_Utils_Type::T_INT,
          ),
          'gender_id' =>
          array(
            'title' => ts('Gender'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id'),
          ),
        ),
        'order_bys' =>
        array(
          'sort_name' => array(
            'title' => ts('Last Name, First Name'),
            'default' => '1',
            'default_weight' => '0',
            'default_order' => 'ASC'
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_email' =>
      array(
        'dao' => 'CRM_Core_DAO_Email',
        'fields' =>
        array(
          'email' =>
          array('title' => ts('Applicant Email'),
            'default' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_phone' =>
      array(
        'dao' => 'CRM_Core_DAO_Phone',
        'fields' =>
        array(
          'phone' =>
          array('title' => ts('Applicant Phone'),
            'no_repeat' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_address' =>
      array(
        'dao' => 'CRM_Core_DAO_Address',
        'fields' =>
        array(
          'city' =>
          array('title' => ts('Work City'),
          ),
          'postal_code' =>
          array('title' => ts('Work Postal Code'),
          ),
          'state_province_id' =>
          array('title' => ts('Work State/Province'),
          ),
          'country_id' =>
          array('title' => ts('Work Country'),
          ),
        ),
        'filters' => 
        array(
          'city' =>
          array('title' => ts('Work City'),
          ),
          'postal_code' =>
          array('title' => ts('Work Postal Code'),
          ),
          'state_province_id' =>
          array('title' => ts('Work State/Province'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' =>
            CRM_Report_Form::OP_MULTISELECT,
            'options' =>
            CRM_Core_PseudoConstant::stateProvince(),
          ),
          'country_id' =>
          array('title' => ts('Work Country'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' =>
            CRM_Report_Form::OP_MULTISELECT,
            'options' =>
            CRM_Core_PseudoConstant::country(),
          ),
        ),
      ),
      'civicrm_group' =>
      array(
        'dao' => 'CRM_Contact_DAO_GroupContact',
        'alias' => 'cgroup',
        'filters' =>
        array(
          'gid' =>
          array(
            'name' => 'group_id',
            'title' => ts('Group'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'group' => TRUE,
            'options' => CRM_Core_PseudoConstant::group(),
            'type' => CRM_Utils_Type::T_INT,
          ),
        ),
      ),
    ) + $this->addAddressFields(FALSE, TRUE);

    parent::__construct();
  }

  function preProcess() {
    parent::preProcess();
  }

  function from() {
    $this->_from .= "
      FROM  civicrm_contact  {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
      LEFT JOIN  civicrm_phone {$this->_aliases['civicrm_phone']}
             ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND
                 {$this->_aliases['civicrm_phone']}.is_primary = 1)";

    if ($this->_emailField) {
      $this->_from .= "
            LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']}
                   ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id AND
                      {$this->_aliases['civicrm_email']}.is_primary = 1\n";
    }

    $locationTypes = CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id', array('flip' => TRUE));
    $workLocTypeId = CRM_Utils_Array::value('Work', $locationTypes);

    //FIXME: add address join only when required
    $this->_from  .= "
                 LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
                           ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id) AND
                               {$this->_aliases['civicrm_address']}.location_type_id = {$workLocTypeId}\n";
  }

  function alterDisplay(&$rows) {
    $entryFound = FALSE;
    $gender     = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id');

    foreach ($rows as $rowNum => $row) {
      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        CRM_Utils_Array::value('civicrm_contact_sort_name', $rows[$rowNum]) &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
               'reset=1&cid=' . $row['civicrm_contact_id'],
               $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact Summary for this Contact.");
        $entryFound = TRUE;
       }

      $entryFound = 
        $this->alterDisplayAddressFields($row, $rows, $rowNum, 'civihr/summary', 'List all contact(s) for this ') ? TRUE : $entryFound;

      if (array_key_exists('civicrm_contact_gender_id', $row)) {
        if (CRM_Utils_Array::value('civicrm_contact_gender_id', $row)) {
          $rows[$rowNum]['civicrm_contact_gender_id'] = CRM_Utils_Array::value($row['civicrm_contact_gender_id'], $gender);
        }
        $entryFound = TRUE;
      }

      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        break;
      }
    }
  }
}
