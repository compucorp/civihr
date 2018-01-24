/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/services'
], function (_, module) {
  'use strict';

  module.factory('beforeHashQueryParams', ['$log', '$window',
    function ($log, $window) {
      $log.debug('Service: beforeHashQueryParams');

      return {
        parse: parse
      };

      /**
       * Parses url into list of query strings
       *
       * @param {String} url
       */
      function parse (url) {
        var urlToParse = url || $window.location.href;
        var cleanUrl = getUrlBeforeHash(urlToParse);

        if (cleanUrl.indexOf('?') === -1) {
          return {};
        }

        return prepareQueryParamsObject(getQueryParams(cleanUrl));
      }

      /**
       * Prepare Query params object
       *
       * @param {Array} queryStrings
       * @return {Object}
       */
      function prepareQueryParamsObject (queryStrings) {
        if (!queryStrings.length) {
          return {};
        }

        return _.transform(queryStrings, function (result, qString) {
          var splitQueryString = qString.split('=');

          if (splitQueryString[0]) {
            result[splitQueryString[0]] = splitQueryString[1] ? decodeURI(splitQueryString[1]) : null || null;
          }
        }, {});
      }

      /**
       * Gets the url before hash
       *
       * @param {String} urlString
       * @return {String}
       */
      function getUrlBeforeHash (urlString) {
        var hashPos = urlString.indexOf('#');

        return urlString.substring(0, hashPos !== -1 ? hashPos : urlString.length);
      }

      /**
       * Returns list of query params form the url
       *
       * @param {String} urlBeforeHash
       * @return {Array}
       */
      function getQueryParams (urlBeforeHash) {
        var query = urlBeforeHash.indexOf('?');

        return urlBeforeHash.substr(query + 1).split('&');
      }
    }
  ]);
});
