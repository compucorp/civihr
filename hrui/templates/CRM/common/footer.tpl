{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
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
*}
{if call_user_func(array('CRM_Core_Permission','check'), 'access CiviCRM')}
  {include file="CRM/common/accesskeys.tpl"}
  {if !empty($contactId)}
    {include file="CRM/common/contactFooter.tpl"}
  {/if}

  <div class="crm-footer" id="civicrm-footer">
    {* PCHR-1323 - Display CiviHR version info. *}
    {ts}Powered by CiviHR version{/ts}
    {civihrVersion assign=civihrversion}.

    {if !empty($footer_status_severity)}
      <span class="status{if $footer_status_severity gt 3} crm-error{elseif $footer_status_severity gt 2} crm-warning{else} crm-ok{/if}">
      <a href="{crmURL p='civicrm/a/#/status'}">{$footer_status_message}</a>.
    </span>&nbsp;
    {/if}
    CiviHR is openly available under the <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU AGPL License</a> and can be downloaded from the
    <a target="_blank" href="https://civihr.org">Project website</a>&nbsp;.
    <div class="text-center">
      <div class="footer-logo">
        <i class="icon-logo-full"></i>
      </div>
    </div>
  </div>
  {include file="CRM/common/notifications.tpl"}
{/if}
