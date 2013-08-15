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
      this.healthRegion.show(new Summary.SimpleItemView({
        template: '#hrjob-health-summary-template',
        crmEntityName: 'HRJobHealth',
        model: models.HRJobHealth.first() || new HRApp.Entities.HRJobHealth()
      }));
      this.hourRegion.show(new Summary.SimpleItemView({
        template: '#hrjob-hour-summary-template',
        crmEntityName: 'HRJobHour',
        model: models.HRJobHour.first() || new HRApp.Entities.HRJobHour()
      }));
      this.leaveRegion.show(new HRApp.JobTabApp.Leave.SummaryView({
        collection: models.HRJobLeave
      }));
      this.payRegion.show(new Summary.SimpleItemView({
        template: '#hrjob-pay-summary-template',
        crmEntityName: 'HRJobPay',
        model: models.HRJobPay.first() || new HRApp.Entities.HRJobPay()
      }));
      this.pensionRegion.show(new Summary.SimpleItemView({
        template: '#hrjob-pension-summary-template',
        crmEntityName: 'HRJobPension',
        model: models.HRJobPension.first() || new HRApp.Entities.HRJobPension()
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

  /**
   * A simple view for templates which display fields of a CRM entity.
   *
   * If you need something more sophisticated, then considering making a
   * new view class.
   *
   * Required options:
   *  - template: string, CSS selector
   *  - crmEntityName: string, APIv3 entity name
   *
   * @type {*}
   */
  Summary.SimpleItemView = Marionette.ItemView.extend({
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions[this.options.crmEntityName]
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    }
  });

});
