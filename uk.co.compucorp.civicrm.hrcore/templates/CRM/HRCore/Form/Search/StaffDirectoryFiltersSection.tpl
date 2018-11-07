<div class="panel-body">
  <div class="row form-group">
    <div class="col-md-2 col-sm-3">
      {$form.select_staff.label}
      <div id="select-staff">
        {$form.select_staff.html}
      </div>
    </div>
    <div class="col-md-2 col-sm-3">
      {$form.job_title.label}
      <div>
        {$form.job_title.html}
      </div>
    </div>
    <div class="col-md-2 col-sm-3">
      {$form.department.label}
      <div>
        {$form.department.html}
      </div>
    </div>
    <div class="col-md-2 col-sm-3">
      {$form.location.label}
      <div>
        {$form.location.html}
      </div>
    </div>
  </div>
  {include file="CRM/HRCore/Form/Search/StaffDirectoryFiltersSectionDateRange.tpl" fieldName="contract_start_date" from='_low' to='_high'}
  {include file="CRM/HRCore/Form/Search/StaffDirectoryFiltersSectionDateRange.tpl" fieldName="contract_end_date" from='_low' to='_high'}
  <div class="row form-group">
    <div class="col-md-2 col-sm-3">
      <button class="btn btn-primary btn-sm form-control">Filter</button>
    </div>
  </div>
</div>
<script type="text/javascript">
  {literal}
  CRM.$(function($) {
    var CONTRACT_DATES_BLOCK_CLASS = '.contract-dates';

    addFormTextClassToDatepickerInputs();
    initSelectStaffControl();
    toggleContractDates();
    initDateRangeSelectors();
    $.each($('.date-range-selector select'), function () {
      var $dateRangeSelector = $(this);

      toggleAbsoluteDateRangeFields($dateRangeSelector);
    })

    /**
     * Adds 'crm-form-text` class to datepickers inputs
     * inside contract dates range blocks
     *
     * @NOTE this is needed to make them look like regular inputs
     */
    function addFormTextClassToDatepickerInputs () {
      $(CONTRACT_DATES_BLOCK_CLASS)
        .find('.dateplugin')
        .addClass('crm-form-text')
    }

    /**
     * Initiates the Date Range selectors.
     * Toggles absolute date range inputs on selectors values changes.
     */
    function initDateRangeSelectors() {
      $('.date-range-selector select').on('change', function () {
        var $dateRangeSelector = $(this)

        toggleAbsoluteDateRangeFields($dateRangeSelector);
      })
    }

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
     * Toggles contract dates depending on the related Date Range selector value
     *
     * @param {jQuery} $dateRangeSelector
     */
    function toggleAbsoluteDateRangeFields ($dateRangeSelector) {
      var absoluteDateRangeOptionSelected = $dateRangeSelector.val() === '0';
      var $absoluteDateRange = $dateRangeSelector
        .closest(CONTRACT_DATES_BLOCK_CLASS)
        .find('.absolute-date-range');
      var toggleFunction = absoluteDateRangeOptionSelected
        ? 'removeClass'
        : 'addClass';

      $absoluteDateRange[toggleFunction]('hidden');
    }

    /**
     * Toggles contract dates depending on the Select Staff control value
     */
    function toggleContractDates() {
      var selectStaffValue = $('#select-staff select').val();
      var $contractDates = $(CONTRACT_DATES_BLOCK_CLASS);
      var toggleFunction = selectStaffValue === 'choose_date'
        ? 'removeClass'
        : 'addClass';

      $contractDates[toggleFunction]('hidden');
    }
  });
  {/literal}
</script>
