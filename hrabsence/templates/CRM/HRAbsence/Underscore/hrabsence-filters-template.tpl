<script id="hrabsence-filters-template" type="text/template">

  <form>
  {ts}Filter{/ts}:
  {literal}
    <select class="crm-form-select crm-select2" name="activity_type_id" multiple="multiple">
      <% _.each(FieldOptions.activity_type_id, function(label, value){ %>
      <option value="<%= value %>"><%- label %></option>
      <% }) %>
    </select>
  {/literal}

  {ts}Period{/ts}:
  {literal}
    <select class="crm-form-select crm-select2" name="period_id" multiple="multiple">
      <% var keys = _.values(FieldOptions.sort_periods); %>
      <% _.each(keys, function(value){ %>
      <option value="<%= value %>"><%- FieldOptions.period_id[value] %></option>
      <% }) %>
    </select>
  {/literal}

  </form>
</script>
