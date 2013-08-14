CRM.HRApp.module('JobTabApp.Summary', function(Summary, HRApp, Backbone, Marionette, $, _) {
  Summary.ShowView = Marionette.Layout.extend({
    template: '#hrjob-summary-template',
    templateHelpers: function() {
      return {
        RenderUtil: CRM.HRApp.RenderUtil,
        FieldOptions: CRM.FieldOptions.HRJob
      };
    },
    regions: {
      generalRegion: '.hrjob-summary-general',
      healthRegion: '.hrjob-summary-health',
      hourRegion: '.hrjob-summary-hour',
      leaveRegion: '.hrjob-summary-leave',
      payRegion: '.hrjob-summary-pay',
      pensionRegion: '.hrjob-summary-pension',
      roleRegion: '.hrjob-summary-role'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      var models = this.options.models;
      this.generalRegion.show(new HRApp.JobTabApp.General.SummaryView({
        model: models.HRJob.first()
      }));
      this.healthRegion.show(new HRApp.JobTabApp.Health.SummaryView({
        model: models.HRJobHealth.first() || new HRApp.Entities.HRJobHealth()
      }));
      this.hourRegion.show(new HRApp.JobTabApp.Hour.SummaryView({
        model: models.HRJobHour.first() || new HRApp.Entities.HRJobHour()
      }));
      this.payRegion.show(new HRApp.JobTabApp.Pay.SummaryView({
        model: models.HRJobPay.first() || new HRApp.Entities.HRJobPay()
      }));
      /*
       this.roleRegion.show(new HRApp.JobTabApp.Role.TableView({
       newModelDefaults: {
       job_id: 123, // FIXME jobId,
       title: ts('New Role')
       },
       collection: models.HRJobRole
       }));
       */
    }
  });
});
