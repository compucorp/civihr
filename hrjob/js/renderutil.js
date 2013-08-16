CRM.HRApp.module('RenderUtil', function(RenderUtil, HRApp, Backbone, Marionette, $, _){
  CRM.HRApp.on("initialize:before", function(){
    RenderUtil._select = _.template(cj('#renderutil-select-template').text());
    RenderUtil.select = function(args) {
      var defaults = {
        selected: null
      };
      return RenderUtil._select(_.extend(defaults, args));
    };
    RenderUtil.standardButtons = _.template(cj('#renderutil-standardButtons-template').text());
    RenderUtil.required = _.template(cj('#common-required-template').text());
    RenderUtil.toggle = _.template(cj('#renderutil-toggle-template').text());
  });
});