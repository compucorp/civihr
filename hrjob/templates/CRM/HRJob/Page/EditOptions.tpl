{if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
  <a class="crm-option-edit-link crm-hover-button" href="{crmURL p="civicrm/admin/options/$group" q='reset=1'}" data-option-edit-path="civicrm/admin/options/{$group}" target="_blank" title="{ts}Edit Options{/ts}"><span class="icon edit-icon"></span></a>
{/if}
