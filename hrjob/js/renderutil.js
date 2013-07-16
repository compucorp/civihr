CRM.HRApp.module('RenderUtil', function(RenderUtil, HRApp, Backbone, Marionette, $, _){
  CRM.HRApp.on("initialize:before", function(){
    RenderUtil._select = _.template(cj('#renderutil-select-template').text());
    RenderUtil.select = function(args) {
      var defaults = {
        selected: null
      };
      return RenderUtil._select(_.extend(defaults, args));
    };
  });
});