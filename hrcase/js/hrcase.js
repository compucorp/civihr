// http://civicrm.org/licensing
(function ($, _) {
  // js to hide medium and location on case activity and case screen
  $(document).on('crmLoad', function() {
    $('#crm-activity-view-table .crm-case-activity-view-Client .label').html('Contact');
    $('.crm-case-activity-form-block-medium_id, .crm-case-form-block-medium_id').hide();
    $('.crm-case-other-relationships-block').hide();
    $('.crm-case_activities-accordion').insertAfter($('.case-control-panel'));
    $('.crm-case-roles-block').click(function(){
      $(".crm-case-roles-block .crm-accordion-body .dataTables_wrapper .report-layout tbody tr td:contains('Client')").html('Contact');
    });
    if($('.DataTables_sort_wrapper').html()){
      $('.crm-case-activities-type .DataTables_sort_wrapper').html('Activity');
      $('.crm-case-activities-assignee .DataTables_sort_wrapper').html('Assignee');
    }else{
      $('.crm-case-activities-type').html('Activity');
      $('.crm-case-activities-assignee').html('Assignee');
    }
  });

  // js to update date-time while completing case activity status on edit screen
  $(document).on('change', '.crm-case-activity-form-block-status_id #status_id', updateActivityDate);

  function updateActivityDate() {
    var statusval = $(this).val(),
      status = CRM.hrcase.statusID;
    if (statusval == status) {
      var prevDate = $('#activity_date_time').val(),
        prevTime = $("input#activity_date_time_time").val(),
        newDate = new Date(),
        newTime = newDate.getHours()+':' + newDate.getMinutes(),
        date_format = $('input#activity_date_time').attr('format'),
        displayDateValue = $.datepicker.formatDate(date_format, newDate);
      $('input#activity_date_time, input#activity_date_time_display').val( displayDateValue );
      var displayTimeValue = $('input#activity_date_time_time').val(newTime).trigger( 'focus' ).val();
      status = 'Changed from "'+prevDate+' '+prevTime+'" to "'+displayDateValue+' '+displayTimeValue+'". <span id="revert-link"> <a href="javascript:void(0)">Undo</a></span> ';
      CRM.alert(status, 'Updated Completion Time ', 'notice');
      $('#revert-link a').on('click', function() {
        $('input#activity_date_time, input#activity_date_time_display').val( prevDate );
        $('input#activity_date_time_time').val( prevTime );
        $('input#activity_date_time_time').trigger( 'focus' );
      });
    }
  }

// js to update date-time while completing case activity status on Case manage screen
  var manageScreen = CRM.hrcase.manageScreen;
  if( manageScreen ) {
    $(document).ajaxSuccess(function(event, xhr, settings) {
      var statusUrl = settings.url,
        status = CRM.hrcase.statusID,
        searchUrl = CRM.url("civicrm/ajax/rest"),
        params = {sequential: "1"};
      if (statusUrl == searchUrl) {
        var data = settings.data,
          hash,
          hashes = data.split('&');
        for(var i = 0; i < hashes.length; i++) {
          hash = hashes[i].split('=');
          params[hash[0]] = hash[1];
        }
        if( (params['entity'] == "Activity") && (params['status_id'] == status) && (params['action'] == "update") ) {
          var response = $.parseJSON(xhr.responseText);
          var link = $('a.crm-activity-change-status'),
            activityId = response.id,
            caseId = params['case_id'];
          var date = new Date();
          var dateValue = date.toJSON();
          var dataUrl = CRM.url('civicrm/ajax/rest');
          var data = 'json=1&version=3&entity=Activity&action=update&id=' + activityId + '&activity_date_time=' + dateValue + '&case_id=' + caseId;
          $.ajax({
            type     : 'POST',
            dataType : 'json',
            url      : dataUrl,
            data     : data,
            success  : function(values) {
              if( values.is_error ) {
                CRM.alert(values.error_message, ts('Unable to change activity date time to current date time'), 'error');
                return false;
              }
              else {
                // reload the table on success
                if (window.buildCaseActivities) {
                  buildCaseActivities(true);
                }
              }
            },
            error : function(jqXHR) {
              CRM.alert(jqXHR.responseText, jqXHR.statusText, 'error');
              return false;
            }
          });
        }
      }
    });
  }
}(CRM.$, CRM._));
