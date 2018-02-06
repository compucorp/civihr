<div id="crm-contact-actions-wrapper" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Page_Inline_Actions"{rdelim}'>
  {crmButton id="crm-contact-actions-link" href="#" icon="bars"}
    {ts}Actions{/ts}
  {/crmButton}
  <div id="crm-contact-actions-list" class="crm_contact-actions">
    <div id="bootstrap-theme" class="crm_contact-actions__wrapper">
      <div class="crm_contact-actions__panel crm_contact-actions__panel--primary">
        <div class="crm_contact-actions__panel__body">
          {foreach from=$menu->getHighlightedPanelItems() item='group'}
            <div class="crm_contact-actions__group">
              <h3>{$group->getTitle()}</h3>
              {foreach from=$group->getItems() item='item'}
                {$item->render()}
              {/foreach}
            </div>
          {/foreach}
          <div class="crm_contact-actions__group">
          <h3>User Has Access To:</h3>
          <p>
            <span>No staff members</span>
            <span class="fa fa-question-circle"></span>
          </p>
          <a href="#" class="btn btn-secondary">Manage Regional Access</a>
          </div>
        </div>
        <div class="crm_contact-actions__panel__footer">
          <div class="row">
            {if !empty($contactInfo.cmsId)}
              <div class="col-md-6">
                {if $userAccountDisabled}
                  <a class="btn btn-warning"
                    href="/civicrm/contactactionsmenu/enableuseraccount?cid={$contactInfo.contact_id}">
                    Enable User Account
                  </a>
                {else}
                  <a class="btn btn-warning"
                    href="/civicrm/contactactionsmenu/disableuseraccount?cid={$contactInfo.contact_id}">
                    Disable User Account
                  </a>
                {/if}
              </div>
              <div class="col-md-6">
                <a class="btn btn-danger"
                  data-delete-user-url="/civicrm/contactactionsmenu/deleteuseraccount?cid={$contactInfo.contact_id}">
                  Delete User Account
                </a>
              </div>
            {/if}
          </div>
        </div>
      </div>
      <div class="crm_contact-actions__panel crm_contact-actions__panel--secondary">
        <div class="crm_contact-actions__panel__body">
          <div class="crm_contact-actions__group">
            <h3>Leave:</h3>
            <div class="crm_contact-actions__action">
              <a href="#" class="btn btn-primary-outline">
                <i class="fa fa-plus"></i> Record New
              </a>
            </div>
            <div class="crm_contact-actions__action">
              <a href="#" class="btn btn-primary-outline">
                <i class="fa fa-search"></i> View Entitlements
              </a>
            </div>
            <hr>
            <h4>Leave Approver(s):</h4>
            <p><a href="#" class="text-primary">John Snow</a></p>
            <p><a href="#" class="text-primary">Allan Wite</a></p>
            <div class="crm_contact-actions__action">
              <a href="#" class="btn btn-secondary">Manage Leave Approver</a>
            </div>
          </div>
          {foreach from=$menu->getMainPanelItems() item='group'}
            <div class="crm_contact-actions__group">
              <h3>{$group->getTitle()}</h3>
              {foreach from=$group->getItems() item='item'}
                {$item->render()}
              {/foreach}
            </div>
          {/foreach}
          <div class="crm_contact-actions__group">
            <h3>Communicate </h3>
            <div class="crm_contact-actions__action">
              <a href="#" class="btn btn-primary-outline">
                <i class="fa fa-envelope-o"></i> Send Email
              </a>
            </div>
            <div class="crm_contact-actions__action">
              <a href="#" class="btn btn-primary-outline">
                <i class="fa fa-users"></i> Record Meeting
              </a>
            </div>
            <div class="crm_contact-actions__action">
              <a href="#" class="btn btn-primary-outline">
                <i class="fa fa-file-pdf-o"></i> Create PDF Letter
              </a>
            </div>
          </div>
        </div>
        <div class="crm_contact-actions__panel__footer">
          {if call_user_func(array('CRM_Core_Permission','check'), 'delete contacts')}
            <a class="btn btn-danger pull-right"
              href="/civicrm/contact/view/delete?reset=1&delete=1&cid={$contactInfo.contact_id}">
              <span><i class="fa fa-trash"></i></span> Delete Staff Member
            </a>
          {/if}
        </div>
      </div>
    </div>
  <div>
</div>
