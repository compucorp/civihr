<script id="hrjob-role-row-template" type="text/template">
  <td>
    {literal}<%= RenderUtil.toggle({className: 'hrjob-role-toggle'}) %>{/literal}
  </td>
  <td>
    <strong class="hrjob-role-toggle" data-hrjobrole-row="title"></strong>
    <div class="toggle-role-form">
    </div>
  </td>
  <td>
    <strong data-hrjobrole-row="hours"></strong>
  </td>
  <td>
    <a class="ui-icon ui-icon-trash hrjob-role-remove" title="{ts}Remove{/ts}"></a>
    <a class="ui-icon ui-icon-refresh hrjob-role-restore" title="{ts}Restore{/ts}"></a>
  </td>
</script>
