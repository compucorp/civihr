<script id="hrjob-role-template" type="text/template">
    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-title">{ts}Title{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-title" name="title"/>
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-description">{ts}Description{/ts}</label>
      </div>
      <div class="crm-content">
        <textarea id="hrjob-description" name="description"></textarea>
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-hours">{ts}Hours{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-hours" name="hours" type="text" />
        <label for="hrjob-role_hours_unit">{ts}per{/ts}</label>
        {literal}
        <%= RenderUtil.select({
          id: 'hrjob-role_hours_unit',
          name: 'role_hours_unit',
          entity: 'HRJobRole'
        }) %>
        {/literal}
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-cost_center">{ts}Cost Center{/ts}&nbsp;{help id='hrjob-cost-center' file='CRM/HRJob/Page/helptext'}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-cost_center" name="cost_center"/>
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-total_pay">{ts}Total Pay{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-total_pay" name="total_pay" size="30" type="text" disabled="true"/>
        <input id="hrjob-total_pay_amount" name="total_pay_amount" size="15" type="hidden" disabled="true"/>

      </div>
    </div>

    <div class="crm-summary-row" style="display: none">
      <div class="crm-label">
        <label for="hrjob-percent_pay_role">{ts}Percent of Pay Assigned to this Role{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-percent_pay_role_<%= cid %>" name="percent_pay_role_<%= cid %>" size="15" type="int" disabled="true" /> %
      </div>
    </div>

    <div class="crm-summary-row" style="display: none">
      <div class="crm-label">
        <label for="hrjob-Actual_amount">{ts}Pay assigned to this role{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-actual_amount" name="actual_amount"  size="30" type="float" disabled="true">  </span>
      </div>
    </div>

    <div class="crm-summary-row" style="display: none">
      <div class="crm-label">
        <label for="hrjob-funder">{ts}Funder{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="funder" name="funder" class="crm-form-entityref" data-select-params='{literal}{"multiple":true}{/literal}' data-create-links="true" data-api-params='{literal}{"params":{"contact_type":"Organization"}}{/literal}' placeholder="{ts}- select -{/ts}" />

        <input id="hrjob-percent_pay_funder" name="percent_pay_funder" size="15" type="int" /> %
      </div>
    </div>

    <div class="crm-summary-row multi-funder">
      <div class="crm-label">
        <label for="hrjob-funder">{ts}Funder{/ts}</label>
      </div>
      <div class="crm-content">
        <table class="hrjob-role-funder-table">
          <thead>
            <tr>
              <td>{ts}Funder{/ts}</td>
              <td>{ts}% of pay assigned to funder{/ts}</td>
              <td>{ts}Pay amount assigned to funder{/ts}</td>
              <td></td>
            </tr>
          </thead>
          <tbody class="funderTableBody">
            <tr data-funder-no="0">
              <td>
                <input id="funders-0_<%= cid %>" name="funders-0_<%= cid %>" class="crm-form-entityref" data-create-links="true" data-api-params='{literal}{"params":{"contact_type":"Organization"}}{/literal}' placeholder="{ts}- select -{/ts}" />
              </td>
              <td>
                <input id="hrjob-percent_pay_funder-0_<%= cid %>" name="percent_pay_funder-0_<%= cid %>" size="15" type="int" class="funderPerc" />
              </td>
              <td><input class="pay_amount_to_funder" type="int" disabled="true" /></td>
              <td></td>
            </tr>
          </tbody>
        </table>
        <a href="#" class="hrjob-role-funder-add">{ts}Add funder{/ts}</a>
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-department">{ts}Department{/ts}</label>
      </div>
      <div class="crm-content">
      {literal}
        <%= RenderUtil.select({
        id: 'hrjob-department',
        name: 'department',
        entity: 'HRJobRole'
        }) %>
      {/literal}
      {include file="CRM/HRJob/Page/EditOptions.tpl" group='hrjob_department'}
      </div>
    </div>
    {* // HR-394
    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-functional_area">{ts}Functional Area{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-functional_area" name="functional_area"/>
      </div>
    </div>
    *}

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-location">{ts}Location{/ts}</label>
      </div>
      <div class="crm-content">
      {literal}
        <%= RenderUtil.select({
        id: 'hrjob-location',
        name: 'location',
        entity: 'HRJobRole'
        }) %>
      {/literal}
      {include file="CRM/HRJob/Page/EditOptions.tpl" group='hrjob_location'}
      </div>
    </div>

    <div class="crm-summary-row">
       <div class="crm-label">
        <label for="hrjob-level_type">{ts}Level{/ts}</label>
      </div>
      <div class="crm-content">
      {literal}
        <%= RenderUtil.select({
        id: 'hrjob-level_type',
        name: 'level_type',
        entity: 'HRJobRole'
        }) %>
      {/literal}
      {include file="CRM/HRJob/Page/EditOptions.tpl" group='hrjob_level_type'}
      </div>
    </div>

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-manager_contact_id">{ts}Manager{/ts}</label>
      </div>
      <div class="crm-content">
        <input id="hrjob-manager_contact_id" name="manager_contact_id" class="crm-form-entityref" data-api-params='{literal}{"params":{"contact_type":"Individual"}}{/literal}' placeholder="{ts}- select -{/ts}" />
      </div>
    </div>

    {* //HR-394
    <div class="crm-summary-row per">
      <div class="crm-label">
        <label for="hrjob-organization">{ts}Organization{/ts}</label>
      </div>
      <div class="crm-content test">
        <input id="hrjob-organization" name="organization" class="crm-form-entityref"  data-api-params='{literal}{"params":{"contact_type":"Organization"}}{/literal}' placeholder="{ts}- select -{/ts}" />
      </div>
    </div>
    *}

    <div class="crm-summary-row">
      <div class="crm-label">
        <label for="hrjob-region">{ts}Region{/ts}</label>
      </div>
      <div class="crm-content">
      {literal}
        <%= RenderUtil.select({
        id: 'hrjob-region',
        name: 'region',
        entity: 'HRJobRole'
        }) %>
      {/literal}
      {include file="CRM/HRJob/Page/EditOptions.tpl" group='hrjob_region'}
      </div>
    </div>

</script>
