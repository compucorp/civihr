CRM.HRApp.module('JobTabApp.General', function(General, HRApp, Backbone, Marionette, $, _) {
  General.SummaryView = Marionette.ItemView.extend({
    template: '#hrjob-general-summary-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJob
      };
    },
    modelEvents: {
      'change:manager_contact_id': 'renderManagerContact'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.renderManagerContact();
    },
    renderManagerContact: function() {
      var view = this;
      var cid = this.model.get('manager_contact_id');
      if (cid) {
        var contactLookupUrl = CRM.url('civicrm/ajax/rest', 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1');
        var viewContactUrl = CRM.url('civicrm/contact/view', {reset:1, cid: cid});
        $.ajax({
          url     : contactLookupUrl + '&id=' + cid,
          async   : false,
          success : function(html){
            var htmlText = html.split( '|' , 2);
            view.$('.manager_contact')
              .attr('href', viewContactUrl)
              .text(htmlText[0]);
          }
        });
      } else {
        this.$('.manager_contact').text('');
      }
    }
  });

});