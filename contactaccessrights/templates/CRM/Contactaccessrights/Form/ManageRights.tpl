<div>
    <h2>Contact Access Rights</h2>
    <p>You can specify an administrator's access to contacts by region or location here, or grant them access to all
        contacts using Drupal permissions <a href="/admin/people/permissions" target="_blank">here</a>.</p>
    <p>The user will then have access to contacts whose active roles place them in this region or location.</p>
    <p>
        For even more granular permissions, you can use ACL groups
        <a href="/civicrm/admin/access?reset=1" target="_blank">here</a>.
    </p>

    <div class="crm-section">
    <div class="label">{$form.regions.label}</div>
        <div class="content">{$form.regions.html}</div>
        <div class="clear"></div>
    </div>

    <div class="crm-section">
        <div class="label">{$form.locations.label}</div>
        <div class="content">{$form.locations.html}</div>
        <div class="clear"></div>
    </div>
</div>

<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
