CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _){
  Tree.View = Marionette.ItemView.extend({
    template: '#hrjob-tree-template',
    onRender: function() {
      var jsTreeData = {
        data: "Jobs",
        state: 'open',
        children: [
          {
            data: 'Break Dancer',
            state: 'open',
            children: [
              {data: 'Hours'},
              {data: 'Pay'},
              {data: 'Pension'}
            ]
          },
          {
            data: 'Violinist',
            state: 'open',
            children: [
              {data: 'Hours'},
              {data: 'Pay'},
              {data: 'Pension'}
            ]
          }
        ]
      };
      this.$el.jstree({
        'json_data': {
          data: jsTreeData
        },
        'themes': {
          "theme": 'classic',
          "dots": false,
          "icons": false,
          "url": CRM.config.resourceBase + 'packages/jquery/plugins/jstree/themes/classic/style.css'
        },
        'plugins': ['themes', 'json_data', 'ui', 'search']
      });
    }
  });
});
