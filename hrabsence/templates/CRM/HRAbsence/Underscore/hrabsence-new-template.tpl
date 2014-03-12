<script id="hrabsence-new-template" type="text/template">
  <form>
  {ts}New Absence{/ts}:
  {literal}
    <select class="crm-form-select crm-select2" name="activity_type_id">
      <% _.each(FieldOptions.activity_type_id, function(label, value){ %>
      <option value="<%= value %>"><%- label %></option>
      <% }) %>
    </select>
  {/literal}
  </form>
</script>
