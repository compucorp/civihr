<?php
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
class CRM_HRJob_BAO_ReportHook extends CRM_Report_BAO_HookInterface {

  public function alterLogTables(&$logTables) {
    $logTables = 
      array_merge($logTables, 
        array(
          'log_civicrm_hrjob' =>
          array(
            'fk' => 'contact_id',
            'log_type' => 'Job',
          ),
          'log_civicrm_hrjob_hour' =>
          array(
            'fk' => 'contact_id',
            'log_type' => 'Job Hour',
            'joins' => array(
              'table' => 'log_civicrm_hrjob',
              'join' => "entity_log_civireport.job_id = fk_table.id"
            ),
          ),
          'log_civicrm_hrjob_health' =>
          array(
            'fk' => 'contact_id',
            'log_type' => 'Job Health',
            'joins' => array(
              'table' => 'log_civicrm_hrjob',
              'join' => "entity_log_civireport.job_id = fk_table.id"
            ),
          ),
          'log_civicrm_hrjob_leave' =>
          array(
            'fk' => 'contact_id',
            'log_type' => 'Job Leave',
            'joins' => array(
              'table' => 'log_civicrm_hrjob',
              'join' => "entity_log_civireport.job_id = fk_table.id"
            ),
          ),
        )
      );
  }
}
