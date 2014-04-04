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
<div class="crm-clearfix hr-pipeline-tab">
  <div class="hr-pipeline-case-contacts">
    <table class="row-highlight">
      <thead>
        <tr>
          <th>{ts}Applicant{/ts}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$contacts item="contact"}
          <tr id="case-{$contact.case_id}" class="crm-entity {cycle values="odd-row,even-row"}">
            <td><a class="hr-pipeline-case-link" href="{crmURL p='civicrm/case/hrapplicantprofile' q="reset=1&case_id=`$contact.case_id`&cid=`$contact.contact_id`&status_id=`$statusID`"}">{$contact.sort_name}</a></td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  </div>
  <div class="hr-pipeline-case-details">
  </div>
</div>
