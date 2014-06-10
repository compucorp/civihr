<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.3                                                 |
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
 * Main page Vacancy Dashboard.
 *
 */
class CRM_HRRecruitment_Page_Dashboard extends CRM_Core_Page {

 public $useLivePageJS = TRUE;

  function run() {
    CRM_Core_Resources::singleton()
      ->addStyleFile('org.civicrm.hrrecruitment', 'css/dashboard.css')
      ->addScriptFile('org.civicrm.hrrecruitment', 'templates/CRM/HRRecruitment/Page/Dashboard.js')
      ->addScriptFile('civicrm', 'packages/momentjs/moment.min.js');
    $vacancies = CRM_HRRecruitment_BAO_HRVacancy::getVacanciesByStatus();
    $recentActivities = CRM_HRRecruitment_BAO_HRVacancy::recentApplicationActivities();
    $this->assign('vacanciesByStatus', $vacancies);
    $this->assign('recentActivities', $recentActivities);
    parent::run();
  }
}

