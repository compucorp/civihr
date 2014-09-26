// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function ($, _) {
  $(document).on('crmLoad', function() {
    //change heading consisting 'Case' replacing with 'Assignment'
    $('.crm-report-instanceList-form-block .crm-accordion_Case-accordion .crm-accordion-header').html('Assignment Reports');
    $('.crm-report-templateList-form-block .crm-accordion_Case-accordion .crm-accordion-header').html('Assignment Report Templates');
    //change templates name consisting 'Case' replacing with 'Assignment'
    $('#Case #row_1 .crm-report-templateList-title a strong, #Case #row_10 .crm-report-templateList-title a strong').html('Assignment Summary Report');
    $('#Case #row_2 .crm-report-templateList-title a strong, #Case #row_11 .crm-report-templateList-title a strong').html('Assignment Time Spent Report');
    $('#Case #row_4 .crm-report-templateList-title a strong, #Case #row_13 .crm-report-templateList-title a strong').html('Assignment Detail Report');
    //change description text
    $('#Case #row_1 .crm-report-templateList-description').html('Provides a summary of assignments and their duration by date range, status, staff member and / or assignment role.');
    $('#Case #row_2 .crm-report-templateList-description').html('Aggregates time spent on assignment and / or non-assignment activities by activity type and contact.');
    $('#Case #row_3 .crm-report-templateList-description').html('Demographic breakdown for assignment contacts (and or non-assignment contacts) in your database. Includes custom contact fields.');
    $('#Case #row_4 .crm-report-templateList-description').html('Assignment Details');
    //change text on anchor link
    $('.icon').parent('span:contains("New Case Report")').html('<div class="icon add-icon"></div>New Assignment Report');
    //change information text
    var $textChangeEle = $("span:contains('New Assignment Report')").closest('div.action-link').next('.crm-content-block').children('.messages');
    if ($textChangeEle.length > 0) {
      $textChangeEle.html($textChangeEle.html().replace('Case','Assignment'));
    }
  });
}(CRM.$, CRM._));
