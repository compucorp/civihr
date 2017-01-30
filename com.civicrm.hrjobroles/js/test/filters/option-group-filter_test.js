define([
  'common/angular',
  'common/angularMocks',
  'job-roles/filters/option-group-filter',
], function(angular, moment) {
  'use strict';

  describe('getActiveValues', function () {
    var $filter, locations, getActiveValues;

    beforeEach(module('hrjobroles.filters'));
    beforeEach(inject(['$filter', function (_$filter_) {
      $filter = _$filter_;
      getActiveValues = $filter('getActiveValues');
    }]));

    it('is defined', function () {
      expect($filter('getActiveValues')).toBeDefined();
    });

    describe('when filtering locations, departments, region and lavels ', function () {
      beforeEach(function () {
        locations = {
          "Headquarters": {
            "id": "Headquarters",
            "title": "Headquarters",
            "is_active": "1"
          },
          "Home": {
            "id": "Home",
            "title": "Home or Home-Office",
            "is_active": "1"
          },
          "Other": {
            "id": "Home",
            "title": "Home or Home-Office",
            "is_active": "0"
          }
        }
      });

      it('returns 2 values', function () {
        expect(getActiveValues(locations).length).toBe(2);
      });

      it('returns all active values having is_active set to 1', function () {
        expect(getActiveValues(locations)[0].is_active).toBe('1');
        expect(getActiveValues(locations)[1].is_active).toBe('1');
      });

      it('returns correct location values', function () {
        expect(getActiveValues(locations)[0].title).toBe('Headquarters');
        expect(getActiveValues(locations)[1].title).toBe('Home or Home-Office');
      });
    });
  });
});
