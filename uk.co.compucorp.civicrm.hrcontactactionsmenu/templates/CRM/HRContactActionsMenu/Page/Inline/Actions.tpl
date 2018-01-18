<div id="crm-contact-actions-wrapper" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Page_Inline_Actions"{rdelim}'>
  {crmButton id="crm-contact-actions-link" href="#" icon="bars"}
  {ts}Actions{/ts}
  {/crmButton}
  <div id="crm-contact-actions-list">
    <div id="bootstrap-theme">
      <div class="row">
        <div class="col-md-4 crm_contact_action_menu__user_info">
            {foreach from=$menu->getHighlightedPanelItems() item='group'}
            <div class="row">
              <h3>{$group->getTitle()}</h3>
              {foreach from=$group->getItems() item='item'}
                {$item->render()}
              {/foreach}
            </div>
            {/foreach}
          {if !empty($contactInfo.id)}
            <div class="row crm_contact_action_menu__control_buttons_container">
              <hr>
              {if $userAccountDisabled}
                <div>
                  <span>ACCOUNT DISABLED</span>
                </div>
                <div class="col-md-6">
                  <a href="/civicrm/contactactionsmenu/enableuseraccount?cid={$contactInfo.contact_id}">
                    <button class="button__delete btn-warning">Enable User Account</button>
                  </a>
                </div>
              {else}
                <div class="col-md-6">
                  <a href="/civicrm/contactactionsmenu/disableuseraccount?cid={$contactInfo.contact_id}">
                    <button class="button__delete btn-warning">Disable User Account</button>
                  </a>
                </div>
              {/if}
              <div class="col-md-6">
                <a href="/civicrm/contactactionsmenu/deleteuseraccount?cid={$contactInfo.contact_id}">
                  <button class="button__disable btn-danger">Delete User Account</button>
                </a>
              </div>
            </div>
          {/if}
        </div>
        <div class="col-md-8 crm_contact_action_menu__body">
          <div class="row">
            {foreach from=$menu->getMainPanelItems() item='group'}
              <div class="col-md-4">
                <h3>{$group->getTitle()}</h3>
                {foreach from=$group->getItems() item='item'}
                  {$item->render()}
                {/foreach}
              </div>
            {/foreach}
          </div>
          <div class="row">
            <hr>
            {if call_user_func(array('CRM_Core_Permission','check'), 'delete contacts')}
              <a href="/civicrm/contact/view/delete?reset=1&delete=1&cid={$contactInfo.contact_id}">
                <button class="btn btn-danger pull-right">
                  <span><i class="fa fa-trash"></i></span> Delete Staff Member
                </button>
              </a>
            {/if}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
