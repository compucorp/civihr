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
<div id="notes-search" class="form-item">
    <table class="form-layout">
        <tr>
            <td>
                {$form.hrjobcontract_details_position.label}<br />
                {$form.hrjobcontract_details_position.html}
            </td>
            <td>
                {$form.hrjobcontract_details_title.label}<br />
                {$form.hrjobcontract_details_title.html}
            </td>
        </tr>
        
        <tr>
           <td colspan="2"><label>{ts}Period Start Date{/ts}</label></td>
        </tr>
        <tr>
           {include file="CRM/Core/DateRange.tpl" fieldName="hrjobcontract_details_period_start_date" from='_low' to='_high'}
        </tr>
        <tr>
           <td colspan="2"><label>{ts}Period End Date{/ts}</label></td>
        </tr>
        <tr>
           {include file="CRM/Core/DateRange.tpl" fieldName="hrjobcontract_details_period_end_date" from='_low' to='_high'}
        </tr>
        
        <tr>
            <td>
                {$form.hrjobcontract_role_role_level_type.label}<br />
                {$form.hrjobcontract_role_role_level_type.html}
            </td>
            <td>
                {$form.hrjobcontract_details_contract_type.label}<br />
                {$form.hrjobcontract_details_contract_type.html}
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobcontract_details_notice_amount.label}<br />
                {$form.hrjobcontract_details_notice_amount.html}
            </td>
            <td>
                {$form.hrjobcontract_details_notice_unit.label}<br />
                {$form.hrjobcontract_details_notice_unit.html}
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobcontract_details_notice_amount_employee.label}<br />
                {$form.hrjobcontract_details_notice_amount_employee.html}
            </td>
            <td>
                {$form.hrjobcontract_details_notice_unit_employee.label}<br />
                {$form.hrjobcontract_details_notice_unit_employee.html}
            </td>
        </tr>
        
        <tr>
            <td>
                {$form.hrjobcontract_details_funding_notes.label}<br />
                {$form.hrjobcontract_details_funding_notes.html}
            </td>
            <td>
                {$form.hrjobcontract_details_location.label}<br />
                {$form.hrjobcontract_details_location.html}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {$form.hrjobcontract_details_is_primary.label}&nbsp;{$form.hrjobcontract_details_is_primary.html}
            </td>
        </tr>
    </table>
</div>
