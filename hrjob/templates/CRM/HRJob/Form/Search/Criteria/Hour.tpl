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
                {$form.hrjob_hours_type.label}<br />
                {$form.hrjob_hours_type.html}
            </td>
            <td>
                {$form.hrjob_hours_unit.label}<br />
                {$form.hrjob_hours_unit.html}
            </td>
        </tr>
        <tr>
            <td><label>{ts}Hours Amount{/ts}</label> <br />
                {$form.hrjob_hours_amount_low.label}
                {$form.hrjob_hours_amount_low.html} &nbsp;&nbsp;
                {$form.hrjob_hours_amount_high.label}
                {$form.hrjob_hours_amount_high.html} 
            </td>
            <td><label>{ts}Hours FTE{/ts}</label> <br />
                {$form.hrjob_hours_fte_low.label}
                {$form.hrjob_hours_fte_low.html} &nbsp;&nbsp;
                {$form.hrjob_hours_fte_high.label}
                {$form.hrjob_hours_fte_high.html} 
            </td>
        </tr>
    </table>
</div>
