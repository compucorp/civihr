<h1 class="title">{if $action eq 1}{ts}New Absence Period{/ts}{elseif $action eq 2}{ts}Edit Absence Period{/ts}{/if}</h1>

<div class="crm-block crm-form-block crm-absence-period-form-block crm-leave-and-absences-form-block">
  <table class="form-layout">
    <tr>
      <td class="label">{$form.title.label}</td>
      <td class="html-adjust">{$form.title.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.start_date.label}</td>
      <td class="html-adjust">{$form.start_date.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.end_date.label}</td>
      <td class="html-adjust">{$form.end_date.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.weight.label}</td>
      <td class="html-adjust">{$form.weight.html}</td>
    </tr>
  </table>
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
