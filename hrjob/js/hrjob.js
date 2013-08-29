//js to make job tab as default and move job tab to the beginning
cj(document).ready(function($) {
  //make "job" tab as default in case selectedChild is not set
  var selectedTab = CRM.tabs.selectedChild ? CRM.tabs.selectedChild : 'hrjob';
  var tabIndex = $('#tab_' + selectedTab).prevAll().length;
  $("#mainTabContainer").tabs({ selected: tabIndex});

  //move job tab to the beginning
  var jobTab = $("div#mainTabContainer ul li#tab_hrjob");
  jobTab.prependTo(jobTab.parent());
});

function updateJobTitle() {
   if(cj("#hrjob-title").val() === cj("#hrjob-position").val()) {
	    cj("#hrjob-position").bind("keyup", function() {
	  	  cj("#hrjob-title").val(cj(this).val());
	    });
	    cj("#hrjob-title").bind("keyup", function() {
	    	  cj("#hrjob-position").unbind("keyup");
	    });
   }
}