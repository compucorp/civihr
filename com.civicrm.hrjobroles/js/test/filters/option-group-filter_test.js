define([
    'common/angular',
    'common/angularMocks',
    'job-roles/filters/option-group-filter',
    'job-roles/filters/filters',
], function(angular, moment) {
  'use strict';

  describe('Using getActiveGroup filter', function() {
    var $filter, locations, getActiveGroup;

    beforeEach(module('hrjobroles.filters'));
    beforeEach(inject(['$filter', function(_$filter_) {
        $filter = _$filter_;
        getActiveGroup = $filter('getActiveGroup');
      }
    ]));

    it('should define getActiveGroup filter ', function() {
      expect($filter('getActiveGroup')).toBeDefined();
    });

    describe('When filtering locations, departments, region and lavels ', function() {
      beforeEach(function() {
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

      it('should have 2 locations after filtration ', function() {
        expect(getActiveGroup(locations, '1').length).toBe(2);
      });

      it('should have all data with is_active set to 1 ', function() {
        expect(getActiveGroup(locations, '1')[0].is_active).toBe('1');
        expect(getActiveGroup(locations, '1')[1].is_active).toBe('1');
      });

      it('should have filtered correc locations ', function() {
        expect(getActiveGroup(locations, '1')[0].title).toBe('Headquarters');
        expect(getActiveGroup(locations, '1')[1].title).toBe('Home or Home-Office');
      });
    });
  });
});
