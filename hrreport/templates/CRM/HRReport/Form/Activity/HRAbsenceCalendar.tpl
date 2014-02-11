{*
 +--------------------------------------------------------------------+
 | CiviHR version 1.0                                                 |
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
{if $criteriaForm OR $instanceForm OR $instanceFormError}
  <div class="crm-block crm-form-block crm-report-field-form-block">
    {include file="CRM/Report/Form/Fields.tpl"}
  </div>
{/if}

{if $statistics}
  <table class="report-layout statistics-table">
    {foreach from=$statistics.groups item=row}
      <tr>
        <th class="statistics" scope="row">{$row.title}</th>
        <td>{$row.value}</td>
      </tr>
    {/foreach}
    {foreach from=$statistics.filters item=row}
      <tr>
        <th class="statistics" scope="row">{$row.title}</th>
        <td>{$row.value}</td>
      </tr>
    {/foreach}
  </table>
{/if}
{include file="CRM/Report/Form/Actions.tpl"}

{foreach from=$rows item=yearRecord key=year}
  {foreach from=$yearRecord item=monthRecord key=month}
<div id="report-layout statistics-table">
    <div class="hrabsence-calendar-chart">
      <table class="hrabsence-calendar-chart">
        <tr>
          <th colspan={math equation="(x - y)+2" x=$monthRecord.end_day y=$monthRecord.start_day}>
            {$monthRecord.month_name}&nbsp;&nbsp;{$year}
          </th>
        </tr>
        <tr>
          <th>Individual</th>
          {foreach from=$monthDays item=day1}
            {if ($day1 GT $monthRecord.start_day || $day1 EQ $monthRecord.start_day) AND
              ($day1 LT $monthRecord.end_day || $day1 EQ $monthRecord.end_day)}
              <th>{$day1}</th>
            {/if}
          {/foreach}
        </tr>
        {if $monthRecord.contacts}
          {foreach from=$monthRecord.contacts item=properties key=contact_id}
            <tr>
              <td style="text-align:left; color:blue;"><b>{$monthRecord.contacts.$contact_id.link}</b></td>
              {foreach from=$monthDays item=day2}
                {if ($day2 GT $monthRecord.start_day || $day2 EQ $monthRecord.start_day)
                  AND ($day2 LT $monthRecord.end_day || $day2 EQ  $monthRecord.end_day)}
                  {if isset($properties.$day2)}
                    {assign var=activity_type_id value=$properties.$day2.activity_type_id}
                      <td class='{$legend.$activity_type_id.class} hrabsence-cal-chart-item'>{$properties.$day2.day_name}</td>
                    {else}
                      <td></td>
                  {/if} 
                {/if}
              {/foreach}
            </tr>
          {/foreach}
        {/if}
      </table>
    </div>
</div>
  {/foreach}  
{/foreach}

<div class="hrabsence-calendar-chart">
  <table class="hrabsence-legend" style="text-align:center;">
    <tr>
      <th colspan={$legend|@count}>
        <h4>{ts}Legend{/ts}</h4>
      </th>
    <tr>
      {foreach from=$legend item="value"}
        <td class={$value.class} width={$legendWidthPercent}>
          <b>{$value.title}<b>
        </td>
      {/foreach}
    </tr>
  </table>
</div>
