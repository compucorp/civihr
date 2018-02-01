<div id="crm-contact-actions-wrapper" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Page_Inline_Actions"{rdelim}'>
  {crmButton id="crm-contact-actions-link" href="#" icon="bars"}
    {ts}Actions{/ts}
  {/crmButton}
  <div id="crm-contact-actions-list" class="crm_contact-actions">
    <div id="bootstrap-theme">
      <div class="crm_contact-actions__body">
        <div class="crm_contact-actions__primary-panel">
          {foreach from=$menu->getHighlightedPanelItems() item='group'}
            <h3>{$group->getTitle()}</h3>
            {foreach from=$group->getItems() item='item'}
              {$item->render()}
            {/foreach}
          {/foreach}
        </div>
        <div class="crm_contact-actions__secondary-panel">
          {foreach from=$menu->getMainPanelItems() item='group'}
            <div class="crm_contact-actions__secondary-panel__column">
              <h3>{$group->getTitle()}</h3>
              {foreach from=$group->getItems() item='item'}
                {$item->render()}
              {/foreach}
            </div>
          {/foreach}
        </div>
      </div>
      <div class="crm_contact-actions__footer">
        <div class="crm_contact-actions__primary-panel__footer pull-left">
          <div class="row">
            {if !empty($contactInfo.id)}
              <div class="col-md-12">
                <hr>
              </div>
              {if $userAccountDisabled}
                <div class="col-md-6">
                  <a class="btn btn-warning" 
                    href="/civicrm/contactactionsmenu/enableuseraccount?cid={$contactInfo.contact_id}">
                    Enable User Account
                  </a>
                </div>
              {else}
                <div class="col-md-6">
                  <a class="btn btn-warning" 
                    href="/civicrm/contactactionsmenu/disableuseraccount?cid={$contactInfo.contact_id}">
                    Disable User Account
                  </a>
                </div>
              {/if}
                <div class="col-md-6">
                  <a class="btn btn-danger" 
                    href="/civicrm/contactactionsmenu/deleteuseraccount?cid={$contactInfo.contact_id}">
                    Delete User Account
                  </a>
                </div>
            {/if}
          </div>
        </div>
        <div class="crm_contact-actions__secondary-panel__footer">
          <div class="crm_contact-actions__secondary-panel__footer__column">
            <hr>
            {if call_user_func(array('CRM_Core_Permission','check'), 'delete contacts')}
              <a class="btn btn-danger pull-right" 
                href="/civicrm/contact/view/delete?reset=1&delete=1&cid={$contactInfo.contact_id}">
                <span><i class="fa fa-trash"></i></span> Delete Staff Member
              </a>
            {/if}
          </div>
        </div>
      </div>
    </div>
  <div>
</div>
