<?php

class CRM_Appraisals_Reminder
{
    public static $_relationalValues = array();
    
    public static function setRelationalValues()
    {
        if (empty(self::$_relationalValues))
        {
            self::$_relationalValues['appraisal_statuses'] = CRM_Core_OptionGroup::values('appraisal_status');
            self::$_relationalValues['appraisal_cycle_types'] = CRM_Core_OptionGroup::values('appraisal_cycle_type');
        }
    }
    
    public static function sendReminder($appraisalId, $notes = null, $isReminder = false)
    {
        self::setRelationalValues();
        $emailToContactId = array();
        $now = date('Y-m-d');
        
        $appraisalResult = civicrm_api3('Appraisal', 'get', array(
          'sequential' => 1,
          'id' => $appraisalId,
          'is_current' => 1,
        ));
        $appraisalResultValues = CRM_Utils_Array::first($appraisalResult['values']);
        
        $appraisalCycleResult = civicrm_api3('AppraisalCycle', 'get', array(
            'sequential' => 1,
            'id' => (int)$appraisalResultValues['appraisal_cycle_id'],
        ));
        $appraisalCycleResultValues = CRM_Utils_Array::first($appraisalCycleResult['values']);
        
        $contacts = array();
        $recipients = array();
        
        $due = $appraisalResultValues['self_appraisal_due'];
        if ($now > $appraisalResultValues['self_appraisal_due']) {
            $due = $appraisalResultValues['manager_appraisal_due'];
        }
        if ($now > $appraisalResultValues['manager_appraisal_due']) {
            $due = $appraisalResultValues['grade_due'];
        }
        
        if (!empty($appraisalResultValues['contact_id'])) {
            $contacts['contact_id'] = $appraisalResultValues['contact_id'];
        }
        if (!empty($appraisalResultValues['manager_id'])) {
            $contacts['manager_id'] = $appraisalResultValues['manager_id'];
        }
        
        foreach ($contacts as $key => $value) {
            $links = array();
            $names = array();
            $emails = array();
            
            $contactResult = civicrm_api3('Contact', 'get', array(
              'return' => 'display_name',
              'id' => $value,
            ));
            
            $emailResult = civicrm_api3('Email', 'get', array(
              'return' => 'contact_id,email',
              'contact_id' => $value,
              'is_primary' => 1,
            ));
            
            foreach ($contactResult['values'] as $contactKey => $contactValue)
            {
                $url = CIVICRM_UF_BASEURL . '/civicrm/contact/view?reset=1&cid=' . $contactKey;
                $links[] = '<a href="' . $url . '" style="color:#42b0cb;font-weight:normal;text-decoration:underline;">' . $contactValue['display_name'] . '</a>';
                $names[] = $contactValue['display_name'];
            }
            
            foreach ($emailResult['values'] as $contactValue)
            {
                $emails[] = $contactValue['email'];
                $emailToContactId[$contactValue['email']] = $contactValue['contact_id'];
            }
            
            $recipients[$key]['links'] = $links;
            $recipients[$key]['names'] = $names;
            $recipients[$key]['emails'] = $emails;
        }
        
        $template = &CRM_Core_Smarty::singleton();
        foreach ($recipients as $key => $recipient)
        {
            $email = $recipient['emails'][0];
            $contactId = $emailToContactId[$email];
            //$activityName = implode(', ', $activityContact[3]['names']) . ' - ' . self::$_activityOptions['type'][$activityResult['activity_type_id']];
            $title = 'Appraisal (Status: ' . self::$_relationalValues['appraisal_statuses'][$appraisalResultValues['status_id']] . ')';
            $templateBodyHTML = $template->fetchWith('CRM/Appraisals/Reminder/Reminder.tpl', array(
                'isReminder' => $isReminder,
                'notes' => $notes,
                'appraisalCycleId' => $appraisalCycleResultValues['id'],
                'appraisalCycleName' => $appraisalCycleResultValues['cycle_name'],
                //'appraisalCycleActive' => $appraisalCycleResult[''],
                'appraisalCyclePeriod' => $appraisalCycleResultValues['cycle_start_date'] . ' - ' . $appraisalCycleResultValues['cycle_end_date'],
                'contact' => $recipients['contact_id']['links'][0],
                'line_manager' => $recipients['manager_id']['links'][0],
                'cycle_type' => self::$_relationalValues['appraisal_cycle_types'][$appraisalCycleResultValues['cycle_type_id']],
                'status' => self::$_relationalValues['appraisal_statuses'][$appraisalResultValues['status_id']],
                'due' => $due,
            ));
            
            $email = 'tristan992@gmail.com';
            self::_send($contactId, $email, $title, $templateBodyHTML);
        }

        return true;
    }
    
