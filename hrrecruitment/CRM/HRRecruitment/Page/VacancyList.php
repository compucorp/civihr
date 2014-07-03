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

/**
 * Page for displaying list of vacancies
 */
class CRM_HRRecruitment_Page_VacancyList extends CRM_Core_Page {
  function run() {
    CRM_Utils_System::setTitle(ts('Public Vacancy List'));
    global $civicrm_root;
    global $base_url;
    $vacancyId = CRM_Utils_Request::retrieve('id', 'Integer');
    if (empty($vacancyId)) {
      $row = CRM_HRRecruitment_Page_WidgetJs::vacancyListDisplay();
      $this->assign('rows', json_decode($row, true));
      $this->assign('list', 1);

    }else {
      $row = CRM_HRRecruitment_Page_WidgetJs::vacancyInfo($vacancyId);
      $this->assign('rows', json_decode($row, true));
      $this->assign('info', 1);
    }
    parent::run();
  }
}
