/* eslint-env amd */

define(function () {
  'use strict';

  filtersService.__name = 'filtersService';

  function filtersService () {
    return {
      /**
       * Filter items which are different than undefined
       *
       * @param  {array} array
       * @return {array}
       */
      isNotUndefined: function (array) {
        try {
          if (array.constructor === Array) {
            return array.filter(function (value) {
              return (value !== 'undefined' && value !== undefined);
            });
          } else {
            return array;
          }
        } catch (e) {
          return array;
        }
      },

      /**
       * Filter cost centers which have all required data
       *
       * @param  {array} roles
       * @return {array}
       */
      issetCostCentre: function (roles) {
        try {
          if (roles.constructor === Array) {
            return roles.filter(function (role) {
              return (
                role.cost_centre_id !== '' &&
                ((role.type === '1' && parseInt(role.percentage) > 0) ||
                (role.type === '0' && parseInt(role.amount) > 0))
              );
            });
          } else {
            return roles;
          }
        } catch (e) {
          return roles;
        }
      },

      /**
       * Filter funders which have all required data
       *
       * @param  {array} roles
       * @return {array}
       */
      issetFunder: function (roles) {
        try {
          if (roles.constructor === Array) {
            return roles.filter(function (role) {
              return (
                role.funder_id !== '' &&
                ((role.type === '1' && parseInt(role.percentage) > 0) ||
                (role.type === '0' && parseInt(role.amount) > 0))
              );
            });
          } else {
            return roles;
          }
        } catch (e) {
          return roles;
        }
      }
    };
  }

  return filtersService;
});
