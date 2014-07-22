// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
//js to make job tab as default and move job tab to the beginning
(function ($, _) {
  $(document).on('crmLoad', function() {
    //move job tab to the beginning
    var jobTab = $("div#mainTabContainer ul li#tab_hrjob");
    jobTab.prependTo(jobTab.parent());

    //make "job" tab as default in case selectedChild is not set
    var selectedTab = CRM.tabs.selectedChild ? CRM.tabs.selectedChild : 'hrjob';
    var tabIndex = $('#tab_' + selectedTab).prevAll().length;
    $("#mainTabContainer").tabs({ selected: tabIndex});
  });
}(CRM.$, CRM._));
