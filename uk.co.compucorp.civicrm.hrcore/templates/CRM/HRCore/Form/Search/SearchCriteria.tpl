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

        <div class="crm-section">
          <div class="label">
            {$form.name.label}
          </div>
          <div class="content">
            {$form.name.html}
          </div>
          <div class="clear"></div>
        </div>

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

        <div class="crm-section">
          <div class="label">
            {$form.job_title.label}
          </div>
          <div class="content">
            {$form.job_title.html}
          </div>
          <div class="clear"></div>
        </div>

        <div class="crm-section">
          <div class="label">
            {$form.department.label}
          </div>
          <div class="content">
            {$form.department.html}
          </div>
          <div class="clear"></div>
        </div>

        <div class="crm-section">
          <div class="label">
            {$form.location.label}
          </div>
          <div class="content">
            {$form.location.html}
          </div>
          <div class="clear"></div>
        </div>

        <script type="text/javascript">
          {literal}
          CRM.$(function($) {
            $(document).ready(function() {
              toggleContractDates();
              initSelectStaffControl();
            });

            /**
             * Initiates the Select Staff control.
             * Toggles contract dates on change.
             */
            function initSelectStaffControl() {
              $('#select-staff select').on('change', function(e) {
                toggleContractDates();
              });
            }

            /**
             * Toggles contract dates depending on the Select Staff control value
             */
            function toggleContractDates() {
              var $selectStaffValue = $('#select-staff select option:selected').val();
              var $contractDates = $('.contract-dates');

              if ($selectStaffValue === 'choose_date') {
                $contractDates.show()
              } else {
                $contractDates.hide();
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
