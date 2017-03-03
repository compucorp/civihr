define([
  'common/angular',
  'common/angularMocks',
  'job-roles/filters/option-values-filter',
], function(angular, moment) {
  'use strict';

  describe('getActiveValues', function () {
    var $filter, locations, getActiveValues;

    beforeEach(module('hrjobroles.filters'));
    beforeEach(inject(['$filter', function (_$filter_) {
      $filter = _$filter_;
      getActiveValues = $filter('getActiveValues');
    }]));

    it('filter is defined', function () {
      expect(getActiveValues).toBeDefined();
    });

    describe('when filtering locations, departments, region and lavels ', function () {
      beforeEach(function () {
        locations = {
          "Headquarters": {
            "id": "35",
            "title": "Headquarters",
            "is_active": "1"
          },
          "Home": {
            "id": "36",
            "title": "Home or Home-Office",
            "is_active": "1"
          },
          "Other": {
            "id": "37",
            "title": "Home or Home-Office",
            "is_active": "0"
          }
        }
      });

      it('returns 2 values', function () {
        expect(Object.keys(getActiveValues(locations)).length).toBe(2);
      });

      it('returns all active values having is_active set to 1', function () {
        expect(getActiveValues(locations)['Headquarters'].is_active).toBe('1');
        expect(getActiveValues(locations)['Home'].is_active).toBe('1');
      });

      it('returns correct location values', function () {
        expect(getActiveValues(locations)['Headquarters'].title).toBe('Headquarters');
        expect(getActiveValues(locations)['Home'].title).toBe('Home or Home-Office');
      });
    });
  });
});
