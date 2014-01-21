<script id="hrabsence-filters-template" type="text/template">

  <form>
  {ts}Filter{/ts}:
  {literal}
    <select name="activity_type_id" multiple="multiple">
      <% _.each(FieldOptions.activity_type_id, function(label, value){ %>
      <option value="<%= value %>"><%- label %></option>
      <% }) %>
    </select>
  {/literal}

  {ts}Period{/ts}:
  {literal}
    <select name="period_id" multiple="multiple">
      <% var keys = _.keys(FieldOptions.period_id); %>
      <% keys.reverse(); %>
      <% _.each(keys, function(value){ %>
      <option value="<%= value %>"><%- FieldOptions.period_id[value] %></option>
      <% }) %>
    </select>
  {/literal}

  </form>
</script>
