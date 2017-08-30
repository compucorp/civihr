<div id="bootstrap-theme">
  <h1 class="title">
    {if $action eq 1}{ts}New Absence Period{/ts}
    {elseif $action eq 2}{ts}Edit Absence Period{/ts}{/if}
  </h1>
  <div class="panel panel-default crm-absence_type-form-block crm-leave-and-absences-form-block">
    <div class="panel-body crm-form-block">
      <div class="form-group row">
        <div class="col-sm-3">{$form.title.label}</div>
        <div class="col-sm-9">{$form.title.html}</div>
      </div>
      <div class="form-group row">
        <div class="col-sm-3">{$form.start_date.label}</div>
        <div class="col-sm-9">{$form.start_date.html}</div>
      </div>
      <div class="form-group row">
        <div class="col-sm-3">{$form.end_date.label}</div>
        <div class="col-sm-9">{$form.end_date.html}</div>
      </div>
      <div class="form-group row">
        <div class="col-sm-3">{$form.weight.label}</div>
        <div class="col-sm-9">{$form.weight.html}</div>
      </div>
    </div>
    {literal}
      <script type="text/javascript">
        CRM.$(function () {
          var form = new CRM.HRLeaveAndAbsencesApp.Form.AbsencePeriod();
        });
      </script>
    {/literal}
    <div class="panel-body">
      <div class="pull-right">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>
