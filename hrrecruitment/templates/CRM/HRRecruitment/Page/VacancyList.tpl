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
{if $list}
  <div class="view-hrvacancies">
    {if $rows}
      {strip}
        <div class="description">
          {ts}Click on the job position for more information.{/ts}
        </div>
        <table class="selector">
          <tr class="columnheader">
            <th>{ts}Job Position{/ts}</th>
            <th>{ts}Location{/ts}</th>
            <th>{ts}Salary{/ts}</th>
            <th>{ts}Application Dates{/ts}</th>
            <th></th>
          </tr>
          {foreach from=$rows item=row}
            <tr id='rowid{$row.id}' class=" crm-hrvacancy-id_{$row.id} {cycle values="odd-row,even-row"}">
              <td class="crm-job_position"><a class="hr-job-position-link" href="{crmURL p="civicrm/vacancy" q="reset=1&id=`$row.id`"}">{$row.position}</a></td>
              <td class="crm-location">{$row.location}</td>
              <td class="crm-salary">{$row.salary}</td>
              <td class="crm-application_dates">{$row.startDate} - {$row.endDate}</td>
              <td><a href="{crmURL p="civicrm/vacancy/apply" q="reset=1&id=`$row.id`"}">{ts}Apply Now{/ts}</a></td>
            </tr>
          {/foreach}
        </table>
      {/strip}
    {else}
      <div class="messages status no-popup">
        <div class="icon inform-icon"></div>&nbsp;
          {ts}There are no Job Vacanies.{/ts}
        </div>
    {/if}
  </div>
  {literal}
  <script type="text/javascript">
    cj(function($) {
      $('.hr-job-position-link').live('click', function(e) {
        var url = $(this).attr('href');
        CRM.loadPage(url);
        e.preventDefault();
        $('.hr-job-info-close').live('click', function(e) {
          e.preventDefault();
          $(this).closest('div[role=dialog]').remove();
        });
      });
    });
  </script>
  {/literal}
{/if}
{if $info}
  <div class="view-hrvacancy-info">
    {if $rows}
      {strip}
        <h3>{$rows.position}</h3>
         <table class="vacancy-popup">
           <tr><td>Salary:</td><td>{$rows.salary}</td></tr>
           <tr><td>location:</td><td>{$rows.location}</td></tr>
           <tr><td>description:</td><td>{$rows.description}</td></tr>
           <tr><td>benefits:</td><td>{$rows.benefits}</td></tr>
           <tr><td>requirements:</td><td>{$rows.requirements}</td></tr>
         </table>
         <a href="{crmURL p="civicrm/vacancy/apply" q="reset=1&id=`$rows.id`"}" class="button"><span>{ts}Apply Now{/ts}</a>
         <a href="" class="button hr-job-info-close"><span>{ts}Close{/ts}</a>
       {/strip}
     {/if}
  </div>
{/if}
