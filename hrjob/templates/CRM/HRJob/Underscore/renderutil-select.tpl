{*
  @param string id
  @param string name
  @param string selected
  @param array options

  FIXME: escape "value" attribute
  TODO: make placeholder element optional/configurable
*}
{literal}
<script id="renderutil-select-template" type="text/template">
  <select class="crm-form-select crm-select2" id="<%= id %>" name="<%= name %>" data-api-entity="<%= entity %>" data-api-field="<%= field %>" >
    <option value="">{/literal}{ts}- select -{/ts}{literal}</option>
    <% _.each(options, function(optionLabel, optionValue) { %>
    <option value="<%- optionValue %>" <%= selected == optionValue ? 'selected' : ''  %>><%- optionLabel %></option>
    <% }); %>
  </select>
</script>
{/literal}
