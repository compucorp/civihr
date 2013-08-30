CRM.HRApp.module('RenderUtil', function(RenderUtil, HRApp, Backbone, Marionette, $, _){
  CRM.HRApp.on("initialize:before", function(){
    RenderUtil._select = _.template($('#renderutil-select-template').html());
    RenderUtil.select = function(args) {
      var defaults = {
        selected: null
      };
      return RenderUtil._select(_.extend(defaults, args));
    };
    RenderUtil.standardButtons = _.template($('#renderutil-standardButtons-template').html());
    RenderUtil.required = _.template($('#common-required-template').html());
    RenderUtil.toggle = _.template($('#renderutil-toggle-template').html());
  });
});