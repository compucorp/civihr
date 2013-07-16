CRM.HRApp.module('JobTabApp.Leave', function(Leave, HRApp, Backbone, Marionette, $, _){
  Leave.Controller = {
    editLeave: function(cid, jobId){
      var model = HRApp.request("hrjob:entity", jobId);
      var leaveModels = [];
      var oddball = 3;
      _.each(CRM.FieldOptions.HRJobLeave.leave_type, function(leaveTypeLabel, leaveTypeValue){
        oddball = oddball + 2;
        leaveModels.push(new HRApp.Entities.HRJobLeave({
          leave_type: leaveTypeValue,
          leave_amount: oddball
        }));
      });
      var leaveCollection = new HRApp.Entities.HRJobLeaveCollection(leaveModels);

      var mainView = new Leave.TableView({
        model: model,
        collection: leaveCollection
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
