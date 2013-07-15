//js to make job tab as default and move job tab to the beginning
cj(document).ready(function($) {
  //make "job" tab as default
  var tabIndex = $('#tab_hrjob').prevAll().length;
  $("#mainTabContainer").tabs({ selected: tabIndex});

  //move job tab to the beginning
  var jobTab = $("div#mainTabContainer ul li#tab_hrjob");
  jobTab.prependTo(jobTab.parent());
});