<h1 class="title">{if $action eq 1}{ts}New Public Holiday{/ts}{elseif $action eq 2}{ts}Edit Public Holiday{/ts}{/if}</h1>

<div class="crm-block crm-form-block crm-public_holiday-form-block crm-leave-and-absences-form-block">
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
                    <div class="label">{$form.date.label}</div>
                    <div class="content">{$form.date.html}</div>
                    <div class="clear"></div>
                </div>
                <div class="crm-section">
                    <div class="label">{$form.is_active.label}</div>
                    <div class="content">{$form.is_active.html}</div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    {literal}
        <script type="text/javascript">
            CRM.$(function($) {
                function initDeleteButton() {
                    $('.crm-button-type-delete').on('click', function(e) {
                        e.preventDefault();
                        CRM.confirm({
                            title: ts('Delete Public Holiday'),
                            message: ts('Are you sure you want to delete this Public Holiday?'),
                            options: {
                                yes: ts('Yes'),
                                no: ts('No')
                            }
                        })
                        .on('crmConfirm:yes', deleteCallback);
                    });
                }

                function deleteCallback() {
                    {/literal}
                    window.location = "{$deleteUrl}";
                    {literal}
                }

                $(document).ready(function() {
                    initDeleteButton();
                });

            });
        </script>
    {/literal}
    {/if}
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
