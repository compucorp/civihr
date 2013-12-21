<script id="hrabsence-filters-template" type="text/template">

  <form>
  {ts}Filter{/ts}:
  {literal}
    <select name="activity_type_id">
      <% _.each(FieldOptions.activity_type_id, function(label, value){ %>
      <option value="<%= value %>"><%- label %></option>
      <% }) %>
    </select>
  {/literal}

  {ts}Period{/ts}:
  {literal}
    <select name="period_id">
      <% _.each(FieldOptions.period_id, function(label, value){ %>
      <option value="<%= value %>"><%- label %></option>
      <% }) %>
    </select>
  {/literal}

  </form>
</script>
