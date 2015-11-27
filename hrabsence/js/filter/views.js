// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('Filter', function(Filter, HRAbsenceApp, Backbone, Marionette, $, _) {
  Filter.FilterView = Marionette.ItemView.extend({
    template: '#hrabsence-filters-template',
    templateHelpers: function() {
	i=1;
      return {
        // 'RenderUtil': CRM.HRAbsenceApp.RenderUtil,
        'FieldOptions': {
          'activity_type_id': CRM.absenceApp.activityTypes,
	      'sort_periods': _.reduce(CRM.absenceApp.sortPeriods, function(r,m){r[i]= m.id; i++; return r;}, {}),
          'period_id': _.reduce(CRM.absenceApp.periods, function(r,m){r[m.id]= m.title; return r;}, {})
        }
      };
    },
    initialize: function(options) {
      this.cache.contactId = options.model.attributes.target_contact_id
    },
    events: {
      "change [name=activity_type_id]": function(e) {
        if ($(e.currentTarget).val()) {
          this.cache.setValue('activity_type_id', $(e.currentTarget).val());
          this.model.set('activity_type_id', $(e.currentTarget).val());
        } else {
          this.cache.clearValue('activity_type_id');
          this.model.unset('activity_type_id');
        }
      },
      "change [name=period_id]": function(e) {
        if ($(e.currentTarget).val()) {
          this.cache.setValue('period_id', $(e.currentTarget).val());
          this.model.set('period_id', $(e.currentTarget).val());
        } else {
          this.cache.clearValue('period_id');
          this.model.unset('period_id');
        }
      }

    },
    onRender: function() {
      var activity = this.cache.getValue('activity_type_id');
      var period = this.cache.getValue('period_id');

      if(activity) this.model.set('activity_type_id', activity);
      if(period) this.model.set('period_id', period);

      this.$('[name=activity_type_id]').val(this.model.get('activity_type_id'));

      this.$('[name=period_id]').val(this.model.get('period_id'));
      this.$('[name=activity_type_id]').multiselect({
        minWidth: 250,
        noneSelectedText: ts('(Select type)'),
        selectedText: function(numChecked, numTotal, checkedItems){
          if (numChecked == 1) return $(checkedItems).parent().text();
          return ts("%1 of %2 selected", {1: numChecked, 2: numTotal});
        }
      });

      this.$('[name=period_id]').multiselect({
        minWidth: 300,
        noneSelectedText: ts('(Select period)'),
        selectedText: function(numChecked, numTotal, checkedItems){
          if (numChecked == 1) return $(checkedItems).parent().text();
          return ts("%1 of %2 selected", {1: numChecked, 2: numTotal});
        }
      });
    },
    cache: {
      setValue: function (name, value) {
        window.sessionStorage.setItem(name + '-' + this.contactId, JSON.stringify(value));
      },
      getValue: function (name) {
        var result = window.sessionStorage.getItem(name + '-' + this.contactId);
        return JSON.parse(result);
      },
      clearValue: function (name) {
        window.sessionStorage.removeItem(name + '-' + this.contactId);
      }
    }
  });
});