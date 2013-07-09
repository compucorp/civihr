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
class CRM_HRReport_Form_Contact_HRSummary extends CRM_Report_Form {
  //FIXME: extend should be a subtype suitable for CiviHR applicants
  protected $_customGroupExtends = array('Individual');

  function __construct() {
    $this->_emailField = FALSE;
    $this->_customGroupJoin = 'INNER JOIN';
    $this->_customGroupGroupBy = TRUE;
    $this->_exposeContactID    = FALSE;

    $this->_columns = array(
      'civicrm_contact' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          // _exposeContactID already set by default which will expose contact - ID
          'id' =>
          array('title' => ts('People'),
            'default' => TRUE,
            'statistics' => array('count_distinct' => ts('People'),),
          ),
          'gender_id' =>
          array(
            'title' => ts('Gender'),
          ),
        ),
        'group_bys' => array(
          'gender_id' =>
          array(
            'title' => ts('Gender'),
          ),
        ),
        'filters' =>
        array(
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
        'grouping' => array('contact-fields' => 'Stats and Work Fields'),
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
        'group_bys' =>
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
        'grouping' => 'contact-fields',
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
    );

    parent::__construct();

    $customGroupName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Constituent Information', 'table_name', 'title');
    unset($this->_columns[$customGroupName]);

    $jobPositionTable = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Job Positions', 'table_name', 'title');
    $jobPositionColumnID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', 'Name of Job Position', 'id', 'label');
    $jobPositionColumnName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', 'Name of Job Position', 'column_name', 'label');

    $this->_columns[$jobPositionTable]['fields']["custom_{$jobPositionColumnID}_1"] =
      array('name' => $jobPositionColumnName,
        'title' => ts('Job Positions'),
        'default' => TRUE,
        'statistics' => array('count' => ts('Job Positions'),),
        'grouping'   => 'contact-fields',
      );
  }

  function from() {
    $this->_from .= "
      FROM  civicrm_contact  {$this->_aliases['civicrm_contact']} {$this->_aclFrom}";

    $locationTypes = CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id', array('flip' => TRUE));
    $workLocTypeId = CRM_Utils_Array::value('Work', $locationTypes);

    //FIXME: add address join only when required
    $this->_from  .= "
                 LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
                           ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id) AND
                               {$this->_aliases['civicrm_address']}.location_type_id = {$workLocTypeId}\n";
  }

  function statistics(&$rows) {
    $statistics = parent::statistics($rows);

    if (!empty($this->_statFields)) {
      // after removing group-by we have
      $sql = "{$this->_select} {$this->_from} {$this->_where} {$this->_having} {$this->_orderBy} {$this->_limit}";
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ($dao->fetch()) {
        foreach ($this->_statFields as $title => $alias) {
          $statistics['counts'][$alias] = array(
            'title' => $title,
            'value' => $dao->$alias,
            'type' => CRM_Utils_Type::T_INT,
          );
        }
      }
    }
    return $statistics;
  }

  function alterDisplay(&$rows) {
    $entryFound = FALSE;
    $gender     = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id');

    foreach ($rows as $rowNum => $row) {
      $entryFound =
        $this->alterDisplayAddressFields($row, $rows, $rowNum, 'civihr/detail', 'List all contact(s) for this ') ? TRUE : $entryFound;

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

