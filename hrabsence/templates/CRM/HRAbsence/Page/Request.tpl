{*
 +--------------------------------------------------------------------+
 | CiviHR version 1.2                                                |
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
{* Search form and results for Event Participants *}
<div class="crm-block crm-content-block">
{assign var='loggedinuserid' value=$loginuserid}
  <table class="absencedetail" style="width: auto; border: medium none ! important;">
    <tr>
      <td>Employee: </td> 
      <td colspan="2">{$emp_name}</td>
    </tr>
    <tr>
      <td>Position: </td> 
      <td colspan="2">{$emp_position}</td>
    </tr>
    <tr>
      <td>Absence Type: </td> 
      <td colspan="2">{$absenceType}</td>
    </tr>
      <tr class="crm-event-manage-eventinfo-form-block-start_date">
        <td class="label">Dates: </td>
        <td>{$fromDate} - {$toDate}</td>
      </tr>
  </table>

{* <table id="tblabsence" class="report"><tbody><tr><td>Date</td><td>Absence</td></tr></tbody></table> *}

  <table id="tblabsence" >
    <tbody>
      <tr>
        <td>Date</td>
	<td>Absence</td>
      </tr>
      {foreach from=$absenceDateDuration item=val key=key}
      <tr class="{cycle values="odd-row,even-row"} {$row.class}" >
        <td>{$key}</td>
	<td>{$val}</td>
      </tr>
      {/foreach}
      <tr>
	<td>Total</td>
	<td>{$totalDays} days</td>
      </tr>
    </tbody>
  </table>
</div>


{include file="CRM/Custom/Page/CustomDataView.tpl"} 

<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
