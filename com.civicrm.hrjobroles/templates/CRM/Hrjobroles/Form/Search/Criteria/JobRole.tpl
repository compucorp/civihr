<div id="notes-search" class="form-item">
    <table class="form-layout">
        <tr>
            <td>
                {$form.hrjobroles_title.label}<br />
                {$form.hrjobroles_title.html}
            </td>
            <td>
                {$form.hrjobroles_description.label}<br />
                {$form.hrjobroles_description.html}
            </td>
        </tr>
        
        <tr>
           <td colspan="2"><label>{ts}Start Date{/ts}</label></td>
        </tr>
        <tr>
           {include file="CRM/Core/DateRange.tpl" fieldName="hrjobroles_start_date" from='_low' to='_high'}
        </tr>
        <tr>
           <td colspan="2"><label>{ts}End Date{/ts}</label></td>
        </tr>
        <tr>
           {include file="CRM/Core/DateRange.tpl" fieldName="hrjobroles_end_date" from='_low' to='_high'}
        </tr>
        <tr>
            <td>
                {$form.hrjobroles_location.label}<br />
                {$form.hrjobroles_location.html}
            </td>
            <td>
                {$form.hrjobroles_region.label}<br />
                {$form.hrjobroles_region.html}
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobroles_department.label}<br />
                {$form.hrjobroles_department.html}
            </td>
            <td>
                {$form.hrjobroles_level_type.label}<br />
                {$form.hrjobroles_level_type.html}
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobroles_funder.label}<br />
                {$form.hrjobroles_funder.html}
            </td>
            <td>
                {$form.hrjobroles_funder_val_type.label}<br />
                {$form.hrjobroles_funder_val_type.html}
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobroles_percent_pay_funder.label}<br />
                {$form.hrjobroles_percent_pay_funder.html}
            </td>
            <td>
                {$form.hrjobroles_amount_pay_funder.label}<br />
                {$form.hrjobroles_amount_pay_funder.html}
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobroles_cost_center.label}<br />
                {$form.hrjobroles_cost_center.html}
            </td>
            <td>
                {$form.hrjobroles_cost_center_val_type.label}<br />
                {$form.hrjobroles_cost_center_val_type.html}
            </td>
        </tr>
        <tr>
            <td>
                {$form.hrjobroles_percent_pay_cost_center.label}<br />
                {$form.hrjobroles_percent_pay_cost_center.html}
            </td>
            <td>
                {$form.hrjobroles_amount_pay_cost_center.label}<br />
                {$form.hrjobroles_amount_pay_cost_center.html}
            </td>
        </tr>
    </table>
</div>
