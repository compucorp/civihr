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
<h3>{ts}Edit Hour Type Option{/ts}</h3>
  <div class="crm-block crm-form-block crm-edit-hour-option-form-block">
    <table class="form-layout" style="height:100px; width:50%;">
      <tr>
        <thead class="sticky">
          <th>Label</th>
          <th>Value</th>
        </thead>
      </tr>
      {foreach from=$optionGroupIds item=row}
      <tr id="HRJob-EditOption-{$row.id}" class="crm-entity {cycle values="odd-row,even-row"}">
        <td >{$row.label}</td>
        <td >{$row.value}</td>
      </tr>
      {/foreach}
    </table>
    </br><hr></br>
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div></br>
    <table class="form-layout">
      <tr>
        <td class="label">{$form.hour_type_select.label}</td>
        <td class="html-adjust">{$form.hour_type_select.html}</td>
      </tr>
      <tr>
        <td class="label">{$form.hour_value.label}</td>
        <td class="html-adjust">{$form.hour_value.html}</td>
      </tr>
    </table></br>
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
  </div>

{literal}
<script type="text/javascript" >
  CRM.$(function($) {
  $('#hour_type_select').change(function() {
    $('#hour_value').val($(this).val());
    });
  });
</script>
{/literal}
