# Contact Access Rights

## Introduction

This extension allows granting permissions to a CiviCRM user, based on his/her location and region. For example, the head of HR may see all data, while the HR manager of a branch may only see staff of the same branch (see [here](http://wiki.civicrm.org/confluence/pages/viewpage.action?pageId=206438407) for more information).

The mockups for the features implemented by the extension can be found [here](http://wiki.civicrm.org/confluence/display/HR/CiviHR+-+proposed+permission+structure).

## Installation

Simply install the extension as a normal CiviCRM extension.

## Usage

Permissions for a contact can be set from the 'Manage roles and teams' menu item, in the action overflow menu on a contact's page. From there, one can define which locations and regions a contact has access to in terms of viewing contacts.

Consequently, when a contact (i.e. a CiviCRM user) logs in, he/she will only be able to view contacts he/she has access to, in accordance with the locations and regions settings defined for him/her. When trying to a contact one doesn't has access to, an error message notification will be shown, followed by redirection to CiviCRM home.

Note: This extension only allows restricting permissions for users who do not have permissions to view or edit all contacts.

## Architecture & Development

The custom permission structure defined by this extension is implemented using `hook_civicrm_aclWhereClause` hook, which has been defined in the extension's main file (i.e. `contactaccessrights.php`). The business logic for building the SQL 'where' clause is delegated to `CRM_Contactaccessrights_Utils_ACL` class, which would make it easy to reuse the logic, in case SQL queries need to be restricted from within Drupal modules as well.

The locations and regions a contact has access to are represented by a custom entity called `Rights` (the DAO class being `CRM_Contactaccessrights_DAO_Rights`), which persists these settings in a table called `civicrm_contactaccessrights_rights`.

In order to make it easy to retrieve the locations and regions a contact has access to, a couple of custom API endpoints have also been defined (as actions `getregions` and `getlocations` under the `Rights` entity).
