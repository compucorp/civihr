{assign var=relativeName value=$fieldName|cat:"_relative"}
<div class="row contract-dates hidden form-group">
  <div class="col-lg-3 col-sm-4">
    <label>
      Job Contract
      {if $relativeName eq 'contract_start_date_relative'}Start{else}End{/if}
      Date
    </label>
    <div class="date-range-selector">
      {$form.$relativeName.html}
    </div>
  </div>
  <div class="col-lg-3 col-sm-4 absolute-date-range">
    {assign var=fromName value=$fieldName|cat:$from}
    {$form.$fromName.label}
    <div>
      {include file="CRM/common/jcalendar.tpl" elementName=$fromName}
    </div>
  </div>
  <div class="col-lg-3 col-sm-4 absolute-date-range">
    {assign var=toName value=$fieldName|cat:$to}
    {$form.$toName.label}
    <div>
      {include file="CRM/common/jcalendar.tpl" elementName=$toName}
    </div>
  </div>
</div>
