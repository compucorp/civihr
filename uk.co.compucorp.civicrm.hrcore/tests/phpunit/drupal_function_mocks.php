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

function user_load($userID) {
  $user = new stdClass();
  $user->roles = [1 => 'Fake Role'];
  $user->uid = $userID;
  $user->status = 1;

  if($userID == 0) {
    $user->status = 0;
  }

  return $user;
}

function _user_mail_notify($operation, $user) {
  return ['user' => $user, 'operation' => $operation];
}

function user_cancel($params, $uid, $method) {
  return ['params' => $params, 'uid' => $uid, 'method' => $method];
}

function &batch_get() {
  $batch = ['progressive' => ''];

  return $batch;
}

function batch_process() {}
