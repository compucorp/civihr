var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * [selectTab description]
     * @return {[type]} [description]
     */
    selectTab: function (tabName) {
      var casper = this.casper;

      casper.then(function () {
        casper.click('div.chr_leave-request-modal__tab li[heading=' + tabName + '] a');
      });

      return this;
    },

    /**
     * [addCommentToScope description]
     * @return {[type]} [description]
     */
    addCommentToScope: function (comment) {
      var casper = this.casper;

      casper.then(function () {
        casper.evaluate(function (commentParam) {
          angular.element(".modal").scope().$ctrl.comment.text = commentParam;
          angular.element(".modal").scope().$apply();
        }, comment);
      });

      return this;
    },

    /**
     * [addComment description]
     * @return {[type]} [description]
     */
    addComment: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('button[ng-click="$ctrl.addComment()"]');
      });

      return this;
    }
  });
})();
