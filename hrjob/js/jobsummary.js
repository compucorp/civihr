// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing

cj(document).ready(function($) {
  var gid = CRM.grID;
  var joinDate = $('#custom-set-content-'+gid+' .crm-inline-block-content div:nth-child(2) .crm-custom-data').html();
  if(joinDate) {
    var duration = lengthEmployment(joinDate);
    var length = "<div class='crm-summary-row' id='initial_join_date'><div class='crm-label'>Length Of Employment</div><div class='crm-content crm-custom-data lengthEmployment'></div></div>";
    $('#custom-set-content-'+gid+' .crm-inline-block-content').append(length);
    $('.lengthEmployment').html(duration);
  }
  $(document).on("click", "#_qf_CustomData_upload", function() {
    $(document).ajaxSuccess(function(data, textStatus, jqXHR) {
      if(jqXHR.extraData) {
        if (jqXHR.extraData.class_name == 'CRM_Contact_Form_Inline_CustomData' && jqXHR.extraData.groupID == gid) {
          setTimeout(function(){
            $('#initial_join_date').remove();
	    var length = "<div class='crm-summary-row' id='initial_join_date'><div class='crm-label'>Length Of Employment</div><div class='crm-content crm-custom-data lengthEmployment'></div></div>";
	    var joinDate = $('#custom-set-content-'+gid+' .crm-inline-block-content div:nth-child(2) .crm-custom-data').html();
	    var duration = lengthEmployment(joinDate);
	    $('#custom-set-content-'+gid+' .crm-inline-block-content').append(length);
	    $('.lengthEmployment').html(duration);
          },300 );
        }
      }
    });
  });
});

function lengthEmployment(joinDate) {
  var join_date = moment(joinDate,"MMMM DD, YYYY");
  if (moment().diff(join_date,'months') == 0) {
    duration = moment().diff(join_date,'days')+' days';
  }
  else {
    duration = moment().diff(join_date,'months')+' months';
  }
  return(duration);
}