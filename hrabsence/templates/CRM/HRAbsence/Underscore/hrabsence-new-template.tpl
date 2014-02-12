<script id="hrabsence-new-template" type="text/template">
  <form>
  {ts}New Absence{/ts}:
  {literal}
    <select name="activity_type_id">
      <% _.each(FieldOptions.activity_type_id, function(label, value){ %>
      <option value="<%= value %>"><%- label %></option>
      <% }) %>
    </select>
  {/literal}
  </form>
</script>
