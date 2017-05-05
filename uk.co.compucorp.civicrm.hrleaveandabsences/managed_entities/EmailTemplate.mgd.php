<?php

return [
  [
    'name' => 'Email Template: Leave Request',
    'entity' => 'MessageTemplate',
    'params' => [
      'version' => 3,
      'msg_title' => 'CiviHR Leave Request Notification',
      'msg_subject' => 'Leave Request',
      'msg_text' => 'CiviHR Leave RequestLeave Request Type{ts}Status:{/ts}{$leaveStatus}{ts}Staff Member:{/ts}{contact.display_name}{if $leaveRequest->from_date eq $leaveRequest->to_date}{ts}Date:{/ts}{$fromDate|truncate:10:\'\'|crmDate}{$fromDateType}{else}{ts}From Date:{/ts}{$fromDate|truncate:10:\'\'|crmDate}{$fromDateType}{ts}To Date:{/ts}{$toDate|truncate:10:\'\'|crmDate}{$toDateType}{/if}View This Request{if $leaveComments}Request Comments{foreach from=$leaveComments item=value key=label}{$value.commenter}:{$value.created_at|crmDate}{$value.text}{/foreach}{/if}{if $leaveFiles}Other files recorded on this request{foreach from=$leaveFiles item=value key=label}{$value.name}: Added on{$value.upload_date|crmDate}{/foreach}{/if}',
      'msg_html' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <title>CiviHR Leave Request</title>
    <style>{literal}@media only screen {
  html {
    min-height: 100%;
    background: #E8EEF0;
  }
}

@media only screen and (max-width: 596px) {
  .small-text-left {
    text-align: left !important;
  }
}

@media only screen and (max-width: 596px) {
  table.body img {
    width: auto;
    height: auto;
  }

  table.body center {
    min-width: 0 !important;
  }

  table.body .container {
    width: 95% !important;
  }

  table.body .columns {
    height: auto !important;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    padding-left: 16px !important;
    padding-right: 16px !important;
  }

  table.body .collapse .columns {
    padding-left: 0 !important;
    padding-right: 0 !important;
  }

  th.small-12 {
    display: inline-block !important;
    width: 100% !important;
  }
}

@media only screen and (max-width: 596px) {
  .email-date {
    line-height: normal !important;
  }
}

@media only screen and (max-width: 596px) {
  .request-data dt {
    float: none !important;
    width: auto !important;
  }
}{/literal}</style>
  </head>
  <body style="-moz-box-sizing: border-box; -ms-text-size-adjust: 100%; -webkit-box-sizing: border-box; -webkit-text-size-adjust: 100%; Margin: 0; box-sizing: border-box; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; min-width: 100%; padding: 0; text-align: left; width: 100% !important;">
    <span class="preheader" style="color: #E8EEF0; display: none !important; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; mso-hide: all !important; opacity: 0; overflow: hidden; visibility: hidden;"></span>
    <table class="body" style="Margin: 0; background: #E8EEF0; border-collapse: collapse; border-spacing: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; height: 100%; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
      <tr style="padding: 0; text-align: left; vertical-align: top;">
        <td class="center" align="center" valign="top" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
          <center class="email" data-parsed="" style="Margin: 40px 0; margin: 40px 0; min-width: 580px; width: 100%;">
            <!-- header -->
            <table align="center" class="container email-heading float-center" style="Margin: 0 auto; background: transparent; border-collapse: collapse; border-spacing: 0; float: none; margin: 0 auto; padding: 0; text-align: center; vertical-align: top; width: 580px;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;"><td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
              <table class="row collapse" style="border-collapse: collapse; border-spacing: 0; display: table; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;">
                <th class="small-12 large-6 columns first" style="Margin: 0 auto; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0; padding-right: 0; text-align: left; width: 298px;"><table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0; text-align: left;">
                  <h1 class="email-title" style="Margin: 0; Margin-bottom: 0; border-left: 5px solid #42AFCB; color: #4D5663; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 500; line-height: 40px; margin: 0; margin-bottom: 0; padding: 0; padding-left: 15px; text-align: left; word-wrap: normal;">CiviHR Leave Request</h1>
                </th></tr></table></th>
                <th class="small-12 large-6 columns last" style="Margin: 0 auto; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0; padding-right: 0; text-align: left; width: 298px;"><table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0; text-align: left;">
                  <p class="email-date small-text-left text-right" style="Margin: 0; Margin-bottom: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 40px; margin: 0; margin-bottom: 0; padding: 0; text-align: right;">{$currentDateTime->format("D d F Y")}</p>
                </th></tr></table></th>
              </tr></tbody></table>
            </td></tr></tbody></table>
            <table class="spacer float-center" style="Margin: 0 auto; border-collapse: collapse; border-spacing: 0; float: none; margin: 0 auto; padding: 0; text-align: center; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;"><td height="16px" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 16px; margin: 0; mso-line-height-rule: exactly; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">&#xA0;</td></tr></tbody></table>
            <!-- body -->
            <table align="center" class="container float-center" style="Margin: 0 auto; background: transparent; border-collapse: collapse; border-spacing: 0; float: none; margin: 0 auto; padding: 0; text-align: center; vertical-align: top; width: 580px;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;"><td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">

              <div class="request">
                <table class="row collapse" style="border-collapse: collapse; border-spacing: 0; display: table; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;">
                  <th class="small-12 large-12 columns first last" style="Margin: 0 auto; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0; padding-right: 0; text-align: left; width: 588px;"><table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0; text-align: left;">
                    <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                      <tr style="padding: 0; text-align: left; vertical-align: top;">
                        <td class="callout-header" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #F3F6F7; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 15px 20px; text-align: left; vertical-align: top; word-wrap: break-word;">
                          <h2 class="callout-title" style="Margin: 0; Margin-bottom: 0; color: #4D5663; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 500; line-height: 1.53846; margin: 0; margin-bottom: 0; padding: 0; text-align: left; word-wrap: normal;">Leave Request Type</h2>
                        </td>
                      </tr>
                    </table>

                    <table class="callout" style="Margin-bottom: 16px; border-collapse: collapse; border-spacing: 0; margin-bottom: 16px; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th class="callout-inner request-data" style="Margin: 0; background: #FFF; border: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 20px; text-align: left; width: 100%;">
                      <dl style="Margin: 0; margin: 0;">
                        <dt style="color: #4D5663; float: left; font-weight: 600; width: 150px;">{ts}Status:{/ts}</dt>
                        <dd style="Margin-bottom: 10px; Margin-left: 0; margin-bottom: 10px; margin-left: 0;">{$leaveStatus}</dd>
                        <dt style="color: #4D5663; float: left; font-weight: 600; width: 150px;">{ts}Staff Member:{/ts}</dt>
                        <dd style="Margin-bottom: 10px; Margin-left: 0; margin-bottom: 10px; margin-left: 0;">{contact.display_name}</dd>
                        {if $leaveRequest->from_date eq $leaveRequest->to_date}
                          <dt style="color: #4D5663; float: left; font-weight: 600; width: 150px;">Date:</dt>
                          <dd style="Margin-bottom: 10px; Margin-left: 0; margin-bottom: 10px; margin-left: 0;">{$fromDate|truncate:10:\'\'|crmDate} {$fromDateType}</dd>
                        {else}
                          <dt style="color: #4D5663; float: left; font-weight: 600; width: 150px;">{ts}From Date:{/ts}</dt>
                          <dd style="Margin-bottom: 10px; Margin-left: 0; margin-bottom: 10px; margin-left: 0;">{$fromDate|truncate:10:\'\'|crmDate} {$fromDateType}</dd>
                          <dt style="color: #4D5663; float: left; font-weight: 600; width: 150px;">{ts}To Date:{/ts}</dt>
                          <dd style="Margin-left: 0; margin-left: 0;">{$toDate|truncate:10:\'\'|crmDate} {$toDateType}</dd>
                        {/if}
                      </dl>
                    </th><th class="expander" style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th></tr></table>
                    <table class="button expanded alert" style="Margin: 0 0 16px 0; border-collapse: collapse; border-spacing: 0; margin: 0 0 16px 0; padding: 0; text-align: left; vertical-align: top; width: 100% !important;"><tr style="padding: 0; text-align: left; vertical-align: top;"><td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;"><table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #E6807F; border: 0px solid #E6807F; border-collapse: collapse !important; color: #FFF; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;"><center data-parsed="" style="min-width: 0; width: 100%;"><a href="{$leaveRequestLink}" align="center" class="float-center" style="Margin: 0; border: 0 solid #E6807F; border-radius: 3px; color: #FFF; display: inline-block; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 8px 16px 8px 16px; padding-left: 0; padding-right: 0; text-align: center; text-decoration: none; width: 100%;">View This Request</a></center></td></tr></table></td>
<td class="expander" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; vertical-align: top; visibility: hidden; width: 0; word-wrap: break-word;"></td></tr></table>
                    {if $leaveComments}
                      <hr style="Margin: 20px auto; border: 0; border-top: 1px solid #D3DEE2; height: 0; margin: 20px auto;">
                      <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                        <tr style="padding: 0; text-align: left; vertical-align: top;">
                          <td class="callout-header" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #F3F6F7; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 15px 20px; text-align: left; vertical-align: top; word-wrap: break-word;">
                            <h2 class="callout-title" style="Margin: 0; Margin-bottom: 0; color: #4D5663; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 500; line-height: 1.53846; margin: 0; margin-bottom: 0; padding: 0; text-align: left; word-wrap: normal;">Request comments</h2>
                          </td>
                        </tr>
                      </table>

                      <table class="callout" style="Margin-bottom: 16px; border-collapse: collapse; border-spacing: 0; margin-bottom: 16px; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th class="callout-inner callout-no-padding" style="Margin: 0; background: #FFF; border: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0; text-align: left; width: 100%;">
                        <div class="request-comments">
                          {foreach from=$leaveComments item=value key=label}
                            <div class="request-comment" style="border-bottom: 1px solid #E8EEF0; padding: 20px;">
                              <div class="request-comment-header" style="Margin-bottom: 10px; color: #4D5663; font-weight: 600; margin-bottom: 10px;">
                                <span class="request-comment-author">{$value.commenter}:</span>
                                <span class="request-comment-datetime">{$value.created_at|crmDate}</span>
                              </div>
                              <div class="request-comment-body">
                                {$value.text}
                              </div>
                            </div>
                          {/foreach}
                        </div>
                      </th><th class="expander" style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th></tr></table>
                    {/if}
                    {if $leaveFiles}
                      <hr style="Margin: 20px auto; border: 0; border-top: 1px solid #D3DEE2; height: 0; margin: 20px auto;">
                      <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                        <tr style="padding: 0; text-align: left; vertical-align: top;">
                          <td class="callout-header" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #F3F6F7; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 15px 20px; text-align: left; vertical-align: top; word-wrap: break-word;">
                            <h2 class="callout-title" style="Margin: 0; Margin-bottom: 0; color: #4D5663; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 500; line-height: 1.53846; margin: 0; margin-bottom: 0; padding: 0; text-align: left; word-wrap: normal;">Other files recorded in this request</h2>
                          </td>
                        </tr>
                      </table>

                      <table class="callout" style="Margin-bottom: 16px; border-collapse: collapse; border-spacing: 0; margin-bottom: 16px; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th class="callout-inner" style="Margin: 0; background: #FFF; border: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 20px; text-align: left; width: 100%;">
                        <div class="request-attachments">
                          {foreach from=$leaveFiles item=value key=label}
                            <div class="request-attachment" style="Margin-bottom: 10px; margin-bottom: 10px;">
                              <span class="request-attachment-name" style="color: #4D5663; font-weight: 600;">{$value.name}</span>: Added on {$value.upload_date|crmDate}
                            </div>
                          {/foreach}
                        </div>
                      </th><th class="expander" style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th></tr></table>
                    {/if}
                  </th>
<th class="expander" style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th></tr></table></th>
                </tr></tbody></table>
                <table class="row collapse" style="border-collapse: collapse; border-spacing: 0; display: table; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;">
                  <th class="small-12 large-12 columns first last" style="Margin: 0 auto; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0; padding-right: 0; text-align: left; width: 588px;"><table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0; text-align: left;">
                    <table class="spacer" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;"><td height="16px" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 16px; margin: 0; mso-line-height-rule: exactly; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">&#xA0;</td></tr></tbody></table>
                    <img class="text-center email-logo" src="https://civihr.org/sites/default/files/email-logo.png" style="-ms-interpolation-mode: bicubic; Margin: 0 auto; clear: both; display: block; float: none; margin: 0 auto; max-width: 100%; outline: none; text-align: center; text-decoration: none; width: 100px;">
                  </th>
<th class="expander" style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th></tr></table></th>
                </tr></tbody></table>
              </div>

            </td></tr></tbody></table>
          </center>
        </td>
      </tr>
    </table>
    <!-- prevent Gmail on iOS font size manipulation -->
   <div style="display:none; white-space:nowrap; font:15px courier; line-height:0;"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </div>
  </body>
</html>',
      'is_reserved' => 1
    ],
  ],
  [
    'name' => 'Email Template: TOIL Request',
    'entity' => 'MessageTemplate',
    'params' => [
      'version' => 3,
      'msg_title' => 'CiviHR TOIL Request Notification',
      'msg_subject' => 'TOIL Request',
      'msg_text' => 'CiviHR TOIL RequestLeave Request Type{ts}Status:{/ts}{$leaveStatus}{ts}Staff Member:{/ts}{contact.display_name}{if $leaveRequest->from_date eq $leaveRequest->to_date}{ts}Date:{/ts}{$fromDate|truncate:10:\'\'|crmDate}{else}{ts}From Date:{/ts}{$fromDate|truncate:10:\'\'|crmDate}{ts}To Date:{/ts}{$toDate|truncate:10:\'\'|crmDate}{/if}{ts}No. TOIL Days Requested{/ts}{$leaveRequest->toil_to_accrue}{if $leaveRequest->toil_to_accrue > 1}days{else}day{/if}View This Request{if $leaveComments}Request Comments{foreach from=$leaveComments item=value key=label}{$value.commenter}:{$value.created_at|crmDate}{$value.text}{/foreach}{/if}{if $leaveFiles}Other files recorded on this request{foreach from=$leaveFiles item=value key=label}{$value.name}: Added on{$value.upload_date|crmDate}{/foreach}{/if}',
      'msg_html' => '<html><head><title></title></head><body><h3>CiviHR TOIL Request</h3><h4>{$currentDateTime->format("D d F Y")}</h4><p><strong>Leave Request Type</strong></p><table><tbody><tr><td>{ts}Status:{/ts}</td><td>{$leaveStatus}</td></tr><tr><td>{ts}Staff Member:{/ts}</td><td>{contact.display_name}</td></tr>{if $leaveRequest->from_date eq $leaveRequest->to_date}<tr><td>{ts}Date:{/ts}</td><td>{$fromDate|truncate:10:\'\'|crmDate}</td></tr>{else}<tr><td>{ts}From Date:{/ts}</td><td>{$fromDate|truncate:10:\'\'|crmDate}</td></tr><tr><td>{ts}To Date:{/ts}</td><td>{$toDate|truncate:10:\'\'|crmDate}</td></tr>{/if}<tr><td>{ts}No. TOIL Days Requested{/ts}</td><td>{$leaveRequest->toil_to_accrue}{if $leaveRequest->toil_to_accrue > 1}days{else}day{/if}</td></tr></tbody></table><p><a href="{$leaveRequestLink}">View This Request</a></p>{if $leaveComments}<p><strong>Request Comments</strong></p><table border="0" cellpadding="1" cellspacing="1" style="width: 700px;"><tbody>{foreach from=$leaveComments item=value key=label}<tr><td>{$value.commenter}:{$value.created_at|crmDate}</td></tr><tr><td>{$value.text}</td></tr>{/foreach}</tbody></table>{/if}<p></p>{if $leaveFiles}<p><b>Other files recorded on this request</b></p><table border="0" cellpadding="1" cellspacing="1" style="width: 700px";><tbody>{foreach from=$leaveFiles item=value key=label}<tr><td>{$value.name}: Added on{$value.upload_date|crmDate}</td></tr>{/foreach}</tbody></table>{/if}</body></html>',
      'msg_html' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <title>CiviHR TOIL Request</title>
    <style>{literal}@media only screen {
  html {
    min-height: 100%;
    background: #E8EEF0;
  }
}

@media only screen and (max-width: 596px) {
  .small-text-left {
    text-align: left !important;
  }
}

@media only screen and (max-width: 596px) {
  table.body img {
    width: auto;
    height: auto;
  }

  table.body center {
    min-width: 0 !important;
  }

  table.body .container {
    width: 95% !important;
  }

  table.body .columns {
    height: auto !important;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    padding-left: 16px !important;
    padding-right: 16px !important;
  }

  table.body .collapse .columns {
    padding-left: 0 !important;
    padding-right: 0 !important;
  }

  th.small-12 {
    display: inline-block !important;
    width: 100% !important;
  }
}

@media only screen and (max-width: 596px) {
  .email-date {
    line-height: normal !important;
  }
}

@media only screen and (max-width: 596px) {
  .request-data dt {
    float: none !important;
    width: auto !important;
  }
}{/literal}</style>
  </head>
  <body style="-moz-box-sizing: border-box; -ms-text-size-adjust: 100%; -webkit-box-sizing: border-box; -webkit-text-size-adjust: 100%; Margin: 0; box-sizing: border-box; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; min-width: 100%; padding: 0; text-align: left; width: 100% !important;">
    <span class="preheader" style="color: #E8EEF0; display: none !important; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; mso-hide: all !important; opacity: 0; overflow: hidden; visibility: hidden;"></span>
    <table class="body" style="Margin: 0; background: #E8EEF0; border-collapse: collapse; border-spacing: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; height: 100%; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
      <tr style="padding: 0; text-align: left; vertical-align: top;">
        <td class="center" align="center" valign="top" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
          <center class="email" data-parsed="" style="Margin: 40px 0; margin: 40px 0; min-width: 580px; width: 100%;">
            <!-- header -->
            <table align="center" class="container email-heading float-center" style="Margin: 0 auto; background: transparent; border-collapse: collapse; border-spacing: 0; float: none; margin: 0 auto; padding: 0; text-align: center; vertical-align: top; width: 580px;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;"><td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
              <table class="row collapse" style="border-collapse: collapse; border-spacing: 0; display: table; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;">
                <th class="small-12 large-6 columns first" style="Margin: 0 auto; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0; padding-right: 0; text-align: left; width: 298px;"><table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0; text-align: left;">
                  <h1 class="email-title" style="Margin: 0; Margin-bottom: 0; border-left: 5px solid #42AFCB; color: #4D5663; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 500; line-height: 40px; margin: 0; margin-bottom: 0; padding: 0; padding-left: 15px; text-align: left; word-wrap: normal;">CiviHR TOIL Request</h1>
                </th></tr></table></th>
                <th class="small-12 large-6 columns last" style="Margin: 0 auto; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0; padding-right: 0; text-align: left; width: 298px;"><table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0; text-align: left;">
                  <p class="email-date small-text-left text-right" style="Margin: 0; Margin-bottom: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 40px; margin: 0; margin-bottom: 0; padding: 0; text-align: right;">{$currentDateTime->format("D d F Y")}</p>
                </th></tr></table></th>
              </tr></tbody></table>
            </td></tr></tbody></table>
            <table class="spacer float-center" style="Margin: 0 auto; border-collapse: collapse; border-spacing: 0; float: none; margin: 0 auto; padding: 0; text-align: center; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;"><td height="16px" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 16px; margin: 0; mso-line-height-rule: exactly; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">&#xA0;</td></tr></tbody></table>
            <!-- body -->
            <table align="center" class="container float-center" style="Margin: 0 auto; background: transparent; border-collapse: collapse; border-spacing: 0; float: none; margin: 0 auto; padding: 0; text-align: center; vertical-align: top; width: 580px;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;"><td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">

              <div class="request">
                <table class="row collapse" style="border-collapse: collapse; border-spacing: 0; display: table; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;">
                  <th class="small-12 large-12 columns first last" style="Margin: 0 auto; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0; padding-right: 0; text-align: left; width: 588px;"><table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0; text-align: left;">
                    <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                      <tr style="padding: 0; text-align: left; vertical-align: top;">
                        <td class="callout-header" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #F3F6F7; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 15px 20px; text-align: left; vertical-align: top; word-wrap: break-word;">
                          <h2 class="callout-title" style="Margin: 0; Margin-bottom: 0; color: #4D5663; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 500; line-height: 1.53846; margin: 0; margin-bottom: 0; padding: 0; text-align: left; word-wrap: normal;">Leave Request Type</h2>
                        </td>
                      </tr>
                    </table>

                    <table class="callout" style="Margin-bottom: 16px; border-collapse: collapse; border-spacing: 0; margin-bottom: 16px; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th class="callout-inner request-data request-data-large" style="Margin: 0; background: #FFF; border: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 20px; text-align: left; width: 100%;">
                      <dl style="Margin: 0; margin: 0;">
                        <dt style="color: #4D5663; float: left; font-weight: 600; width: 200px;">{ts}Status:{/ts}</dt>
                        <dd style="Margin-bottom: 10px; Margin-left: 0; margin-bottom: 10px; margin-left: 0;">{$leaveStatus}</dd>
                        <dt style="color: #4D5663; float: left; font-weight: 600; width: 200px;">{ts}Staff Member:{/ts}</dt>
                        <dd style="Margin-bottom: 10px; Margin-left: 0; margin-bottom: 10px; margin-left: 0;">{contact.display_name}</dd>
                        {if $leaveRequest->from_date eq $leaveRequest->to_date}
                          <dt style="color: #4D5663; float: left; font-weight: 600; width: 200px;">Date:</dt>
                          <dd style="Margin-bottom: 10px; Margin-left: 0; margin-bottom: 10px; margin-left: 0;">{$fromDate|truncate:10:\'\'|crmDate} {$fromDateType}</dd>
                        {else}
                          <dt style="color: #4D5663; float: left; font-weight: 600; width: 200px;">{ts}From Date:{/ts}</dt>
                          <dd style="Margin-bottom: 10px; Margin-left: 0; margin-bottom: 10px; margin-left: 0;">{$fromDate|truncate:10:\'\'|crmDate} {$fromDateType}</dd>
                          <dt style="color: #4D5663; float: left; font-weight: 600; width: 200px;">{ts}To Date:{/ts}</dt>
                          <dd style="Margin-bottom: 10px; Margin-left: 0; margin-bottom: 10px; margin-left: 0;">{$toDate|truncate:10:\'\'|crmDate} {$toDateType}</dd>
                        {/if}
                        <div class="request-data-toil" style="line-height: 2.5;">
                          <dt style="color: #4D5663; float: left; font-weight: 600; width: 200px;">{ts}No. TOIL Days Requested:{/ts}</dt>
                          <dd style="Margin-left: 0; margin-left: 0;"><span style="border: 1px solid #727E8A; display: inline-block; text-align: center; width: 100px;">{$leaveRequest->toil_to_accrue}{if $leaveRequest->toil_to_accrue > 1}days{else}day{/if}</span></dd>
                        </div>
                      </dl>
                    </th><th class="expander" style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th></tr></table>
                    <table class="button expanded alert" style="Margin: 0 0 16px 0; border-collapse: collapse; border-spacing: 0; margin: 0 0 16px 0; padding: 0; text-align: left; vertical-align: top; width: 100% !important;"><tr style="padding: 0; text-align: left; vertical-align: top;"><td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;"><table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #E6807F; border: 0px solid #E6807F; border-collapse: collapse !important; color: #FFF; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;"><center data-parsed="" style="min-width: 0; width: 100%;"><a href="{$leaveRequestLink}" align="center" class="float-center" style="Margin: 0; border: 0 solid #E6807F; border-radius: 3px; color: #FFF; display: inline-block; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 8px 16px 8px 16px; padding-left: 0; padding-right: 0; text-align: center; text-decoration: none; width: 100%;">View This Request</a></center></td></tr></table></td>
<td class="expander" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; vertical-align: top; visibility: hidden; width: 0; word-wrap: break-word;"></td></tr></table>
                    {if $leaveComments}
                      <hr style="Margin: 20px auto; border: 0; border-top: 1px solid #D3DEE2; height: 0; margin: 20px auto;">
                      <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                        <tr style="padding: 0; text-align: left; vertical-align: top;">
                          <td class="callout-header" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #F3F6F7; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 15px 20px; text-align: left; vertical-align: top; word-wrap: break-word;">
                            <h2 class="callout-title" style="Margin: 0; Margin-bottom: 0; color: #4D5663; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 500; line-height: 1.53846; margin: 0; margin-bottom: 0; padding: 0; text-align: left; word-wrap: normal;">Request comments</h2>
                          </td>
                        </tr>
                      </table>

                      <table class="callout" style="Margin-bottom: 16px; border-collapse: collapse; border-spacing: 0; margin-bottom: 16px; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th class="callout-inner callout-no-padding" style="Margin: 0; background: #FFF; border: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0; text-align: left; width: 100%;">
                        <div class="request-comments">
                          {foreach from=$leaveComments item=value key=label}
                            <div class="request-comment" style="border-bottom: 1px solid #E8EEF0; padding: 20px;">
                              <div class="request-comment-header" style="Margin-bottom: 10px; color: #4D5663; font-weight: 600; margin-bottom: 10px;">
                                <span class="request-comment-author">{$value.commenter}:</span>
                                <span class="request-comment-datetime">{$value.created_at|crmDate}</span>
                              </div>
                              <div class="request-comment-body">
                                {$value.text}
                              </div>
                            </div>
                          {/foreach}
                        </div>
                      </th><th class="expander" style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th></tr></table>
                    {/if}
                    {if $leaveFiles}
                      <hr style="Margin: 20px auto; border: 0; border-top: 1px solid #D3DEE2; height: 0; margin: 20px auto;">
                      <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                        <tr style="padding: 0; text-align: left; vertical-align: top;">
                          <td class="callout-header" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #F3F6F7; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; hyphens: auto; line-height: 1.53846; margin: 0; padding: 15px 20px; text-align: left; vertical-align: top; word-wrap: break-word;">
                            <h2 class="callout-title" style="Margin: 0; Margin-bottom: 0; color: #4D5663; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 500; line-height: 1.53846; margin: 0; margin-bottom: 0; padding: 0; text-align: left; word-wrap: normal;">Other files recorded in this request</h2>
                          </td>
                        </tr>
                      </table>

                      <table class="callout" style="Margin-bottom: 16px; border-collapse: collapse; border-spacing: 0; margin-bottom: 16px; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th class="callout-inner" style="Margin: 0; background: #FFF; border: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 20px; text-align: left; width: 100%;">
                        <div class="request-attachments">
                          {foreach from=$leaveFiles item=value key=label}
                            <div class="request-attachment" style="Margin-bottom: 10px; margin-bottom: 10px;">
                              <span class="request-attachment-name" style="color: #4D5663; font-weight: 600;">{$value.name}</span>: Added on {$value.upload_date|crmDate}
                            </div>
                          {/foreach}
                        </div>
                      </th><th class="expander" style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th></tr></table>
                    {/if}
                  </th>
<th class="expander" style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th></tr></table></th>
                </tr></tbody></table>
                <table class="row collapse" style="border-collapse: collapse; border-spacing: 0; display: table; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;">
                  <th class="small-12 large-12 columns first last" style="Margin: 0 auto; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0; padding-right: 0; text-align: left; width: 588px;"><table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tr style="padding: 0; text-align: left; vertical-align: top;"><th style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0; text-align: left;">
                    <table class="spacer" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;"><td height="16px" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 16px; margin: 0; mso-line-height-rule: exactly; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">&#xA0;</td></tr></tbody></table>
                    <img class="text-center email-logo" src="https://civihr.org/sites/default/files/email-logo.png" style="-ms-interpolation-mode: bicubic; Margin: 0 auto; clear: both; display: block; float: none; margin: 0 auto; max-width: 100%; outline: none; text-align: center; text-decoration: none; width: 100px;">
                  </th>
<th class="expander" style="Margin: 0; color: #727E8A; font-family: \'Open Sans\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: normal; line-height: 1.53846; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th></tr></table></th>
                </tr></tbody></table>
              </div>

            </td></tr></tbody></table>
          </center>
        </td>
      </tr>
    </table>
    <!-- prevent Gmail on iOS font size manipulation -->
   <div style="display:none; white-space:nowrap; font:15px courier; line-height:0;"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </div>
  </body>
</html>

'
      'is_reserved' => 1
    ],
  ],
  [
    'name' => 'Email Template: Sickness Request',
    'entity' => 'MessageTemplate',
    'params' => [
      'version' => 3,
      'msg_title' => 'CiviHR Sickness Record Notification',
      'msg_subject' => 'Sickness Request',
      'msg_html' => '<html><head><title></title></head><body><h3>CiviHR Sickness Record</h3><h4>{$currentDateTime->format("D d F Y")}</h4><p><strong>Sickness Request Type Name</strong></p><table><tbody><tr><td>{ts}Status:{/ts}</td><td>{$leaveStatus}</td></tr><tr><td>{ts}Staff Member:{/ts}</td><td>{contact.display_name}</td></tr>{if $leaveRequest->from_date eq $leaveRequest->to_date}<tr><td>{ts}Date:{/ts}</td><td>{$fromDate|truncate:10:\'\'|crmDate}{$fromDateType}</td></tr>{else}<tr><td>{ts}From Date:{/ts}</td><td>{$fromDate|truncate:10:\'\'|crmDate}{$fromDateType}</td></tr><tr><td>{ts}To Date:{/ts}</td><td>{$toDate|truncate:10:\'\'|crmDate}{$toDateType}</td></tr>{/if}</tbody></table><p>Additional Details:</p>The Reason: <table border="0" cellpadding="1" cellspacing="1" style="width: 700px;"><tbody>{foreach from=$sicknessReasons item=value key=id}<tr><td><input type="checkbox"{if $id eq $leaveRequest->sickness_reason}checked{/if}>{$value}</td></tr>{/foreach}</tbody></table>{if $leaveRequiredDocuments}Documents: <table border="0" cellpadding="1" cellspacing="1" style="width: 700px;"><tbody>{foreach from=$sicknessRequiredDocuments item=value key=id}<tr><td><input type="checkbox"{if in_array($id, $leaveRequiredDocuments)}checked{/if}>{$value}</td></tr>{/foreach}</tbody></table>{/if}<p><a href="{$leaveRequestLink}">View This Request</a></p>{if $leaveComments}<p><strong>Request Comments</strong></p><table border="0" cellpadding="1" cellspacing="1" style="width: 700px;"><tbody>{foreach from=$leaveComments item=value key=label}<tr><td>{$value.commenter}:{$value.created_at|crmDate}</td></tr><tr><td>{$value.text}</td></tr>{/foreach}</tbody></table>{/if}<p></p>{if $leaveFiles}<p><b>Other files recorded on this request</b></p><table border="0" cellpadding="1" cellspacing="1" style="width: 700px";><tbody>{foreach from=$leaveFiles item=value key=label}<tr><td>{$value.name}: Added on{$value.upload_date|crmDate}</td></tr>{/foreach}</tbody></table>{/if}</body></html>',
      'msg_text' => 'CiviHR Sickness RecordSickness Request Type Name{ts}Status:{/ts}{$leaveStatus}{ts}Staff Member:{/ts}{contact.display_name}{if $leaveRequest->from_date eq $leaveRequest->to_date}{ts}Date:{/ts}{$fromDate|truncate:10:\'\'|crmDate}{$fromDateType}{else}{ts}From Date:{/ts}{$fromDate|truncate:10:\'\'|crmDate}{$fromDateType}{ts}To Date:{/ts}{$toDate|truncate:10:\'\'|crmDate}{$toDateType}{/if}Additional Details:The Reason{foreach from=$sicknessReasons item=value key=id}{if $id eq $leaveRequest->sickness_reason}{$value}{/if}{/foreach}{if $leaveRequiredDocuments}{foreach from=$sicknessRequiredDocuments item=value key=id}{if in_array($id, $leaveRequiredDocuments)}{$value}{/if}{/foreach}{/if}{if $leaveComments}Request Comments{foreach from=$leaveComments item=value key=label}{$value.commenter}:{$value.created_at|crmDate}{$value.text}{/foreach}{/if}{if $leaveFiles}Other files recorded on this request{foreach from=$leaveFiles item=value key=label}{$value.name}: Added on{$value.upload_date|crmDate}{/foreach}{/if}',
      'is_reserved' => 1
    ],
  ]
];
