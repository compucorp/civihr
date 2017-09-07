<div id="bootstrap-theme">
  <h1 class="title">
    {if $action eq 1}{ts}New Work Pattern{/ts}
    {elseif $action eq 2}{ts}Edit Work Pattern{/ts}{/if}
  </h1>
  <div class="work-pattern-form panel panel-default">
    {if $action neq 8}
      <ul class="nav nav-tabs">
        <li class="active"><a href="#work-pattern-details" data-toggle="tab">{ts}Details{/ts}</a></li>
        <li><a href="#work-pattern-calendar" data-toggle="tab">{ts}Calendar{/ts}</a></li>
      </ul>
      <div class="tab-content">
        <div id="work-pattern-details" class="tab-pane active">
          {include file="CRM/HRLeaveAndAbsences/Form/WorkPattern/Details.tpl"}
        </div>
        <div id="work-pattern-calendar" class="tab-pane">
          {include file="CRM/HRLeaveAndAbsences/Form/WorkPattern/Calendar.tpl"}
        </div>
      </div>
    {/if}
    <div class="panel-body">
      <div class="pull-right">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
  {literal}
    <script type="text/javascript">
      CRM.$(function($) {
        $('#bootstrap-theme select').select2('destroy');
      });
    </script>
  {/literal}
</div>
