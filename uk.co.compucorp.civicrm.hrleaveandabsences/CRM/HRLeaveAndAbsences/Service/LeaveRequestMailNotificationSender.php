<?php

use CRM_HRLeaveAndAbsences_Mail_Message as Message;
use CRM_Core_BAO_MessageTemplate as MessageTemplate;

class CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotificationSender {

  /**
   * Send Email using the CRM_Core_BAO_MessageTemplate::sendTemplate method.
   *
   * @param \CRM_HRLeaveAndAbsences_Mail_Message $message
   *
   * @return array
   *  an array containing the status of the mail sent for each recipient
   *  ['test@example.com' => true, 'tester@example.com' => true]
   */
  public function send(Message $message) {

    $recipientEmails = $message->getRecipientEmails();
    $status = [];
    foreach($recipientEmails as $recipient) {

      list($mailSent, $subject, $text, $html) =
        MessageTemplate::sendTemplate([
          'messageTemplateID' => $message->getTemplateID(),
          'contactId' => $message->getLeaveContactID(),
          'tplParams' => $message->getTemplateParameters(),
          'from' => $message->getFromEmail(),
          'toName' => $recipient['api.Contact.get']['values'][0]['display_name'],
          'toEmail' => $recipient['email'],
        ]
      );
      $status[$recipient['email']] = $mailSent;
    }

    return $status;
  }
}
