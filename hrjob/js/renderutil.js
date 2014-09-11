// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('RenderUtil', function(RenderUtil, HRApp, Backbone, Marionette, $, _){
  CRM.HRApp.on("initialize:before", function(){
    RenderUtil._select = _.template($('#renderutil-select-template').html());
    RenderUtil.select = function(args) {
      var settings = {
        selected: null,
        entity: null,
        field: args.field || args.name
      };
      _.extend(settings, args);
      settings.options = args.options || CRM.FieldOptions[args.entity][settings.field];
      return RenderUtil._select(settings);
    };
    RenderUtil.standardButtons = _.template($('#renderutil-standardButtons-template').html());
    RenderUtil.required = _.template($('#common-required-template').html());
    RenderUtil.toggle = _.template($('#renderutil-toggle-template').html());
    RenderUtil.funder = _.template($('#renderutil-funder-template').html());
  });
});
