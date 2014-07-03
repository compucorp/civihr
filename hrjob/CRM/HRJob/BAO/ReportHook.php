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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_HRJob_BAO_ReportHook extends CRM_Report_BAO_HookInterface {

  public function alterLogTables(&$reportObj, &$logTables) {
    if ($reportObj instanceof CRM_Report_Form_Contact_LoggingDetail) {
      $logTables[] = 'civicrm_hrjob';
      $logTables[] = 'civicrm_hrjob_health';
      $logTables[] = 'civicrm_hrjob_hour';
      $logTables[] = 'civicrm_hrjob_leave';
      $logTables[] = 'civicrm_hrjob_pay';
      $logTables[] = 'civicrm_hrjob_pension';
      $logTables[] = 'civicrm_hrjob_role';
    } else if ($reportObj instanceof CRM_Report_Form_Contact_LoggingSummary) {
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
            'log_civicrm_hrjob_pay' =>
            array(
              'fk' => 'contact_id',
              'log_type' => 'Job Pay',
              'joins' => array(
                'table' => 'log_civicrm_hrjob',
                'join' => "entity_log_civireport.job_id = fk_table.id"
              ),
            ),
            'log_civicrm_hrjob_role' =>
            array(
              'fk' => 'contact_id',
              'log_type' => 'Job Role',
              'joins' => array(
                'table' => 'log_civicrm_hrjob',
                'join' => "entity_log_civireport.job_id = fk_table.id"
              ),
            ),
            'log_civicrm_hrjob_pension' =>
            array(
              'fk' => 'contact_id',
              'log_type' => 'Job Pension',
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

  public function logDiffClause(&$reportObj, $table) {
    $contactIdClause = $join = '';
    if ($reportObj instanceof CRM_Logging_Differ) {
      switch ($table) {
        case 'civicrm_hrjob_health':
        case 'civicrm_hrjob_hour':
        case 'civicrm_hrjob_leave':
        case 'civicrm_hrjob_pay':
        case 'civicrm_hrjob_pension':
        case 'civicrm_hrjob_role':
          $join  = "
INNER JOIN civicrm_hrjob hrjob ON hrjob.id = lt.job_id";
          $contactIdClause = "AND hrjob.contact_id = %3";
          break;
      }
    }
    return array($contactIdClause, $join);
  }
}
