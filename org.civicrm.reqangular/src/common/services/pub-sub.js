define([
  'common/modules/services'
], function (services) {
  'use strict';

  var topics = Object.create(null);

  /**
   * This service is used for communication between different angular apps using pub-sub pattern
   *
   * @ngdoc service
   * @name pubSub
   * @requires $rootScope
   */
  services.factory('pubSub', ['$rootScope', function ($rootScope) {
    return {
      /**
       * Subscribe a given listener callback to a specific topic
       *
       * @param {string} topic
       * @param {Function} listener
       * @return {Object} an object that provides the ability to cancel the subscription
       */
      subscribe: function (topic, listener) {
        var index;
        topics[topic] = topics[topic] || [];
        listener.$rootScope = $rootScope;
        index = topics[topic].push(listener) - 1;

        return {
          remove: function () {
            delete topics[topic][index];
          }
        };
      },

      /**
       * Publish a specific topic along with Data
       *
       * @param {string} topic
       * @param {*} data Any value that will be sent for the subscriber to be fetched
       */
      publish: function (topic, data) {
        if (!topics[topic]) {
          return;
        }
        data = data != undefined ? data : {};

        topics[topic].forEach(function (listener) {
          listener.$rootScope.$applyAsync(function () {
            listener(data);
          });
        });
      }
    };
  }
  ]);
});