    public static function getContactIds($startDate = null, $endDate = null)
    {
        $result = array();
        
        if (!$startDate)
        {
            $startDate = date('Y-m-d');
        }
        if (!$endDate)
        {
            $endDate = date('Y') . '-12-31';
        }
        
        $appraisalContactsIds = CRM_Core_DAO::executeQuery(
            "SELECT contact_id, manager_id FROM civicrm_appraisal WHERE "
            . "(self_appraisal_due IS NOT NULL AND ("
            . " self_appraisal_due >= CAST('{$startDate}' AS DATE) AND "
            . " self_appraisal_due <= CAST('{$endDate}' AS DATE) "
            . ")) OR "
            . "(manager_appraisal_due IS NOT NULL AND ("
            . " manager_appraisal_due >= CAST('{$startDate}' AS DATE) AND "
            . " manager_appraisal_due <= CAST('{$endDate}' AS DATE) "
            . ")) OR "
            . "(grade_due IS NOT NULL AND ("
            . " grade_due >= CAST('{$startDate}' AS DATE) AND "
            . " grade_due <= CAST('{$endDate}' AS DATE) "
            . ")) "
        );
        while ($appraisalContactsIds->fetch())
        {
            $result[] = $appraisalContactsIds->contact_id;
            $result[] = $appraisalContactsIds->manager_id;
        }
        
        return array_unique($result);
    }
    
    public static function get($from, $to, $contactId)
    {
        self::setRelationalValues();
        
        $result = array();
        $now = date('Y-m-d');
        
        $appraisals = CRM_Core_DAO::executeQuery(
            "SELECT a.*, ac.cycle_type_id FROM civicrm_appraisal a "
            . "INNER JOIN civicrm_appraisal_cycle ac ON ac.id = a.appraisal_cycle_id "
            . "WHERE "
            . "(contact_id = {$contactId} OR manager_id = {$contactId}) AND ("
            . "(self_appraisal_due IS NOT NULL AND ("
            . " self_appraisal_due >= CAST('{$from}' AS DATE) AND "
            . " self_appraisal_due <= CAST('{$to}' AS DATE) "
            . ")) OR "
            . "(manager_appraisal_due IS NOT NULL AND ("
            . " manager_appraisal_due >= CAST('{$from}' AS DATE) AND "
            . " manager_appraisal_due <= CAST('{$to}' AS DATE) "
            . ")) OR "
            . "(grade_due IS NOT NULL AND ("
            . " grade_due >= CAST('{$from}' AS DATE) AND "
            . " grade_due <= CAST('{$to}' AS DATE) "
            . "))) "
        );
        while ($appraisals->fetch())
        {
            $contactResult = civicrm_api3('Contact', 'get', array(
              'return' => 'display_name',
              'id' => $appraisals->contact_id,
            ));
            $contactResultValues = CRM_Utils_Array::first($contactResult['values']);
            
            $managerResult = civicrm_api3('Contact', 'get', array(
              'return' => 'display_name',
              'id' => $appraisals->manager_id,
            ));
            $managerResultValues = CRM_Utils_Array::first($managerResult['values']);
            
            $due = $appraisals->self_appraisal_due;
            if ($now > $appraisals->self_appraisal_due) {
                $due = $appraisals->manager_appraisal_due;
            }
            if ($now > $appraisals->manager_appraisal_due) {
                $due = $appraisals->grade_due;
            }
            
            $result[$appraisals->id] = array(
                'appraisal_url' => '#',//TODO!
                'contact_name' => $contactResultValues['display_name'],
                'manager_name' => $managerResultValues['display_name'],
                'cycle_type' => self::$_relationalValues['appraisal_cycle_types'][$appraisals->cycle_type_id],
                'status' => self::$_relationalValues['appraisal_statuses'][$appraisals->status_id],
                'due' => $due,
            );
        }
        
        return $result;
    }
    
