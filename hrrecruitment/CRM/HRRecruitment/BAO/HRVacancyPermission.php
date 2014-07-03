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

class CRM_HRRecruitment_BAO_HRVacancyPermission extends CRM_HRRecruitment_DAO_HRVacancyPermission {

  /**
   * Based on vacancy ID and/or given permission return list of permission
   * that the logged in user have or return TRUE/FALSE if the loggedin user
   * has permission in the given permission list $checkPermissions respectively
   *
   * @param int   $vacancyID  vacancy ID to retrieve its permission for loggedin user
   * @param array $checkPermissions list of permission to check if loggedin user has any of the permission for given vacancy
   * whose id = $vacancyID
   *
   * @return boolean|array
   * @access public
   * @static
   */

  public static function checkVacancyPermission($vacancyID, $checkPermissions = array(), $defaultPermissions = array()) {
    $session = CRM_Core_Session::singleton();
    $userID = $session->get('userID');
    $vacancyPermissions = array();

    $dao = new self();
    $dao->vacancy_id = $vacancyID;
    $dao->contact_id = $userID;
    $dao->find();

    if (CRM_Core_Permission::check('administer CiviCRM')) {
      if (count($checkPermissions) && in_array('administer CiviCRM', $checkPermissions)) {
        return TRUE;
      }
      else {
        $vacancyPermissions[] = 'administer CiviCRM';
      }
    }

    if (empty($defaultPermissions)) {
      $defaultPermissions[] = $checkPermissions;
    }
    if (count($defaultPermissions) && CRM_Core_Permission::check($defaultPermissions)) {
      return TRUE;
    }

    while ($dao->fetch()) {
      if (count($checkPermissions)) {
        if (in_array($dao->permission, $checkPermissions)) {
          return TRUE;
        }
      }
      else {
        $vacancyPermissions[] = $dao->permission;
      }
    }

    if (count($checkPermissions)) {
      return FALSE;
    }

    return $vacancyPermissions;
  }

  /**
   * Retreive contact ID having specific permission from particular vacancy ID
   *
   * @param int $vacancyID vacancy ID
   * @param String $permissionName
   *
   * @return array found contact IDs for specified permission name
   * @access public
   */
  public static function getPermissionContact($vacancyID, $permissionName) {
    $permissionContactId = array();
    if ($permissionName) {
      $permissiondao = new CRM_HRRecruitment_DAO_HRVacancyPermission;
      $permissiondao->vacancy_id = $vacancyID;
      $permissiondao->permission = $permissionName;
      $permissiondao->find();
      while ($permissiondao->fetch()) {
        $permissionContactId[] = $permissiondao->contact_id;
      }
    }
    return $permissionContactId;
  }
}
