<?php

use CRM_HRLeaveAndAbsences_Mail_Message as Message;
use CRM_Core_BAO_MessageTemplate as MessageTemplate;
use CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestMailNotificationSenderException as InvalidLeaveRequestMailNotificationSenderException;

class CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotificationSender {

  /**
   * Send Email using the CRM_Core_BAO_MessageTemplate::sendTemplate method.
   *
   * @param \CRM_HRLeaveAndAbsences_Mail_Message $message
   *
   * @return array
   *  an array containing the status of the mail sent for each recipient
   *  ['test@example.com' => true, 'tester@example.com' => true]
   *
   * @throws InvalidLeaveRequestMailNotificationSenderException
   */
  public function send(Message $message) {
    $recipientEmails = $message->getRecipientEmails();
    $templateID = $message->getTemplateID();
    $contactID = $message->getLeaveContactID();
    $fromEmail = $message->getFromEmail();
    $status = [];

    if(is_null($fromEmail)) {
      throw new InvalidLeaveRequestMailNotificationSenderException(
        'From Email Address need to be configured in order to allow Email notifications'
      );
    }

    foreach($recipientEmails as $recipient) {
      $recipientID = $recipient['api.Contact.get']['values'][0]['id'];

      $mailSent =
        MessageTemplate::sendTemplate([
          'messageTemplateID' => $templateID,
          'contactId' => $contactID,
          'tplParams' => $message->getTemplateParameters($recipientID),
          'from' => $fromEmail,
          'toName' => $recipient['api.Contact.get']['values'][0]['display_name'],
          'toEmail' => $recipient['email'],
        ])[0];
      $status[$recipient['email']] = $mailSent;
    }

    return $status;
  }
}
