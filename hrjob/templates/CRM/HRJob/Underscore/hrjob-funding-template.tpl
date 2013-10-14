<script id="hrjob-funding-template" type="text/template">
<form>
  <h3>{ts}Funding{/ts}</h3>

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
      <label for="hrjob-funding_notes">{ts}Funding Notes{/ts}</label>
    </div>
    <div class="crm-content">
      <textarea id="hrjob-funding_notes" name="funding_notes"></textarea>
    </div>
  </div>

  {literal}<% if (!isNewDuplicate) { %> {/literal}
  <button class="standard-save">{ts}Save{/ts}</button>
  {literal}<% } else { %>{/literal}
  <button class="standard-save">{ts}Save New Copy{/ts}</button>
  {literal}<% } %>{/literal}
  <button class="standard-reset">{ts}Reset{/ts}</button>
</form>
</script>
