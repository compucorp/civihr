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
                {$form.hrjobcontract_pay_pay_scale.label}<br />
                {$form.hrjobcontract_pay_pay_scale.html}
            </td>
            <td>
                {$form.hrjobcontract_pay_is_paid.label}<br />
                {$form.hrjobcontract_pay_is_paid.html}
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobcontract_pay_pay_amount.label}<br />
                {$form.hrjobcontract_pay_pay_amount.html}
            </td>
            <td><label>{ts}Pay Amount range{/ts}</label> <br />
                {$form.hrjobcontract_pay_pay_amount_low.label}
                {$form.hrjobcontract_pay_pay_amount_low.html} &nbsp;&nbsp;
                {$form.hrjobcontract_pay_pay_amount_high.label}
                {$form.hrjobcontract_pay_pay_amount_high.html} 
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobcontract_pay_pay_unit.label}<br />
                {$form.hrjobcontract_pay_pay_unit.html}
            </td>
            <td>
                {$form.hrjobcontract_pay_pay_currency.label}<br />
                {$form.hrjobcontract_pay_pay_currency.html}
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobcontract_pay_pay_annualized_est.label}<br />
                {$form.hrjobcontract_pay_pay_annualized_est.html}
            </td>
            <td><label>{ts}Estimated Annual Pay range{/ts}</label> <br />
                {$form.hrjobcontract_pay_pay_annualized_est_low.label}
                {$form.hrjobcontract_pay_pay_annualized_est_low.html} &nbsp;&nbsp;
                {$form.hrjobcontract_pay_pay_annualized_est_high.label}
                {$form.hrjobcontract_pay_pay_annualized_est_high.html} 
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobcontract_pay_pay_is_auto_est.label}<br />
                {$form.hrjobcontract_pay_pay_is_auto_est.html}
            </td>
            <td>
                {$form.hrjobcontract_pay_pay_cycle.label}<br />
                {$form.hrjobcontract_pay_pay_cycle.html}
            </td>
        </tr>
        
        <tr>
            <td><label>{ts}Pay Per Cycle Gross range{/ts}</label> <br />
                {$form.hrjobcontract_pay_pay_per_cycle_gross_low.label}
                {$form.hrjobcontract_pay_pay_per_cycle_gross_low.html} &nbsp;&nbsp;
                {$form.hrjobcontract_pay_pay_per_cycle_gross_high.label}
                {$form.hrjobcontract_pay_pay_per_cycle_gross_high.html} 
            </td>
            <td><label>{ts}Pay Per Cycle Net range{/ts}</label> <br />
                {$form.hrjobcontract_pay_pay_per_cycle_net_low.label}
                {$form.hrjobcontract_pay_pay_per_cycle_net_low.html} &nbsp;&nbsp;
                {$form.hrjobcontract_pay_pay_per_cycle_net_high.label}
                {$form.hrjobcontract_pay_pay_per_cycle_net_high.html} 
            </td>
        </tr>
    </table>
</div>
