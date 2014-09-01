<script id="hrjob-funding-template" type="text/template">
<form>
  <h3>{ts}Funding{/ts}</h3>
  {* --HR-395
  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-is_tied_to_funding">{ts}Tied to Funding{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="hrjob-is_tied_to_funding" name="is_tied_to_funding" type="checkbox" />
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-funding_org_id">{ts}Funding organization{/ts}</label>
    </div>
    <div class="crm-content">
      <input id="funding_org_id" name="funding_org_id" class="crm-form-entityref" data-api-params='{literal}{"params":{"contact_type":"Organization"}}{/literal}' placeholder="{ts}- select -{/ts}" />
    </div>
  </div>
  *}

  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-funding_notes">{ts}Funding Notes{/ts}</label>
    </div>
    <div class="crm-content">
      <textarea id="hrjob-funding_notes" name="funding_notes"></textarea>
    </div>
  </div>

  {literal}<% if (!isNewDuplicate) { %> {/literal}
  <button class="crm-button standard-save">{ts}Save{/ts}</button>
  {literal}<% } else { %>{/literal}
  <button class="crm-button standard-save">{ts}Save New Copy{/ts}</button>
  {literal}<% } %>{/literal}
  <button class="crm-button standard-reset">{ts}Reset{/ts}</button>
</form>
</script>
