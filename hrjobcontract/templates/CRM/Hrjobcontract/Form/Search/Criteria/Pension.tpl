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
            <td colspan="2">
                {$form.hrjobcontract_pension_is_enrolled.label}
                {$form.hrjobcontract_pension_is_enrolled.html}
            </td>
        </tr>
        <tr>
            <td><label>{ts}Employee Contribution Percentage range{/ts}</label> <br />
                {$form.hrjobcontract_pension_ee_contrib_pct_low.label}
                {$form.hrjobcontract_pension_ee_contrib_pct_low.html} &nbsp;&nbsp;
                {$form.hrjobcontract_pension_ee_contrib_pct_high.label}
                {$form.hrjobcontract_pension_ee_contrib_pct_high.html} 
            </td>
            <td><label>{ts}Employer Contribution Percentage range{/ts}</label> <br />
                {$form.hrjobcontract_pension_er_contrib_pct_low.label}
                {$form.hrjobcontract_pension_er_contrib_pct_low.html} &nbsp;&nbsp;
                {$form.hrjobcontract_pension_er_contrib_pct_high.label}
                {$form.hrjobcontract_pension_er_contrib_pct_high.html} 
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobcontract_pension_pension_type.label}
                {$form.hrjobcontract_pension_pension_type.html}
            </td>
            <td><label>{ts}Employee Contribution Absolute Amount range{/ts}</label> <br />
                {$form.hrjobcontract_pension_ee_contrib_abs_low.label}
                {$form.hrjobcontract_pension_ee_contrib_abs_low.html} &nbsp;&nbsp;
                {$form.hrjobcontract_pension_ee_contrib_abs_high.label}
                {$form.hrjobcontract_pension_ee_contrib_abs_high.html} 
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {$form.hrjobcontract_pension_ee_evidence_note.label}
                {$form.hrjobcontract_pension_ee_evidence_note.html}
            </td>
        </tr>
    </table>
</div>
