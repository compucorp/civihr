<div id="bootstrap-theme">
  <h1 class="title">{ts}Leave and Absences General Settings{/ts}</h1>
  <div class="panel panel-default crm-block crm-form-block crm-general_settings-form-block crm-leave-and-absences-form-block">
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

    <div class="panel-body">
      <div class="pull-right">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>
