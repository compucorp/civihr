<?php

class CRM_HRCore_Listener_Form_ContactImportMapField extends CRM_HRCore_Listener_AbstractListener {

  protected $objectClass = 'CRM_Contact_Import_Form_MapField';

  public function onAlterContent(&$content) {
    if (!$this->canHandle()) {
      return;
    }

    $columnToHide = array(
      'formal_title',
      'job_title',
      'legal_identifier',     //Legal Identifier
      'addressee',            //Addressee
      'addressee_custom',     //Addressee Custom
      'do_not_email',         //Do Not Email
      'do_not_mail',          //Do Not Mail
      'do_not_phone',         //Do Not Phone
      'do_not_sms',           //Do Not Sms
      'do_not_trade',         //Do Not Trade
      'email_greeting',       //Email Greeting
      'email_greeting_custom',//Email Greeting Custom
      'geo_code_1',           //Latitude
      'master_id',            //Master Address Belongs To
      'is_opt_out',           //No Bulk Emails (User Opt Out)
      'openid',               //OpenID
      'postal_greeting',      //Postal Greeting
      'postal_greeting_custom',//Postal Greeting Custom
      'preferred_communication_method',//Preferred Communication Method
      'preferred_language',    //Preferred Language
      'preferred_mail_format',//Preferred Mail Format
      'signature_html',       //Signature Html
      'signature_text',       //Signature Text
      'user_unique_id'        //Unique ID (OpenID)
    );
    $relations = CRM_Contact_BAO_Relationship::getContactRelationshipType(NULL, NULL, NULL, 'Individual', FALSE, 'name', TRUE );
    $relationsToHide = array( 'Benefits Specialist', 'Benefits Specialist is','Case Coordinator','Case Coordinator is','Health Services Coordinator','Health Services Coordinator is','Homeless Services Coordinator','Homeless Services Coordinator is','Senior Services Coordinator','Senior Services Coordinator is', 'Partner of' );
    $hideRelations = array_intersect($relations, $relationsToHide);
    $str = '';

    foreach($columnToHide as $columnToHide) {
      $str .= "$('select[name^=\"mapper\"] option[value={$columnToHide}]').remove();";
    }

    foreach($hideRelations as $columnToHide => $columnName) {
      $str .= "$('select[name^=\"mapper\"] option[value={$columnToHide}]').remove();";
    }

    $content .="<script type=\"text/javascript\">
      CRM.$(function($) {
        {$str};
        $('select[name^=\"mapper\"]').on('change', function() {
          {$str};
        });
      });
    </script>";
  }
}
