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
{* Search form and results for Vacancy *}
<div class="crm-form-block crm-search-form-block">
  <div class="crm-accordion-wrapper crm-advanced_search_form-accordion {if $rows}collapsed{/if}">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Edit Search Criteria{/ts}
    </div>
    <!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
      <div id="searchForm" class="form-item">
        {strip}
          <table class="form-layout">
            <tr>
              <td class="font-size12pt" colspan="3">
                {$form.position.label}&nbsp;&nbsp;{$form.position.html|crmAddClass:'twenty'}
              </td>
            </tr>
            <tr>
              <td width="25%">
                <label>{ts}Location{/ts}</label><br/>
                <div class="listing-box">
                  {foreach from=$form.location item="location_val"}
                    <div class="{cycle values="odd-row,even-row"}">
                      {$location_val.html}
                    </div>
                  {/foreach}
                </div>
                <br/>
              </td>
              <td colspan="2" width="25%">
                <label>{ts}Status{/ts}</label><br/>
                <div class="listing-box">
                  {foreach from=$form.status_id item="status_val"}
                    <div class="{cycle values="odd-row,even-row"}">
                      {$status_val.html}
                    </div>
                  {/foreach}
                </div>
                <br/>
              </td>
            </tr>
            <tr>
              <td colspan="3">
                {include file="CRM/common/formButtons.tpl"}
              </td>
            </tr>
          </table>
        {/strip}
      </div>
    </div>
  </div>
</div>

{literal}
  <script type="text/javascript">
    cj(function () {
      cj().crmAccordions();
      var roleId = cj('input[name=activity_role]:checked', '#Search').val();
      if (roleId) {
        cj('.description .option-' + roleId).show();
      }
    });
    cj('[name=activity_role]:input').change(function () {
      cj('.description .contact-name-option').hide();
      if (cj(this).is(':checked')) {
        cj('.description .option-' + cj(this).val()).show();
      }
    }).change();
  </script>
{/literal}
