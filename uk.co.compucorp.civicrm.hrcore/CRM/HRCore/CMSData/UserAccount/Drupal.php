<?php

use CRM_HRCore_CMSData_UserAccountInterface as UserAccountInterface;

/**
 * Class CRM_HRCore_CMSData_UserAccount_Drupal
 */
class CRM_HRCore_CMSData_UserAccount_Drupal implements UserAccountInterface {

  /**
   * Gets the Drupal user object
   *
   * @param array $contactData
   *
   * @return \stdClass
   */
  private function getUser($contactData) {
    return user_load($contactData['cmsId']);
  }

  /**
   * Cancel the user account by deleting the account
   * and make its content belong to the Anonymous user.
   *
   * @param array $contactData
   *
   * @return mixed
   */
  public function cancel($contactData) {
    $user = $this->getUser($contactData);

    $result = user_cancel(
      [
        'user_cancel_notify' => FALSE,
        'user_cancel_method' => 'user_cancel_reassign',
      ],
      $user->uid,
      'user_cancel_reassign'
    );

    $batch = &batch_get();
    $batch['progressive'] = FALSE;
    batch_process();

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function disable($contactData) {
    $user = $this->getUser($contactData);

    return user_save($user, ['status' => 0]);
  }

  /**
   * {@inheritdoc}
   */
  public function enable($contactData) {
    $user = $this->getUser($contactData);

    return user_save($user, ['status' => 1]);
  }

  /**
   * {@inheritdoc}
   */
  public function isUserDisabled($contactData) {
    $user = $this->getUser($contactData);

    return !$user->status ? true : false;
  }
}
