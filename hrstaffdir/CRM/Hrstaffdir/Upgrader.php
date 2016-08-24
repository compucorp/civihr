<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
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
 * Collection of upgrade steps
 */
class CRM_Hrstaffdir_Upgrader extends CRM_Hrstaffdir_Upgrader_Base {

  public function upgrade_1400() {
    $this->ctx->log->info('Applying update 1400');
    $finalTermDate = civicrm_api3('CustomField', 'getvalue', array('custom_group_id' => 'HRJobContract_Summary','name' => 'Final_Termination_Date', 'return' => 'id'));

    //create uffield
    $ufGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrstaffdir_listing', 'id', 'name');
    $ufFieldParam = array(
      'uf_group_id' => $ufGroupID,
      'field_name' => "custom_{$finalTermDate}",
      'is_active' => "1",
      'label' => "Final Termination Date",
      'field_type' => "Individual",
      'is_view' => "1",
      'visibility' => 'Public Pages',
      'is_searchable' => "0",
      'is_selector' => "0"
    );
    $result = civicrm_api3('UFField', 'create', $ufFieldParam);
    _hrstaffdir_phone_type($ufGroupID);
    return TRUE;
  }

  public function upgrade_1401() {
    $ufGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrstaffdir_listing', 'id', 'name');
    $result = civicrm_api3('UFField', 'get', [
      'sequential' => 1,
      'uf_group_id' => $ufGroupID,
      'field_name' => 'hrjobcontract_role_role_department'
    ]);

    // Rename hrjobcontract_role_role_department to hrjc_role_department
    if(!empty($result['values'])) {
      $params = $result['values'][0];
      $params['field_name'] = 'hrjc_role_department';
      try {
        civicrm_api3('UFField', 'create', $params);
      } catch(Exception $ex) {
        return false;
      }
    }

    return true;
  }
}
