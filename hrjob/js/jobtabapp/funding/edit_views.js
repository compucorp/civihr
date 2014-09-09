// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Funding', function(Funding, HRApp, Backbone, Marionette, $, _) {
  Funding.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-funding-template',
    templateHelpers: function() {
      return {
        'isNew': this.model.get('id') ? false : true,
        'isNewDuplicate': this.model._isDuplicate ? true : false,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJob,
        'rolesInfo': this.roleDataView()
      };
    },
    initialize: function() {
      HRApp.Common.Views.StandardForm.prototype.initialize.apply(this, arguments);
      this.listenTo(this.options.collection, 'sync', this.toggleIsPrimary);
    },
    onRender: function() {
      HRApp.Common.Views.StandardForm.prototype.onRender.apply(this, arguments);
      this.roleDataView();
    },
    modelEvents: {
      'change:funder': 'roleDataView'
    },
    roleDataView: function() {
      var view = this, rolesInfo = {};
      _.forEach(view.options.roleCollection.models, function (model) {
        var id = model.get('id'),
          funderExpr = model.get('funder'),
          funders = null;
        if (funderExpr) {
          funders = funderExpr.split(',');
        }
        if(!rolesInfo[id]) {
          rolesInfo[id]={
            'position': model.get('title'),
            'funder': funders,
            'percentPay': model.get('percent_pay_role')
          };
        }
        _.each(funders, function(funderId){
          view.$('a#hrjob-role-funder-'+funderId).hrContactLink({
            cid: funderId
          });
        });
      });
      return rolesInfo;
    }
    /* HR-395 -- remove is tied to funding field
    onBindingCreate: function(bindings) {
      bindings.is_tied_to_funding = {
        selector: 'input[name=is_tied_to_funding]',
        converter: HRApp.Common.convertCheckbox
      };
    }
    */
  });
});
