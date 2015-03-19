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
                {$form.hrjobcontract_hour_location_standard_hours.label}<br />
                {$form.hrjobcontract_hour_location_standard_hours.html}
            </td>
            <td>
                {$form.hrjobcontract_hour_hours_type.label}<br />
                {$form.hrjobcontract_hour_hours_type.html}
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobcontract_hour_hours_amount.label}<br />
                {$form.hrjobcontract_hour_hours_amount.html}
            </td>
            <td>
                {$form.hrjobcontract_hour_hours_unit.label}<br />
                {$form.hrjobcontract_hour_hours_unit.html}
            </td>
        </tr>
        
        <tr>
            <td colspan="2"><label>{ts}Hours Amount{/ts}</label> <br />
                {$form.hrjobcontract_hour_hours_amount_low.label}
                {$form.hrjobcontract_hour_hours_amount_low.html} &nbsp;&nbsp;
                {$form.hrjobcontract_hour_hours_amount_high.label}
                {$form.hrjobcontract_hour_hours_amount_high.html} 
            </td>
        </tr>
        
        <tr>
            <td>
                {$form.hrjobcontract_hour_hours_fte.label}<br />
                {$form.hrjobcontract_hour_hours_fte.html}
            </td>
            <td>
                {$form.hrjobcontract_hour_hours_fte_num.label}<br />
                {$form.hrjobcontract_hour_hours_fte_num.html}
            </td>
        </tr>
        
        <tr>
            <td colspan="2">
                {$form.hrjobcontract_hour_fte_denom.label}<br />
                {$form.hrjobcontract_hour_fte_denom.html}
            </td>
        </tr>
        
        <tr>
            <td colspan="2"><label>{ts}Hours FTE{/ts}</label> <br />
                {$form.hrjobcontract_hour_hours_fte_low.label}
                {$form.hrjobcontract_hour_hours_fte_low.html} &nbsp;&nbsp;
                {$form.hrjobcontract_hour_hours_fte_high.label}
                {$form.hrjobcontract_hour_hours_fte_high.html} 
            </td>
        </tr>
    </table>
</div>
