// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
//js to make email addresses clickable in search results
CRM.$(function($) {
  if ($('tr:has(td.crm-email)').length > 0) {
    $(".crm-search-results td.crm-email").each(function() {
      if ($(this).text()) {
        var email = $(this).text();
        var clickableEmail = '<a href="mailto:' + email + '">' + email + '</a>';
        $(this).html(clickableEmail);
      }
    });
  }
});