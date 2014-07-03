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
{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/HRAbsence/Form/AbsenceType.tpl"}
{else}
{if $rows}
  <div id="ltype">
     <div id="help">
       <p>&nbsp;{ts}Some absence types cannot be deleted because there are existing absences of that type.{/ts}</p>
     </div>
     <div class="form-item">
        {strip}
  {* handle enable/disable actions*}
   {include file="CRM/common/enableDisableApi.tpl"}
        <table cellpadding="0" cellspacing="0" border="0">
           <thead class="sticky">
            <th>{ts}Title{/ts}</th>
            <th>{ts}Allow Credits?{/ts}</th>
            <th>{ts}Allow Debits?{/ts}</th>
            <th>{ts}Enabled?{/ts}</th>
            <th></th>
          </thead>
         {foreach from=$rows item=row}
        <tr id="HRAbsenceType-{$row.id}" class="crm-entity {cycle values="odd-row,even-row"} {$row.class}{if $row.is_active neq 1} disabled{/if}">
          <td class="crm-editable" data-field="title">{$row.title}</td>
          <td>{if $row.allow_credits eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
          <td>{if $row.allow_debits eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
          <td id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
          <td>{$row.action|replace:'xx':$row.id}</td>
        </tr>
        {/foreach}
         </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
      <div class="action-link">
      <a href="{crmURL q="action=add&reset=1"}" class="button"><span><div class="icon add-icon"></div>{ts}Add Absence Type{/ts}</span></a>
        </div>
        {/if}
    </div>
  </div>
  <script type="text/javascript">
    CRM.$('.crm-editable').crmEditable();
  </script>
{else}
    <div class="messages status no-popup">
        <div class="icon inform-icon"></div>
        {capture assign=crmURL}{crmURL q="action=add&reset=1"}{/capture}
        {ts 1=$crmURL}There are no Absence Types entered. You can <a href='%1'>add one</a>.{/ts}
    </div>
{/if}
{/if}
