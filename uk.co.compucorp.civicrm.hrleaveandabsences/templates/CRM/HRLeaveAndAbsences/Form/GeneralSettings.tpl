<div id="bootstrap-theme">
  <div class="panel panel-default crm-form-block crm-general_settings-form-block crm-leave-and-absences-form-block">
    <div class="panel-heading">
      <h1 class="panel-title">{ts}Leave and Absences General Settings{/ts}</h1>
    </div>
    <div class="panel-body">
      <div class="col-sm-8">
        {foreach from=$elementNames item=elementName}
          <div class="form-group row">
            <div class="col-sm-6">{$form.$elementName.label}</div>
            <div class="col-sm-6">{$form.$elementName.html}</div>
          </div>
        {/foreach}
      </div>
    </div>
    <div class="panel-footer clearfix">
      <div class="pull-right">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>
