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
{include file="CRM/HRRecruitment/Form/Search.tpl"}
{if $rows}
<table>
  <tr>
    <th>{ts}Job Position{/ts}</th>
    <th>{ts}Location{/ts}</th>
    <th>{ts}Salary{/ts}</th>
    <th>{ts}Application Dates{/ts}</th>
    <th>{ts}Status{/ts}</th>
    <th>{ts}{/ts}</th>
  </tr>
{foreach from=$rows item=row}
  <tr class="{cycle values="odd-row,even-row"} {$row.class}">
    <td id="{$row.id}">{$row.position}</td>
    <td>{$row.location}</td>
    <td>{$row.salary}</td>
    <td>{$row.startdate|crmDate:"%e %b %Y"}{ts} - {/ts}{$row.enddate|crmDate:"%e %b %Y"}</td>
    <td>{$row.status_label}</td>
    <td>{$row.action|replace:'xx':$row.id}</td>
  </tr>
{/foreach}
</table>
{else}
  <div class="status messages">
    {ts}No Such Vacancy List Found{/ts}
  </div>
  {include file="CRM/HRRecruitment/Form/Search/EmptyResults.tpl"}
{/if}

