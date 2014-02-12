<?php
// $Id$

/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.2                                                 |
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
          array(
            'title' => ts('Name'),
            'required' => TRUE,
          ),
          'id' =>
          array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'gender' => array(),

          'manager' =>
          array(
            'name' => 'sort_name',
            'title' => ts('Manager'),
            'dbAlias' => 'manager.sort_name'
          ),
        ),
        'filters' =>
        array(
          'sort_name' =>
          array(
            'title' => ts('Name'),
            'operator' => 'like',
          ),
          'id' =>
          array(
            'title' => ts('Contact ID'),
            'no_display' => TRUE,
            'type' => CRM_Utils_Type::T_INT,
          ),
          'gender' => array(),
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
        'grouping' => array('contact-fields' => ts('Personal Details')),
      ),
      'civicrm_email' =>
      array(
        'dao' => 'CRM_Core_DAO_Email',
        'fields' =>
        array(
          'email' =>
          array(
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
          array(
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
          array(
            'title' => ts('Work City'),
          ),
          'postal_code' =>
          array(
            'title' => ts('Work Postal Code'),
          ),
          'state_province_id' =>
          array(
            'title' => ts('Work State/Province'),
          ),
          'country_id' =>
          array(
            'title' => ts('Work Country'),
          ),
        ),
        'filters' =>
        array(
          'city' =>
          array(
            'title' => ts('Work City'),
          ),
          'postal_code' =>
          array(
            'title' => ts('Work Postal Code'),
          ),
          'state_province_id' =>
          array(
            'title' => ts('Work State/Province'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' =>
            CRM_Report_Form::OP_MULTISELECT,
            'options' =>
            CRM_Core_PseudoConstant::stateProvince(),
          ),
          'country_id' =>
          array(
            'title' => ts('Work Country'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' =>
            CRM_Report_Form::OP_MULTISELECT,
            'options' =>
            CRM_Core_PseudoConstant::country(),
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

      'civicrm_hrjob' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJob',
        'fields' =>
        array(
          'hrjob_title'         => array(),
          'hrjob_contract_type' => array(),
          'hrjob_level_type'    => array(),
          'hrjob_period_type'   => array(),
          'hrjob_department'    => array(),
          'hrjob_location'      => array(),
          'hrjob_position'      => array(),

          'hrjob_period_start_date' => array(),
          'hrjob_period_end_date'   => array(),
        ),
        'filters' =>
        array(
          'hrjob_title'         => array(),
          'hrjob_contract_type' => array(),
          'hrjob_level_type'    => array(),
          'hrjob_period_type'   => array(),
          'hrjob_department'    => array(),
          'hrjob_location'      => array(),
          'hrjob_position'      => array(),
          'hrjob_period_start_date' => array(),
          'hrjob_period_end_date'   => array(),

          'is_tied_to_funding' =>
          array(
            'title' => ts('Is Tied to Funding'),
            'default' => NULL,
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('' => ts('Any'), '0' => ts('No'), '1' => ts('Yes')),
          ),
        ),
        'grouping' => array('job-fields' => 'Job'),
      ),

      'civicrm_hrjob_health' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobHealth',
        'fields' =>
        array(
          'hrjob_health_provider' => array(),
          'hrjob_health_plan_type' => array(),
          'hrjob_health_provider_life_insurance' => array(),
          'hrjob_life_insurance_plan_type' => array(),
        ),
        'filters' =>
        array(
          'hrjob_health_plan_type' => array(),
          'hrjob_life_insurance_plan_type' => array(),
        ),
        'grouping' => array('job-fields' => 'Job'),
      ),

      'civicrm_hrjob_health_provider' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
        array(
          'organization_name' =>
          array(
            'title' => ts('Job Healthcare Provider'),
            'no_repeat' => TRUE,
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'id' =>
          array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
        'filters' =>
        array(
          'organization_name' =>
          array(
            'title' => ts('Job Healthcare Provider'),
            'operatorType' => CRM_Report_Form::OP_STRING,
          ),
        ),
      ),

      'civicrm_hrjob_health_life_provider' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
        array(
          'display_name' =>
          array('title' => ts('Job life insurance Provider'),
            'no_repeat' => TRUE,
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'id' =>
          array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
        'filters' =>
        array(
          'display_name' =>
          array(
            'title' => ts('Job life insurance Provider'),
            'operatorType' => CRM_Report_Form::OP_STRING,
          ),
        ),
      ),

      'civicrm_hrjob_hour' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobHour',
        'fields' =>
        array(
          'hrjob_hours_type'   => array(),
          'hrjob_hours_amount' => array(),
          'hrjob_hours_unit'   => array(),
          'hrjob_hours_fte'    => array(),
        ),
        'filters' =>
        array(
          'hrjob_hours_type'   => array(),
          'hrjob_hours_amount' => array(),
          'hrjob_hours_unit'   => array(),
          'hrjob_hours_fte'    => array(),
        ),
        'grouping' => array('job-fields' => 'Job'),
      ),

      'civicrm_hrjob_pay' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobPay',
        'fields' =>
        array(
          'hrjob_pay_grade'    => array(),
          'hrjob_pay_amount'   => array(),
          'hrjob_pay_unit'     => array(),
          'hrjob_pay_currency' => array(),
        ),
        'filters' =>
        array(
          'hrjob_pay_grade'  => array(),
          'hrjob_pay_amount' => array(),
          'hrjob_pay_unit'   => array(),
        ),
        'grouping' => array('job-fields' => 'Job'),
      ),

      'civicrm_hrjob_pension' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobPension',
        'fields' =>
        array(
          'hrjob_is_enrolled' => array(),
        ),
        'filters' =>
        array(
          'hrjob_is_enrolled' => array(),
          ),
        'grouping' => array('job-fields' => 'Job'),
      ),

      'civicrm_hrjob_role' =>
      array(
        'dao' => 'CRM_HRJob_DAO_HRJobRole',
        'fields' =>
        array(
          'hrjob_role_department' => array(),
        ),
        'filters' =>
        array(
          'hrjob_role_department' => array(),
        ),
        'grouping' => array('job-fields' => 'Job'),
      ),
    ) + $this->addAddressFields(FALSE, TRUE);

    parent::__construct();
  }

  function from() {
    $this->_from = "
      FROM  civicrm_contact  {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
      LEFT JOIN  civicrm_phone {$this->_aliases['civicrm_phone']}
             ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND
                 {$this->_aliases['civicrm_phone']}.is_primary = 1)
      INNER JOIN civicrm_hrjob {$this->_aliases['civicrm_hrjob']}
             ON ({$this->_aliases['civicrm_hrjob']}.contact_id = {$this->_aliases['civicrm_contact']}.id)
      LEFT JOIN civicrm_hrjob_health {$this->_aliases['civicrm_hrjob_health']}
             ON ({$this->_aliases['civicrm_hrjob_health']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_hrjob_hour {$this->_aliases['civicrm_hrjob_hour']}
             ON ({$this->_aliases['civicrm_hrjob_hour']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_hrjob_pay {$this->_aliases['civicrm_hrjob_pay']}
             ON ({$this->_aliases['civicrm_hrjob_pay']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_hrjob_pension {$this->_aliases['civicrm_hrjob_pension']}
             ON ({$this->_aliases['civicrm_hrjob_pension']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)
      LEFT JOIN civicrm_contact manager
             ON manager.id = ({$this->_aliases['civicrm_hrjob']}.manager_contact_id)
      LEFT JOIN civicrm_contact {$this->_aliases['civicrm_hrjob_health_provider']}
          ON {$this->_aliases['civicrm_hrjob_health_provider']}.id={$this->_aliases['civicrm_hrjob_health']}.provider
      LEFT JOIN civicrm_contact {$this->_aliases['civicrm_hrjob_health_life_provider']}
          ON {$this->_aliases['civicrm_hrjob_health_life_provider']}.id={$this->_aliases['civicrm_hrjob_health']}.provider_life_insurance";

    foreach ($this->_columns as $tableName => $table) {
      if (!empty($table['fields'])) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) ||
            CRM_Utils_Array::value($fieldName, $this->_params['fields'])
          ) {
            if ($tableName == 'civicrm_hrjob_role') {
              $this->_from .= "LEFT JOIN civicrm_hrjob_role {$this->_aliases['civicrm_hrjob_role']}
               ON ({$this->_aliases['civicrm_hrjob_role']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)";
            }
            elseif ($tableName == 'civicrm_hrjob_leave') {
              $this->_from .= "LEFT JOIN civicrm_hrjob_leave {$this->_aliases['civicrm_hrjob_leave']}
               ON ({$this->_aliases['civicrm_hrjob_leave']}.job_id = {$this->_aliases['civicrm_hrjob']}.id)";
            }
          }
        }
      }
    }
    if ($this->_emailField) {
      $this->_from .= "
            LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']}
                   ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id AND
                      {$this->_aliases['civicrm_email']}.is_primary = 1\n";
    }

    if ($this->_addressField) {
      $locationTypes = CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id', array('flip' => TRUE));
      $workLocTypeId = CRM_Utils_Array::value('Work', $locationTypes);
      $this->_from .= "
                 LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
                           ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id) AND
                               {$this->_aliases['civicrm_address']}.location_type_id = {$workLocTypeId}\n";
    }
  }

  function alterDisplay(&$rows) {
    $entryFound = FALSE;
    $gender = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id');

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

      if (array_key_exists('civicrm_hrjob_health_hrjob_health_provider_life_insurance', $row) &&
        array_key_exists('civicrm_hrjob_health_life_provider_id', $row) && array_key_exists('civicrm_hrjob_health_life_provider_display_name', $row)
      ) {
        $url =  CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_hrjob_health_life_provider_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_hrjob_health_hrjob_health_provider_life_insurance'] = $rows[$rowNum]['civicrm_hrjob_health_life_provider_display_name'];
        $rows[$rowNum]['civicrm_hrjob_health_hrjob_health_provider_life_insurance_link'] = $url;
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_hrjob_health_hrjob_health_provider', $row) &&
        array_key_exists('civicrm_hrjob_health_provider_id', $row) && array_key_exists('civicrm_hrjob_health_provider_organization_name', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_hrjob_health_provider_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_hrjob_health_hrjob_health_provider'] = $rows[$rowNum]['civicrm_hrjob_health_provider_organization_name'];
        $rows[$rowNum]['civicrm_hrjob_health_hrjob_health_provider_link'] = $url;
        $entryFound = TRUE;
      }

      $entryFound =
        $this->alterDisplayAddressFields($row, $rows, $rowNum, 'civihr/summary', 'List all contact(s) for this ') ? TRUE : $entryFound;

      if (array_key_exists('civicrm_contact_gender', $row)) {
        if (CRM_Utils_Array::value('civicrm_contact_gender', $row)) {
          $rows[$rowNum]['civicrm_contact_gender'] = CRM_Utils_Array::value($row['civicrm_contact_gender'], $gender);
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
