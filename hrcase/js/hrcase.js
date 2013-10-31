// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
// js to hide medium and location on case activity and case screen
cj(document).ready(function ($) {
  cj('.crm-case-activity-form-block-medium_id').hide();
});

cj(document).ready(function ($) {
  cj('.crm-case-form-block-medium_id').hide();
});

// js to update date-time while completing case activity status on edit screen
cj(document).ready(function ($) {
  cj('.crm-case-activity-form-block-status_id #status_id').on('change',function(){
    updateActivityDate(this);
  });
});

function updateActivityDate(obj) {
  var statusval = cj(obj).val();
  var status = CRM.hrcase.statusID;
  if( statusval == status ) {
    var prevDate = cj('#activity_date_time').val();
    var prevTime = cj("input#activity_date_time_time").val();
    var newDate = new Date();
    var newTime = newDate.getHours()+':' + newDate.getMinutes();
    var date_format = cj('input#activity_date_time').attr('format');
    var displayDateValue = cj.datepicker.formatDate( date_format, newDate  );
    cj('input#activity_date_time, input#activity_date_time_display').val( displayDateValue );    
    var displayTimeValue = cj('input#activity_date_time_time').val(newTime).trigger( 'focus' ).val();
    var status = 'Changed from "'+prevDate+' '+prevTime+'" to "'+displayDateValue+' '+displayTimeValue+'". <span id="revert-link"> <a href="javascript:void(0)">Undo</a></span> ';
    CRM.alert( status, 'Updated Completion Time ', 'notice');
    cj('#revert-link a').on('click',function(){
      cj('input#activity_date_time, input#activity_date_time_display').val( prevDate );    
      cj('input#activity_date_time_time').val( prevTime );
      cj('input#activity_date_time_time').trigger( 'focus' );
    });
  }
}

// js to update date-time while completing case activity status on Case manage screen
var manageScreen = CRM.hrcase.manageScreen;
if( manageScreen ) {
  cj(document).ajaxSuccess(function(event, xhr, settings) {
    var statusUrl = settings.url;
    var status = CRM.hrcase.statusID;
      var searchUrl = CRM.url("civicrm/ajax/rest");
      var params = cj.parseJSON('{"sequential": "1"}');
      if( statusUrl == searchUrl ) {
      var data = settings.data;
      var hash;
      var hashes = data.split('&');
      for(var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        params[hash[0]] = hash[1];
      }
      if( (params['entity'] == "Activity") && (params['status_id'] == status) && (params['action'] == "update") ) {
        var response = cj.parseJSON(xhr.responseText);
        var link = cj('a.crm-activity-change-status'),
          activityId = response.id,
          caseId = params['case_id'];
        var date = new Date();
        var dateValue = date.toJSON();
        var dataUrl = CRM.url('civicrm/ajax/rest');
        var data = 'json=1&version=3&entity=Activity&action=update&id=' + activityId + '&activity_date_time=' + dateValue + '&case_id=' + caseId;
        cj.ajax({
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