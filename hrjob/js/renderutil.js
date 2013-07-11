CRM.HRApp.module('RenderUtil', function(RenderUtil, HRApp, Backbone, Marionette, $, _){
  CRM.HRApp.on("initialize:before", function(){
    RenderUtil.select = _.template(cj('#renderutil-select-template').text());
  });
});