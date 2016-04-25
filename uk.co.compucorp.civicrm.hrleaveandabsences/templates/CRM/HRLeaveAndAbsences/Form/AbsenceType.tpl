<h1 class="title">{if $action eq 1}{ts}New Leave/Absence Type{/ts}{elseif $action eq 2}{ts}Edit Leave/Absence Type{/ts}{/if}</h1>

<div class="crm-block crm-form-block crm-absence_type-form-block">
    {if $action neq 8}
        <table class="form-layout">
            <tr>
                <td class="label">{$form.title.label}</td>
                <td class="html-adjust">{$form.title.html}</td>
            </tr>
            <tr>
                <td class="label">{$form.color.label}</td>
                <td class="html-adjust">{$form.color.html}</td>
            </tr>
            <tr>
                <td class="label">{$form.default_entitlement.label}</td>
                <td class="html-adjust">{$form.default_entitlement.html}</td>
            </tr>
            <tr>
                <td class="label">{$form.allow_request_cancelation.label}</td>
                <td class="html-adjust">{$form.allow_request_cancelation.html}</td>
            </tr>
        </table>
    {/if}
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
