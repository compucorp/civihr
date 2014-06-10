{*
 +--------------------------------------------------------------------+
 | CiviHR version 1.3                                                 |
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
<div class="crm-block crm-form-block">
  <table class="form-layout-compressed">
    {if !$isTemplate}
      <tr>
        <td class="label">{$form.template_id.label}</td>
        <td>{$form.template_id.html}</td>
      </tr>
    {/if}
    <tr>
      <td class="label">{$form.position.label}</td>
      <td>{$form.position.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.location.label}</td>
      <td>{$form.location.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.salary.label}</td>
      <td>{$form.salary.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.description.label}</td>
      <td>{$form.description.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.benefits.label}</td>
      <td>{$form.benefits.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.requirements.label}</td>
      <td>{$form.requirements.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.start_date.label}</td>
      <td>{include file="CRM/common/jcalendar.tpl" elementName=start_date}</td>
    </tr>
    <tr>
      <td class="label">{$form.end_date.label}</td>
      <td>{include file="CRM/common/jcalendar.tpl" elementName=end_date}</td>
    </tr>
    <tr>
      <td class="label">{$form.status_id.label}</td>
      <td>{$form.status_id.html}</td>
    </tr>
  </table>
  {*Stages Block*}
  {*if !$isTemplate*}
    <fieldset>
      <legend class="collapsible-title">{ts}Stages{/ts}</legend>
      <table class="form-layout-compressed">
        <tr>
          <td>{$form.stages.html}</td>
        </tr>
      </table>
    </fieldset>
  {*/if*}
  {*Application Block*}
  <fieldset>
    <legend class="collapsible-title">{ts}Application Form{/ts}</legend>
    <table class="form-layout-compressed">
      <tr>
        <td>{$form.application_profile.html}</td>
      </tr>
    </table>
  </fieldset>
  {*Evaluation Block*}
  <fieldset>
    <legend class="collapsible-title">{ts}Evaluation Form{/ts}</legend>
    <table class="form-layout-compressed">
      <tr>
        <td>{$form.evaluation_profile.html}</td>
      </tr>
    </table>
  </fieldset>
  {*Permission block*}
  <fieldset>
    <legend class="collapsible-title">{ts}Permissions{/ts}</legend>
    <table class="form-layout-compressed">
      <tr class="columnheader">
        <td scope="column">{ts}Person{/ts}</td>
        <td scope="column">{ts}Permission{/ts}</td>
      </tr>
      {section name='i' start=1 loop=$rowCount}
        {assign var='rowNumber' value=$smarty.section.i.index}
        <tr id="permission-{$rowNumber}"
            class="permission-block {if $rowNumber GT $showPermissionRow}hiddenElement{/if}">
          <td>{$form.permission_contact_id.$rowNumber.html}</td>
          <td>{$form.permission.$rowNumber.html}
            &nbsp;<a class="crm-hover-button permission-delete-link" href="#"><span class="icon delete-icon"></span></a>
          </td>
          <td>{help id="id-set_permissions"}</td>
        </tr>
      {/section}
      <tr>
        <td>
          <a href="#" class="crm-hover-button" id="addMorePermission"><span
              class="icon add-icon"></span> {ts}add another permission{/ts}</a>
        </td>
      </tr>
    </table>
  </fieldset>
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl"}
  </div>
</div>
{literal}
  <script type="text/javascript">
    CRM.$(function ($) {
      $('#template_id', '#HRVacancy').change(function () {
        $('#crm-main-content-wrapper')
          .crmSnippet({url: CRM.url('civicrm/vacancy/add', {action: 'add', reset: 1, template_id: $(this).val()})})
          .crmSnippet('refresh');
      })

      $('#addMorePermission').on('click', function () {
        if ($('tr.permission-block').hasClass("hiddenElement")) {
          $('tr.hiddenElement').filter(':first').show().removeClass('hiddenElement');
      }
      if ($('tr.hiddenElement').length < 1) {
        $('#addMorePermission').hide();
      }
      return false;
    });

    $('.permission-delete-link').click(function(){
      $(this).closest('tr').find('input').val('');
      $(this).closest('tr').addClass('hiddenElement').removeAttr('style');
      $('#addMorePermission').show();
      return false;
    });
  });
</script>
{/literal}

