<script id="hrabsence-new-template" type="text/template">
  <form>
  {ts}New Absence{/ts}:
  {literal}
    <select name="activity_type_id">
      <option value="">(Select Type)</option>
      <% _.each(FieldOptions.activity_type_id, function(label, value){ %>
      <option value="<%= value %>"><%- label %></option>
      <% }) %>
    </select>
  {/literal}
  </form>
</script>
