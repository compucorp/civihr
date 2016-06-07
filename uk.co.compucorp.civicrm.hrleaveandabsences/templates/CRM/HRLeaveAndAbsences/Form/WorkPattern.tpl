<h1 class="title">{if $action eq 1}{ts}New Work Pattern{/ts}{elseif $action eq 2}{ts}Edit Work Pattern{/ts}{/if}</h1>

<div class="crm-block crm-form-block crm-work-pattern-form-block crm-leave-and-absences-form-block">
    {if $action neq 8}
        <div id="mainTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
            <ul class="crm-extensions-tabs-list">
                <li id="tab_details" class="crm-tab-button">
                    <a href="#work-pattern-details" title="{ts}Details{/ts}">{ts}Details{/ts}</a>
                </li>
                <li id="tab_calendar" class="crm-tab-button">
                    <a href="#work-pattern-calendar" title="{ts}Calendar{/ts}">{ts}Calendar{/ts}</a>
                </li>
            </ul>

            <div id="work-pattern-details" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
                {include file="CRM/HRLeaveAndAbsences/Form/WorkPattern/Details.tpl"}
            </div>
            <div id="work-pattern-calendar" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
                {include file="CRM/HRLeaveAndAbsences/Form/WorkPattern/Calendar.tpl"}
            </div>

            <div class="clear"></div>
        </div>
        {literal}
        <script type="text/javascript">
            var selectedTab  = 'details';
            CRM.$(function($) {
                var tabIndex = $('#tab_' + selectedTab).prevAll().length;
                $("#mainTabContainer").tabs({active: tabIndex});
                $(".crm-tab-button").addClass("ui-corner-bottom");
            });
            {/literal}
        </script>
    {/if}
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