    /**
     * @param int $contactId
     * @param string $email
     * @param string $template
     * @param string $html
     *
     * @return bool
     */
    private static function _send($contactId, $email, $body_subject, $body_html)
    {
        $domain     = CRM_Core_BAO_Domain::getDomain();
        $result     = false;
        $hookTokens = array();
        
        $domainValues = array();
        $domainValues['name'] = CRM_Utils_Token::getDomainTokenReplacement('name', $domain);
        
        $domainValue = CRM_Core_BAO_Domain::getNameAndEmail();
        $domainValues['email'] = $domainValue[1];
        $receiptFrom = '"' . $domainValues['name'] . '" <' . $domainValues['email'] . '>';
        
        $body_text = CRM_Utils_String::htmlToText($body_html);
        
        $params = array(array('contact_id', '=', $contactId, 0, 0));
        list($contact, $_) = CRM_Contact_BAO_Query::apiQuery($params);

        $contact = reset($contact);

        if (!$contact || is_a($contact, 'CRM_Core_Error'))
        {
            return NULL;
        }

        // get tokens to be replaced
        $tokens = array_merge(CRM_Utils_Token::getTokens($body_text),
                              CRM_Utils_Token::getTokens($body_html),
                              CRM_Utils_Token::getTokens($body_subject));

        // get replacement text for these tokens
        $returnProperties = array("preferred_mail_format" => 1);
        if (isset($tokens['contact']))
        {
            foreach ($tokens['contact'] as $key => $value)
            {
                $returnProperties[$value] = 1;
            }
        }
        list($details) = CRM_Utils_Token::getTokenDetails(array($contactId),
                                                          $returnProperties,
                                                          null, null, false,
                                                          $tokens,
                                                          'CRM_Core_BAO_MessageTemplate');
        $contact = reset( $details );

        // call token hook
        $hookTokens = array();
        CRM_Utils_Hook::tokens($hookTokens);
        $categories = array_keys($hookTokens);

        // do replacements in text and html body
        $type = array('html', 'text');
        foreach ($type as $key => $value)
        {
            $bodyType = "body_{$value}";
            if ($$bodyType)
            {
                CRM_Utils_Token::replaceGreetingTokens($$bodyType, NULL, $contact['contact_id']);
                $$bodyType = CRM_Utils_Token::replaceDomainTokens($$bodyType, $domain, true, $tokens, true);
                $$bodyType = CRM_Utils_Token::replaceContactTokens($$bodyType, $contact, false, $tokens, false, true);
                $$bodyType = CRM_Utils_Token::replaceComponentTokens($$bodyType, $contact, $tokens, true);
                $$bodyType = CRM_Utils_Token::replaceHookTokens($$bodyType, $contact , $categories, true);
            }
        }
        $html = $body_html;
        $text = $body_text;

        $smarty = CRM_Core_Smarty::singleton();
        foreach (array('text', 'html') as $elem)
        {
            $$elem = $smarty->fetch("string:{$$elem}");
        }

        // do replacements in message subject
        $messageSubject = CRM_Utils_Token::replaceContactTokens($body_subject, $contact, false, $tokens);
        $messageSubject = CRM_Utils_Token::replaceDomainTokens($messageSubject, $domain, true, $tokens);
        $messageSubject = CRM_Utils_Token::replaceComponentTokens($messageSubject, $contact, $tokens, true);
        $messageSubject = CRM_Utils_Token::replaceHookTokens($messageSubject, $contact, $categories, true);

        $messageSubject = $smarty->fetch("string:{$messageSubject}");

        // set up the parameters for CRM_Utils_Mail::send
        $mailParams = array(
          'groupName' => 'Scheduled Reminder Sender',
          'from' => $receiptFrom,
          'toName' => !empty($contact['display_name']) ? $contact['display_name'] : $email,
          'toEmail' => $email,
          'subject' => $messageSubject,
        );
        if (!$html || $contact['preferred_mail_format'] == 'Text' ||
          $contact['preferred_mail_format'] == 'Both'
        )
        {
            // render the &amp; entities in text mode, so that the links work
            $mailParams['text'] = str_replace('&amp;', '&', $text);
        }
        if ($html && ($contact['preferred_mail_format'] == 'HTML' ||
            $contact['preferred_mail_format'] == 'Both'
          ))
        {
            $mailParams['html'] = $html;
        }

        $result = CRM_Utils_Mail::send($mailParams);
        
        return $result;
    }
}
