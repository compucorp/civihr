/* eslint-env amd */

define([
  'common/modules/services'
], function (module) {
  'use strict';

  module.factory('$beforeHashQueryParams', ['$log', '$window',
    function ($log, $window) {
      $log.debug('Service: $beforeHashQueryParams');

      return {
        parse: parse
      };

      /**
       * Parses url into list of query strings
       *
       * @param {String} url
       */
      function parse (url) {
        var obj = {};
        var urlToParse = url || $window.location.href;
        var hashPos = urlToParse.indexOf('#/');
        var cleanUrl = urlToParse.substring(0, hashPos !== -1 ? hashPos : urlToParse.length);
        var query = urlToParse.indexOf('?');
        var tokens = cleanUrl.substr(query + 1).split('&');
        var tokensLength = tokens.length;

        if (query === -1 || cleanUrl.indexOf('=') === -1) {
          return obj;
        }

        for (var i = 0; i < tokensLength; i++) {
          var splittedToken = tokens[i].split('=');

          if (splittedToken[0]) {
            obj[decodeURI(splittedToken[0])] = splittedToken.hasOwnProperty(1)
              ? decodeURI(splittedToken[1])
              : null;
          }
        }

        return obj;
      }
    }
  ]);
});
