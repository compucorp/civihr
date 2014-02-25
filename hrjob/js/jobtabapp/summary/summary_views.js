// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
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
      fundingRegion: '.hrjob-summary-funding',
      healthRegion: '.hrjob-summary-health',
      lifeRegion: '.hrjob-summary-life',
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
      this.fundingRegion.show(new HRApp.JobTabApp.Funding.SummaryView({
          model: models.HRJob.first()
      }));
      this.healthRegion.show(new HRApp.JobTabApp.Health.SummaryView({
        template: '#hrjob-health-summary-template',
        crmEntityName: 'HRJobHealth',
        model: models.HRJobHealth.first() || new HRApp.Entities.HRJobHealth()
      }));
      this.lifeRegion.show(new HRApp.JobTabApp.Health.SummaryView({
        template: '#hrjob-life-summary-template',
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
      this.payRegion.show(new HRApp.JobTabApp.Pay.ShowView({
        model: models.HRJobPay.first() || new HRApp.Entities.HRJobPay()
      }));
      this.pensionRegion.show(new Summary.SimpleItemView({
        template: '#hrjob-pension-summary-template',
        crmEntityName: 'HRJobPension',
        model: models.HRJobPension.first() || new HRApp.Entities.HRJobPension()
      }));
      this.roleRegion.show(new HRApp.JobTabApp.Role.SummaryTableView({
        collection: models.HRJobRole
      }));
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
