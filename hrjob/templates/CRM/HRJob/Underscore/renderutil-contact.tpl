{*
  @param string id form field ID (for the canonical field element)
  @param string name  form field name (for the canonical field element)
*}
{literal}
<script id="renderutil-contact-template" type="text/template">
  <input type="hidden" id="<%= id %>" name="<%= name %>" class="crm-contact-selector" />
  (<span name="<%= name %>"></span>)
</script>
{/literal}
