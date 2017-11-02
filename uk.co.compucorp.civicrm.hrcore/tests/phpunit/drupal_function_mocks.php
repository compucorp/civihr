<?php
/***********************************************************
 *  Some of the classes in this extension make calls to a
 *  few Drupal functions. In order to be able to test these
 *  classes without having to bootstrap the CMS, we create
 *  mock them here.
 ***********************************************************/

function drupal_anonymous_user() {}

function user_password() {}

function user_load_by_mail($email) {}

function user_roles() {
  return [1 => 'Fake Role'];
}

function user_role_save($role) {
  $role->rid = 1;
}

function user_role_delete() {}

function user_save($user, $params) {
  $user = new stdClass();
  foreach($params as $key => $value) {
    $user->$key = $value;
  }

  return $user;
}
