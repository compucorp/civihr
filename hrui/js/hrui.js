// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
// js to hide current employer and job title from contact view screen
cj(document).ready(function($) {
  cj('.crm-contact-current_employer').parent('div.crm-summary-row').hide();
  cj('.crm-contact-job_title').parent('div.crm-summary-row').hide();
 
  //rename "Summary" tab to "Personal Details"
  $('#tab_summary a').text('Personal Details');  
});
// for inline edit
cj(document).ajaxSuccess(function(event, xhr, settings) {
  cj('#current_employer').parent('div.crm-content').parent('div.crm-summary-row').hide();
  cj('#job_title').parent('div.crm-content').parent('div.crm-summary-row').children('div.crm-label').children('label[for="job_title"]').parent().hide();
  cj('#job_title').parent('div.crm-content').hide();;
  
  // Call contactImLink function if url has class name CRM_Contact_Page_Inline_IM
  if(settings.url.search('class_name=CRM_Contact_Page_Inline_IM')>0) {
    contactImLink();
  }
});
// for contact edit screen
cj(document).ready(function($) {
    cj('#current_employer').parent('td').children().remove();
    cj('#job_title').parent('td').hide();
    // Call contactImLink function on page load
    contactImLink();
});

function contactImLink() {
  // build array for IM and its protocol
  var params = {'Yahoo':'ymsgr:sendIM?','Skype':'skype:','Gtalk':'gtalk:chat?','AIM':'aim:goim?screenname=','Jabber':'xmpp:','MSN':'msnim:chat?contact='};
  var i =0;
  cj("#crm-im-content .crm-summary-row").each(function() {
    if (cj('#crm-im-content .crm-summary-row:eq('+i+')')) {
      // get providerlabels
      var providerLabel = cj('#crm-im-content .crm-summary-row:eq('+i+')').find('.crm-label').text();
      // get IM address
      var imName = cj('#crm-im-content .crm-summary-row:eq('+i+')').find('.crm-contact_im').text();
      var providerName = providerLabel.substr(0,providerLabel.match(/\s[(]/).index);
      // build links of IM address
      clickableIM = '<a href="'+ params[providerName] +''+ imName +'">'+ imName + '</a>';
      cj('#crm-im-content .crm-summary-row:eq('+i+')').find('.crm-contact_im').html(clickableIM);
      i++;
    }
  });
}
