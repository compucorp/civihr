     <div class="custom-group custom-group-{$cd_edit.name} crm-accordion-wrapper {if $cd_edit.collapse_display and !$skipTitle}collapsed{/if}">
      {if !$skipTitle}
      <div class="crm-accordion-header">
        {$cd_edit.title}
       </div><!-- /.crm-accordion-header -->
      {/if}
      <div class="crm-accordion-body">
        {if $cd_edit.help_pre}
          <div class="messages help">{$cd_edit.help_pre}</div>
        {/if}
        <table class="form-layout-compressed">
          {foreach from=$cd_edit.fields item=element key=field_id}
            {include file="CRM/Custom/Form/JobContractSummaryCustomField.tpl"}
          {/foreach}
        </table>
        <div class="spacer"></div>
        {if $cd_edit.help_post}
          <div class="messages help">{$cd_edit.help_post}</div>
        {/if}
      </div>
     </div>
