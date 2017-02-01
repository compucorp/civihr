<h1 class="title">{ts}Leave and Absences General Settings{/ts}</h1>

<div class="crm-block crm-form-block crm-general_settings-form-block crm-leave-and-absences-form-block">
      <div class="row">
          <div class="col-sm-6">
              {foreach from=$elementNames item=elementName}
                  <div class="crm-section">
                      <div class="label">{$form.$elementName.label}</div>
                      <div class="content">{$form.$elementName.html}</div>
                      <div class="clear"></div>
                  </div>
              {/foreach}
            </div>
      </div>

    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
