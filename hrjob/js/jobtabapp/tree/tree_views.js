CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _){
  Tree.View = Marionette.ItemView.extend({
    template: '#hrjob-tree-template',
    initialize: function() {
      // TODO this.listenTo(this.options.jobCollection, 'add', this.render);
      // TODO this.listenTo(this.options.jobCollection, 'remove', this.render);
    },
    onRender: function() {
      var self = this;
      var jsTreeData = [];
      this.options.jobCollection.each(function(jobModel) {
        jsTreeData.push(self.createJsonNode(jobModel));
      });
      this.$el.jstree({
        'plugins': ['themes', 'json_data', 'ui', 'search'],
        'themes': {
          "theme": 'classic',
          "dots": false,
          "icons": false,
          "url": CRM.config.resourceBase + 'packages/jquery/plugins/jstree/themes/classic/style.css'
        },
        'json_data': {
          data: jsTreeData
        }
      })
      .bind("select_node.jstree", function (event, data) {
        console.log(data);
      });
    },

    /**
     *
     * @param title string
     * @return {Object} JSTree json node
     */
    createJsonNode: function(jobModel) {
      return {
        data: jobModel.get('position'),
        state: 'open',
        children: [
          {data: 'Hours'},
          {data: 'Pay'},
          {data: 'Pension'}
        ]
      };
    }
  });
});
