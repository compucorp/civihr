{*
 +--------------------------------------------------------------------+
 | CiviHR version 1.4                                                 |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
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
{* this template is used for adding/editing/deleting pay scale  *}
<h3>{if $action eq 1}{ts}New Pay Scale{/ts}{elseif $action eq 2}{ts}Edit Pay Scale{/ts}{else}{ts}Delete Pay Scale{/ts}{/if}</h3>
<div class="crm-block crm-form-block crm-pay_scale-form-block">
   {if $action neq 8}
     <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
     <table class="form-layout">
      <tr>
        <td class="label">{$form.pay_scale.label}</td>
        <td class="html-adjust">{$form.pay_scale.html}</td>
      </tr>
      <tr>
        <td class="label">{$form.pay_grade.label}</td>
        <td class="html-adjust">{$form.pay_grade.html}</td>
      </tr>
      <tr>
        <td class="label">{$form.currency.label}</td>
        <td class="html-adjust">{$form.currency.html}</td>
      </tr>
      <tr>
        <td class="label">{$form.amount.label}</td>
        <td class="html-adjust">{$form.amount.html}</td>
      </tr>
      <tr>
        <td class="label">{$form.periodicity.label}</td>
        <td class="html-adjust">{$form.periodicity.html}</td>
      </tr>
      <tr>
        <td class="label">{$form.is_active.label}</td>
        <td class="html-adjust">{$form.is_active.html}</td>
      </tr>
     </table>
   {/if}
   <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>

