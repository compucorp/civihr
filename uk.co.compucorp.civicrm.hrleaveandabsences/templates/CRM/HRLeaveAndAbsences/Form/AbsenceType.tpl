<h1 class="title">{if $action eq 1}{ts}New Leave/Absence Type{/ts}{elseif $action eq 2}{ts}Edit Leave/Absence Type{/ts}{/if}</h1>

<div class="crm-block crm-form-block crm-absence_type-form-block">
    {if $action neq 8}
        <div class="row">
            <div class="col-sm-6">
                <h3>{ts}Basic Details{/ts}</h3>
                <div class="crm-section">
                    <div class="label">{$form.title.label}</div>
                    <div class="content">{$form.title.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.color.label}</div>
                    <div class="content">{$form.color.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.is_default.label}</div>
                    <div class="content">{$form.is_default.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.is_reserved.label}</div>
                    <div class="content">{$form.is_reserved.html}</div>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <h3>&nbsp;</h3>
                <div class="crm-section">
                    <div class="label">{$form.default_entitlement.label}</div>
                    <div class="content">{$form.default_entitlement.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.add_public_holiday_to_entitlement.label}</div>
                    <div class="content">{$form.add_public_holiday_to_entitlement.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.is_active.label}</div>
                    <div class="content">{$form.is_active.html}</div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <h3>{ts}Requesting Leave/Absence{/ts}</h3>
                <div class="crm-section">
                    <div class="label">{$form.max_consecutive_leave_days.label}</div>
                    <div class="content">{$form.max_consecutive_leave_days.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.allow_request_cancelation.label}</div>
                    <div class="content">{$form.allow_request_cancelation.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.allow_overuse.label}</div>
                    <div class="content">{$form.allow_overuse.html}</div>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <h3>{ts}TOIL{/ts}</h3>
                <div class="crm-section">
                    <div class="label">{$form.allow_accruals_request.label}</div>
                    <div class="content">{$form.allow_accruals_request.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.max_leave_accrual.label}</div>
                    <div class="content">{$form.max_leave_accrual.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.allow_accrue_in_the_past.label}</div>
                    <div class="content">{$form.allow_accrue_in_the_past.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{ts}Default expiry of accrued amounts{/ts}</div>
                    <div class="content">
                        <label><input type="checkbox"> {ts}Never expire{/ts}</label>
                        <br/>
                        {$form.accrual_expiration_duration.html}{$form.accrual_expiration_unit.html}
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <h3>{ts}Public Holidays{/ts}</h3>
                <div class="crm-section">
                    <div class="label">{$form.must_take_public_holiday_as_leave.label}</div>
                    <div class="content">{$form.must_take_public_holiday_as_leave.html}</div>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <h3>{ts}Carry Forward{/ts}</h3>
                <div class="crm-section">
                    <div class="label">{$form.allow_carry_forward.label}</div>
                    <div class="content">{$form.allow_carry_forward.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.max_number_of_days_to_carry_forward.label}</div>
                    <div class="content">{$form.max_number_of_days_to_carry_forward.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{ts}Carry forward leave expiry{/ts}</div>
                    <div class="content">
                        <label><input type="radio" name="carry_forward_expiration"> {ts}Never expire{/ts}</label>
                        <br/>
                        <label><input type="radio" name="carry_forward_expiration"> {ts}Expire after a certain duration{/ts}</label>
                        <br/>
                        {$form.carry_forward_expiration_duration.html}{$form.carry_forward_expiration_unit.html}
                        <br/>
                        <label><input type="radio" name="carry_forward_expiration"> {ts}Expire on a particular date{/ts}</label>
                        <br/>
                        {$form.carry_forward_expiration_date_day.html}{$form.carry_forward_expiration_date_month.html}
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    {/if}
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
