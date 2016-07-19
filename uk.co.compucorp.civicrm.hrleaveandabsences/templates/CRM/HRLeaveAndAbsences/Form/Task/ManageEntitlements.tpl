<div class="crm-block crm-form-block crm-absence-period-form-block crm-leave-and-absences-form-block">
  {if $single eq false}
    <div class="messages status no-popup">{include file="CRM/Contact/Form/Task.tpl"}</div>
  {/if}
  <table class="form-layout">
    <tr>
      <td class="label">{$form.absence_period.label}</td>
      <td class="html-adjust">{$form.absence_period.html}</td>
    </tr>
  </table>
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
