<div id="crm-contact-actions-wrapper" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Page_Inline_Actions"{rdelim}'>
  {crmButton id="crm-contact-actions-link" href="#" icon="bars"}
    {ts}Actions{/ts}
  {/crmButton}
  <div id="crm-contact-actions-list" class="crm_contact-actions">
    <div id="bootstrap-theme">
      <div class="crm_contact-actions__body">
        <div class="col-md-4 crm_contact-actions__body__user-info crm_contact-actions__user-info--margin">
          {foreach from=$menu->getHighlightedPanelItems() item='group'}
            <div class="col-md-12">
              <h3>{$group->getTitle()}</h3>
              {foreach from=$group->getItems() item='item'}
                {$item->render()}
              {/foreach}
            </div>
          {/foreach}
        </div>
        <div class="col-md-8 crm_contact-actions__body__user-actions crm_contact-actions--bg-white">
          {foreach from=$menu->getMainPanelItems() item='group'}
            <div class="col-md-4">
              <h3>{$group->getTitle()}</h3>
              {foreach from=$group->getItems() item='item'}
                {$item->render()}
              {/foreach}
            </div>
          {/foreach}
        </div>
      <div>
      <div class="crm_contact-actions__footer">
        <div class="col-md-4 crm_contact-actions__footer__user-info">
          {if !empty($contactInfo.id)}
            <div class="col-md-12">
              <hr class="crm_contact-actions--dark-gray-blue">
            </div>
            {if $userAccountDisabled}
              <div class="col-md-6">
                <a class="btn btn-warning crm_contact-actions__btn" href="/civicrm/contactactionsmenu/enableuseraccount?cid={$contactInfo.contact_id}">
                  Enable User Account
                </a>
              </div>
            {else}
              <div class="col-md-6">
                <a class="btn btn-warning crm_contact-actions__btn" href="/civicrm/contactactionsmenu/disableuseraccount?cid={$contactInfo.contact_id}">
                  Disable User Account
                </a>
              </div>
            {/if}
              <div class="col-md-6">
                <a class="btn btn-danger crm_contact-actions__btn" href="/civicrm/contactactionsmenu/deleteuseraccount?cid={$contactInfo.contact_id}">
                  Delete User Account
                </a>
              </div>
          {/if}
        </div>
        <div class="col-md-8 crm_contact-actions__footer__user-actions crm_contact-actions--bg-white">
          <div class="col-md-12">
            <hr class="crm_contact-actions--dark-gray-blue">
          </div>
          <div class="col-md-12">
            {if call_user_func(array('CRM_Core_Permission','check'), 'delete contacts')}
              <a class="btn btn-danger crm_contact-actions__btn pull-right" href="/civicrm/contact/view/delete?reset=1&delete=1&cid={$contactInfo.contact_id}">
                <span><i class="fa fa-trash"></i></span> Delete Staff Member
              </a>
            {/if}
          <div>
        </div>
      </div>
    </div>
  <div>
</div>
