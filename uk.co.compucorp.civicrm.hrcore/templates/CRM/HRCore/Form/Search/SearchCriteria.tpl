{if $context EQ 'smog'}
  {capture assign=editTitle}{ts}Find Contacts within this Group{/ts}{/capture}
{elseif $context EQ 'amtg' AND !$rows}
  {capture assign=editTitle}{ts}Find Contacts to Add to this Group{/ts}{/capture}
{else}
  {capture assign=editTitle}{ts}Edit Search Criteria{/ts}{/capture}
{/if}

{strip}
  <div class="crm-block crm-form-block crm-basic-criteria-form-block">
    <div class="crm-accordion-wrapper crm-case_search-accordion {if $rows}collapsed{/if}">
      <div class="crm-accordion-header crm-master-accordion-header">
        {$editTitle}
      </div>
      <div class="crm-accordion-body">

        <div class="crm-section" id="select-staff">
          <div class="label">
            {$form.select_staff.label}
          </div>
          <div class="content">
            {$form.select_staff.html}
          </div>
          <div class="clear"></div>
        </div>

        <div class="contract-dates">
          <div class="crm-section">
            <div class="label">
              Job Contract Start Date
            </div>
            <div class="content">
              {include file="CRM/Core/DateRange.tpl" fieldName="contract_start_date" from='_low' to='_high'}
            </div>
          </div>

          <div class="crm-section">
            <div class="label">
              Job Contract End Date
            </div>
            <div class="content">
              {include file="CRM/Core/DateRange.tpl" fieldName="contract_end_date" from='_low' to='_high'}
            </div>
          </div>
        </div>

        <script type="text/javascript">
          {literal}
          CRM.$(function($) {
            $(document).ready(function() {
              toogleContractDates();
              initSelectStaffControls();
            });

            function initSelectStaffControls() {
              $('#select-staff select').on('change', function(e) {
                toogleContractDates();
              });
            }

            function toogleContractDates() {
              var select_staff_value = $('#select-staff select option:selected').val();
              if (select_staff_value === 'choose_date') {
                $('.contract-dates').show()
              } else {
                $('.contract-dates').hide();
              }
            }
          });
          {/literal}
        </script>

        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>
      </div>
    </div>
  </div>
{/strip}
