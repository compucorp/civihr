{*
 +--------------------------------------------------------------------+
 | CiviHR version 1.3                                                 |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
        <tr>
            <td class="label">{$form.template_id.label}</td>
            <td>{$form.template_id.html}</td>
        </tr>
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
    <fieldset class="crm-collapsible">
        <legend class="collapsible-title">{ts}Stages{/ts}</legend>
    </fieldset>
    {*Application Block*}
    <fieldset class="crm-collapsible">
        <legend class="collapsible-title">{ts}Application Form{/ts}</legend>
        <table class="form-layout-compressed">
            <tr>
                <td></td>
                <td>{$form.application_profile.html}</td>
            </tr>
        </table>
    </fieldset>
    {*Evaluation Block*}
    <fieldset class="crm-collapsible">
        <legend class="collapsible-title">{ts}Evaluation Form{/ts}</legend>
        <table class="form-layout-compressed">
            <tr>
                <td></td>
                <td>{$form.evaluation_profile.html}</td>
            </tr>
        </table>
    </fieldset>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl"}
    </div>
</div>
{literal}
<script type="text/javascript">
  cj(function($) {
    $('#template_id', '#HRVacancy').change(function() {
      $('#crm-main-content-wrapper')
        .crmSnippet({url: CRM.url('civicrm/vacancy/add', {action: 'add', reset: 1, template_id: $(this).val()})})
        .crmSnippet('refresh');
    })
  });
</script>
{/literal}

