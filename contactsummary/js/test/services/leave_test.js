/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'common/angularMocks',
  'contact-summary/app',
  'contact-summary/services/leave',
  'mocks/constants',
  'mocks/services'
], function (angular, _, moment) {
  'use strict';

  describe('LeaveService', function () {
    var LeaveService,
      ApiServiceMock, ModelServiceMock, ContactDetailsServiceMock,
      rootScope;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.factory('ApiService', function () {
        return ApiServiceMock;
      });

      $provide.factory('ModelService', function () {
        return ModelServiceMock;
      });

      $provide.factory('ContactDetailsService', function () {
        return ContactDetailsServiceMock;
      });
    }));

    beforeEach(inject(function ($injector) {
      ApiServiceMock = $injector.get('ApiServiceMock');
      ModelServiceMock = $injector.get('ModelServiceMock');
      ContactDetailsServiceMock = $injector.get('ContactDetailsServiceMock');
      rootScope = $injector.get('$rootScope');
    }));

    beforeEach(inject(function (_LeaveService_) {
      LeaveService = _LeaveService_;
    }));

    describe('get()', function () {
      var leaves;
      var expectedAbsenceTypes = {
        values: [
          {debit_activity_type_id: '52', id: '1', is_active: '1', title: 'Sick'},
          {debit_activity_type_id: '53', id: '2', is_active: '1', title: 'Annual Leave'},
          {debit_activity_type_id: '54', id: '3', is_active: '1', title: 'Maternity'},
          {debit_activity_type_id: '55', id: '4', is_active: '1', title: 'Paternity'},
          {credit_activity_type_id: '57', debit_activity_type_id: '56', id: '5', is_active: '1', title: 'TOIL'}
        ]
      };
      var expectedAbsences = {
        values: {
          625: {absence_range: {approved_duration: 1440, duration: 960}, activity_type_id: '52'},
          626: {absence_range: {approved_duration: 10080, duration: 10080}, activity_type_id: '53'},
          661: {absence_range: {approved_duration: 31200, duration: 31200}, activity_type_id: '54'},
          753: {absence_range: {approved_duration: 1440, duration: 2400}, activity_type_id: '53'},
          760: {absence_range: {approved_duration: 480, duration: 480}, activity_type_id: '56'}
        }
      };
      var expectedEntitlement = {
        values: [
          {amount: '11', id: '73', period_id: '1', type_id: '1'},
          {amount: '40', id: '74', period_id: '1', type_id: '2'},
          {amount: '15', id: '75', period_id: '1', type_id: '3'},
          {amount: '0', id: '76', period_id: '1', type_id: '4'},
          {amount: '15', id: '77', period_id: '1', type_id: '5'},
          {amount: '0', id: '78', period_id: '1', type_id: '6'}
        ]
      };

      beforeEach(function () {
        ApiServiceMock.respondGet('HRAbsenceType', expectedAbsenceTypes);
        ApiServiceMock.respondPost('Activity', 'getabsences', expectedAbsences);
        ApiServiceMock.respondGet('HRAbsenceEntitlement', expectedEntitlement);
        ContactDetailsServiceMock.respond('get', {id: 123});

        LeaveService.get().then(function (response) {
          leaves = response;
        });

        rootScope.$digest();

        ApiServiceMock.flush();
        ContactDetailsServiceMock.flush();
      });

      it('should return leaves', function () {
        expect(angular.isObject(leaves)).toBe(true);
      });

      describe('a leave', function () {
        it('should have the required fields', function () {
          expect(_.size(leaves)).toBeGreaterThan(0);

          angular.forEach(leaves, function (leave) {
            expect(leave.title).toBeDefined();
            expect(leave.entitled).toBeDefined();
            expect(leave.taken).toBeDefined();
            expect(leave.credit_activity_type_id).toBeDefined();
            expect(leave.debit_activity_type_id).toBeDefined();
          });
        });
      });
    });
  });
});
