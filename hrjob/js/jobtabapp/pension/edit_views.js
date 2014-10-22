// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Pension', function(Pension, HRApp, Backbone, Marionette, $, _){
  Pension.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-pension-template',
    templateHelpers: function() {
      return {
        'isNew': this.model.get('id') ? false : true,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobPension
      };
    },
    onRender: function(){
      var entityID = this.model.get('job_id');
      var fileurl = CRM.url('civicrm/hrjob/file/display');
      $.ajax({ type: "POST",
        url: fileurl,
        async: false,
        data: { entityID: entityID, entityTable: "civicrm_hrjob" }
      }).done(function( msg ) {
        $('<div class=evidence-file-display>'+msg+'</div>').insertBefore('#evidence_file');
      });
    },
    onValidateRulesCreate: function(view, r) {
      _.extend(r.rules, {
        er_contrib_pct: {
          number: true,
          range: [0, 100]
        },
        ee_contrib_pct: {
          number: true,
          range: [0, 100]
        },
        ee_contrib_abs: {
          number: true,
        } 
      });
    },   
    doSave: function(){
      var view = this;
      var entityID = this.model.get('job_id');

      if (!this.$('form').valid() || !view.model.isValid()) {
        return false;
      }
      HRApp.trigger('ui:block', ts('Saving'));
      if (this.$('form input[type=file]') ) {
        var formfile = this.$('form');
        var filedata = new FormData(formfile[0]);
        var fileurl = CRM.url('civicrm/hrjob/file/upload');
        filedata.append("entityID", entityID);
        filedata.append("entityTable", "civicrm_hrjob");
        $.ajax({ type: "POST",
          url: fileurl,
          data: filedata,
          processData:false,
          contentType: false,
          async: false,
          success: function() {}
        });
      }
      this.model.save({}, {
        success: function() {
          HRApp.trigger('ui:unblock');
          CRM.alert(ts('Saved'), null, 'success');
          view.modelBackup = view.model.toJSON();
          view.render();
          view.triggerMethod('standard:save', view, view.model);
        },
        error: function() {
          HRApp.trigger('ui:block', ts('Error while saving. Please reload and retry.'));
        }
      });
      return false;
    },
    events: {
      'click .standard-save': 'doSave',
      'click .standard-reset': 'doReset',
      'click .file-delete > a': 'doDelete',
    },
    doDelete: function() {
      var view = this;
      var entityID = this.model.get('job_id');
      var fileID = $('.file-delete a').attr('id').split('_');
      var fileurl = CRM.url('civicrm/hrjob/file/delete');

      $.ajax({ type: "POST",
        url: fileurl,
        async: false,
        data: { entityID : entityID, fileID : fileID[1], entityTable: "civicrm_hrjob" },
        success: function(html) {
          $('#del_'+fileID[1]).remove();
            var successMsg = ts('The selected attachment has been deleted.');
            CRM.alert(successMsg, ts('Removed'), 'success');
        }
      });
    }
  });
});
