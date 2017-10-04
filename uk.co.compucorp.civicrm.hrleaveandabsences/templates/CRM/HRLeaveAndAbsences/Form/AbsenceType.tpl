<div id="bootstrap-theme">
  <div class="panel panel-default crm-form-block crm-absence_type-form-block crm-leave-and-absences-form-block">
    <div class="panel-heading">
      <h1 class="panel-title">
        {if $action eq 1}{ts}New Leave/Absence Type{/ts}
        {elseif $action eq 2}{ts}Edit Leave/Absence Type{/ts}{/if}
      </h1>
    </div>
    <div class="panel-body">
      {if $action neq 8}
        <div class="row">
          <div class="col-sm-8">
            <h3>{ts}Basic Details{/ts}</h3>
            <div class="form-group row">
              <div class="col-sm-6">{$form.title.label}</div>
              <div class="col-sm-6">{$form.title.html}</div>
            </div>
            <div class="form-group row">
              <div class="col-sm-6">{$form.color.label}</div>
              <div class="col-sm-6">{$form.color.html}</div>
            </div>
            <div class="form-group row">
              <div class="col-sm-6">{$form.is_default.label}</div>
              <div class="col-sm-6">{$form.is_default.html}</div>
            </div>
            <div class="form-group row">
              <div class="col-sm-6">{$form.is_reserved.label}</div>
              <div class="col-sm-6">{$form.is_reserved.html}</div>
            </div>
            <div class="form-group row">
              <div class="col-sm-6">{$form.calculation_unit.label}</div>
              <div class="col-sm-6">{$form.calculation_unit.html}</div>
            </div>
          </div>
          <div class="col-sm-8">
            <div class="form-group row">
              <div class="col-sm-6">{$form.default_entitlement.label}</div>
              <div class="col-sm-6">{$form.default_entitlement.html}</div>
            </div>
            <div class="form-group row">
              <div class="col-sm-6">{$form.add_public_holiday_to_entitlement.label}</div>
              <div class="col-sm-6">{$form.add_public_holiday_to_entitlement.html}</div>
            </div>
            <div class="form-group row">
              <div class="col-sm-6">{$form.notification_receivers_ids.label}</div>
              <div class="col-sm-6">{$form.notification_receivers_ids.html}</div>
            </div>
            <div class="form-group row">
              <div class="col-sm-6">{$form.is_active.label}</div>
              <div class="col-sm-6">{$form.is_active.html}</div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-8">
            <h3>{ts}Requesting Leave/Absence{/ts}</h3>
            <div class="form-group row">
              <div class="col-sm-6">{$form.max_consecutive_leave_days.label}</div>
              <div class="col-sm-6">{$form.max_consecutive_leave_days.html}</div>
            </div>
            <div class="form-group row">
              <div class="col-sm-6">{$form.allow_request_cancelation.label}</div>
              <div class="col-sm-6">{$form.allow_request_cancelation.html}</div>
            </div>
            <div class="form-group row">
              <div class="col-sm-6">{$form.allow_overuse.label}</div>
              <div class="col-sm-6">{$form.allow_overuse.html}</div>
            </div>
          </div>
          <div class="col-sm-8">
            <h3>{ts}TOIL{/ts}</h3>
            <div class="form-group row">
              <div class="col-sm-6">{$form.allow_accruals_request.label}</div>
              <div class="col-sm-6">{$form.allow_accruals_request.html}</div>
            </div>
            <div class="form-group row toil-option">
              <div class="col-sm-6">{$form.max_leave_accrual.label}</div>
              <div class="col-sm-6">{$form.max_leave_accrual.html}</div>
            </div>
            <div class="form-group row toil-option">
              <div class="col-sm-6">{$form.allow_accrue_in_the_past.label}</div>
              <div class="col-sm-6">{$form.allow_accrue_in_the_past.html}</div>
            </div>
            <div class="form-group row toil-option">
              <div class="col-sm-6">{ts}Default expiry of accrued amounts{/ts}</div>
              <div class="col-sm-6">
                {assign var="accrual_never_expire" value=false }
                {if $form.accrual_expiration_duration.value eq '' and $form.accrual_expiration_unit.value.0 eq ''}
                  {assign var="accrual_never_expire" value=true }
                {/if}
                <label><input type="checkbox" id="accrual_never_expire" {if $accrual_never_expire}checked{/if}> {ts}Never expire{/ts}</label>
                <br/>
                <span class="toil-expiration">{$form.accrual_expiration_duration.html}{$form.accrual_expiration_unit.html}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-8">
            <h3>{ts}Public Holidays{/ts}</h3>
              <div class="form-group row">
                <div class="col-sm-6">{$form.must_take_public_holiday_as_leave.label}</div>
                <div class="col-sm-6">{$form.must_take_public_holiday_as_leave.html}</div>
              </div>
          </div>
          <div class="col-sm-8">
            <h3>{ts}Carry Forward{/ts}</h3>
            <div class="form-group row">
              <div class="col-sm-6">{$form.allow_carry_forward.label}</div>
              <div class="col-sm-6">{$form.allow_carry_forward.html}</div>
            </div>
            <div class="form-group row carry-forward-option">
              <div class="col-sm-6">{$form.max_number_of_days_to_carry_forward.label}</div>
              <div class="col-sm-6">{$form.max_number_of_days_to_carry_forward.html}</div>
            </div>
            <div class="form-group row carry-forward-option">
              <div class="col-sm-6">{ts}Carry forward leave expiry{/ts}</div>
              <div class="col-sm-6">
                {assign var="carry_forward_never_expire" value=false }
                {if $form.carry_forward_expiration_duration.value eq '' and $form.carry_forward_expiration_unit.value.0 eq ''}
                  {assign var="carry_forward_never_expire" value=true }
                {/if}
                <label><input type="radio" name="carry_forward_expiration" id="carry_forward_never_expire" {if $carry_forward_never_expire}checked{/if}> {ts}Never expire{/ts}</label>
                <br/>
                <label><input type="radio" name="carry_forward_expiration" id="carry_forward_expire_after_duration" {if not $carry_forward_never_expire}checked{/if}> {ts}Expire after a certain duration{/ts}</label>
                <br/>
                <span class="carry-forward-expiration-duration">{$form.carry_forward_expiration_duration.html}{$form.carry_forward_expiration_unit.html}<br/></span>
              </div>
            </div>
          </div>
        </div>
      {literal}
          <script type="text/javascript">
              CRM.$(function($) {
                  function initToilControls() {
                      var allow_accruals_request = $('#allow_accruals_request');
                      var accrual_never_expire = $('#accrual_never_expire');

                      if(allow_accruals_request.is(':checked')) {
                          $('.toil-option').show();
                      }

                      if(!accrual_never_expire.is(':checked')) {
                          $('.toil-expiration').show();
                      }

                      allow_accruals_request.on('click', function() {
                          if(this.checked) {
                              $('.toil-option').show();
                          } else {
                              hideToilOptions();
                          }
                      });

                      accrual_never_expire.on('click', function() {
                          if(this.checked) {
                              hideToilExpiration();
                          } else {
                              $('.toil-expiration').show();
                          }
                      });
                  }

                  function hideToilOptions() {
                      document.getElementById('max_leave_accrual').value = '';
                      var allow_accrue_in_the_past_radios = document.getElementsByName('allow_accrue_in_the_past')
                      for(i = 0; i < allow_accrue_in_the_past_radios.length; i++) {
                          allow_accrue_in_the_past_radios.item(i).checked = false;
                      }
                      document.getElementById('accrual_never_expire').checked = true;
                      hideToilExpiration();
                      $('.toil-option').hide();
                  }

                  function hideToilExpiration() {
                      document.getElementById('accrual_expiration_duration').value = '';
                      $('#accrual_expiration_unit').select2('val', '');
                      $('.toil-expiration').hide();
                  }

                  function initCarryForwardControls() {
                      var allow_carry_forward = $('#allow_carry_forward');
                      var carry_forward_never_expire = $('#carry_forward_never_expire');
                      var carry_forward_expire_after_duration = $('#carry_forward_expire_after_duration');

                      if(allow_carry_forward.is(':checked')) {
                          $('.carry-forward-option').show();
                      }

                      if(!carry_forward_never_expire.is(':checked')) {
                          $('.carry-forward-expiration-duration').show();
                      }

                      allow_carry_forward.on('click', function() {
                          if(this.checked) {
                              $('.carry-forward-option').show();
                          } else {
                              hideCarryForwardOptions();
                          }
                      });

                      carry_forward_never_expire.on('click', function() {
                          if(this.checked) {
                              hideCarryForwardExpirationDuration();
                          }
                      });

                      carry_forward_expire_after_duration.on('click', function() {
                          if(this.checked) {
                              $('.carry-forward-expiration-duration').show();
                          }
                      });
                  }

                  function hideCarryForwardOptions() {
                      var carry_forward_expiration_radios = document.getElementsByName('carry_forward_expiration');
                      document.getElementById('max_number_of_days_to_carry_forward').value = '';
                      for(i = 0; i < carry_forward_expiration_radios.length; i++) {
                          carry_forward_expiration_radios.item(i).checked = false;
                      }
                      carry_forward_expiration_radios.item(0).checked = true;
                      hideCarryForwardExpirationDuration();
                      $('.carry-forward-option').hide();
                  }

                  function hideCarryForwardExpirationDuration() {
                      document.getElementById('carry_forward_expiration_duration').value = '';
                      $('#carry_forward_expiration_unit').select2('val', '');
                      $('.carry-forward-expiration-duration').hide();
                  }

                  function initColorPicker() {
                      $('#color').spectrum({
                          preferredFormat: "hex3",
                          allowEmpty:true,
                          showPaletteOnly: true,
                          showPalette:true,
                          {/literal}
                          palette: {$availableColors}
                          {literal}
                      });
                  }

                  function initDeleteButton() {
                      $('.crm-button-type-delete').on('click', function(e) {
                        e.preventDefault();
                        {/literal}
                        {if $canDeleteType}
                        {literal}
                            CRM.confirm({
                              title: ts('Delete Leave/Absence type'),
                              message: ts('Are you sure you want to delete this leave/absence type?'),
                              options: {
                                yes: ts('Yes'),
                                no: ts('No')
                              }
                            })
                            .on('crmConfirm:yes', deleteCallback);
                        {/literal}
                        {else}
                        {literal}
                            CRM.alert("This leave/absence type is in use and cannot be deleted. Please disable it instead.",
                                      'Delete Leave/Absence type', 'error');
                        {/literal}
                        {/if}
                        {literal}

                      });
                  }

                  function deleteCallback() {
                      {/literal}
                      window.location = "{$deleteUrl}";
                      {literal}
                  }

                  $(document).ready(function() {
                      initToilControls();
                      initCarryForwardControls();
                      initColorPicker();
                      initDeleteButton();
                  });

              });
          </script>
        {/literal}
      {/if}
    </div>
    <div class="panel-footer clearfix">
      <div class="pull-right">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>
