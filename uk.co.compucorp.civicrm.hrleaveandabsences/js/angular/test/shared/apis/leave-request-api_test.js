define([
  'mocks/data/leave-request-data',
  'common/moment',
  'leave-absences/shared/apis/leave-request-api',
], function (mockData, moment) {
  'use strict';

  describe('LeaveRequestAPI', function () {
    var LeaveRequestAPI, $httpBackend, $rootScope, $q, dateFormat = 'YYYY-MM-DD';
    var promise, requestData, errorObject;

    beforeEach(module('leave-absences.apis'));

    beforeEach(inject(function (_LeaveRequestAPI_, _$httpBackend_, _$rootScope_, _$q_) {
      LeaveRequestAPI = _LeaveRequestAPI_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;
      $q = _$q_;

      //Intercept backend calls for LeaveRequest.all
      $httpBackend.whenGET(/action\=getFull&entity\=LeaveRequest/)
        .respond(mockData.all());

      //Intercept backend calls for LeaveRequest.balanceChangeByAbsenceType
      $httpBackend.whenGET(/action\=getbalancechangebyabsencetype&entity\=LeaveRequest/)
        .respond(mockData.balanceChangeByAbsenceType());

      //Intercept backend calls for LeaveRequest.calculateBalanceChange
      $httpBackend.whenGET(/action\=calculatebalancechange&entity\=LeaveRequest/)
        .respond(mockData.calculateBalanceChange());

      //Intercept backend calls for LeaveRequest.create in POST
      $httpBackend.whenPOST(/\/civicrm\/ajax\/rest/)
        .respond(function (method, url, data, headers, params) {
          var uriParts = decodeURIComponent(data).split('&');
          var uriEntityAction = {};

          var uriFilter = uriParts.filter(function (item) {
            var itemSplit = item.split('=');
            if (itemSplit[0] === 'entity' || itemSplit[0] === 'action') {
              uriEntityAction[itemSplit[0]] = itemSplit[1];
              return true;
            }
          });

          //'update' is also 'create' call with is set
          if (uriEntityAction.entity === 'LeaveRequest' && uriEntityAction.action === 'create') {
            return mockData.getRandomLeaveRequest();
          }
        });

      //Intercept backend calls for LeaveRequest.isValid
      $httpBackend.whenGET(/action\=isValid&entity\=LeaveRequest/)
        .respond(mockData.getisValid());
    }));

    describe('all()', function () {
      var leaveRequestPromise;

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'getAll').and.callThrough();
        leaveRequestPromise = LeaveRequestAPI.all();
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the getAll() method', function () {
        expect(LeaveRequestAPI.getAll).toHaveBeenCalled();
        expect(LeaveRequestAPI.getAll.calls.mostRecent().args[0]).toBe('LeaveRequest');
        expect(LeaveRequestAPI.getAll.calls.mostRecent().args[5]).toBe('getFull');
      });

      it('returns all the data', function () {
        leaveRequestPromise.then(function (response) {
          expect(response.list).toEqual(mockData.all().values);
        });
      });
    });

    describe('balanceChangeByAbsenceType()', function () {

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
      });

      describe('error handling', function () {

        afterEach(function () {
          $rootScope.$apply();
        });

        function commonExpect(data) {
          expect(data).toEqual({
            is_error: 1,
            error_message: 'contact_id and period_id are mandatory'
          });
        }

        it('throws error if contact_id is blank', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(null, jasmine.any(String))
            .then(commonExpect);
        });

        it('throws error if periodId is blank', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), null)
            .then(commonExpect);
        });
      });

      describe('default values', function () {

        afterEach(function () {
          $httpBackend.flush();
        });

        it('status and publicHoliday has default values if falsy values has been passed', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String));

          expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', {
            contact_id: jasmine.any(String),
            period_id: jasmine.any(String),
            statuses: null,
            public_holiday: false
          });
        });

        it('status and publicHoliday has original values if truthy values has been passed', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String), jasmine.any(Array), true);

          expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', {
            contact_id: jasmine.any(String),
            period_id: jasmine.any(String),
            statuses: jasmine.any(Array),
            public_holiday: true
          });
        });
      });

      it('contains expected data', function () {
        LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String), jasmine.any(Array), true).then(function (response) {
          expect(response).toEqual(mockData.balanceChangeByAbsenceType().values);
        });

        $httpBackend.flush();
      });
    });

    describe('calculateBalanceChange()', function () {
      var promise, requestData;

      beforeEach(function () {
        requestData = mockData.calculateBalanceChangeRequest();
        spyOn(LeaveRequestAPI, 'calculateBalanceChange').and.callThrough();
        promise = LeaveRequestAPI.calculateBalanceChange(requestData);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint', function () {
        promise.then(function (result) {
          expect(LeaveRequestAPI.calculateBalanceChange).toHaveBeenCalled();
        });
      });

      it('returns expected data keys', function () {
        promise.then(function (result) {
          //returns an object(associative array) and not an array
          expect(result.amount).toBeDefined();
          expect(result.breakdown).toBeDefined();

          var breakdown = result.breakdown[0];
          expect(breakdown.date).toBeDefined();
          expect(breakdown.amount).toBeDefined();
          expect(breakdown.type).toBeDefined();

          var breakdownType = breakdown.type;
          expect(breakdownType.id).toBeDefined();
          expect(breakdownType.value).toBeDefined();
          expect(breakdownType.label).toBeDefined();
        });
      });

      it('returns expected data values', function () {
        promise.then(function (result) {
          expect(result.amount).toEqual(jasmine.any(Number));
          expect(result.breakdown).toEqual(jasmine.any(Object));

          var breakdown = result.breakdown[0];
          expect(breakdown.amount).toEqual(jasmine.any(Number));
          expect(moment(breakdown.date, dateFormat, true).isValid()).toBe(true);
          expect(breakdown.type).toEqual(jasmine.any(Object));

          var breakdownType = breakdown.type;
          expect(breakdownType.id).toEqual(jasmine.any(Number));
          expect(breakdownType.value).toEqual(jasmine.any(Number));
          expect(mockData.getAllAbsenceTypesTitles()).toContain(breakdownType.label);
        });
      });

      describe('error handling', function () {

        beforeEach(function () {
          errorObject = {
            is_error: 1,
            error_message: 'contact_id, from_date and from_date_type in params are mandatory'
          };
          requestData = {};
          promise = LeaveRequestAPI.calculateBalanceChange(requestData);
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('throws an error', function () {
          promise.then(function (result) {
            expect(result).toEqual(errorObject);
          });
        });
      });
    });

    describe('create()', function () {

      beforeEach(function () {
        requestData = mockData.createRandomLeaveRequest();
        spyOn(LeaveRequestAPI, 'create').and.callThrough();
        spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
        promise = LeaveRequestAPI.create(requestData);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoints', function () {
        promise.then(function (result) {
          expect(LeaveRequestAPI.create).toHaveBeenCalled();
          expect(LeaveRequestAPI.isValid).toHaveBeenCalled();
        });
      });

      it('returns expected data keys', function () {
        promise.then(function (results) {
          var result = results[0];

          expect(result.id).toBeDefined();
          expect(result.type_id).toBeDefined();
          expect(result.contact_id).toBeDefined();
          expect(result.status_id).toBeDefined();
          expect(result.from_date).toBeDefined();
          expect(moment(result.from_date, dateFormat, true).isValid()).toBe(true);
          expect(result.from_date_type).toBeDefined();
        });
      });

      it('returns expected data values', function () {
        promise.then(function (results) {
          var result = results[0];

          expect(result.id).toEqual(jasmine.any(String));
          expect(result.type_id).toBeDefined();
          expect(mockData.getAllAbsenceTypesIds()).toContain(result.type_id);
          expect(result.contact_id).toEqual(jasmine.any(String));
          expect(mockData.getAllRequestStatusesValues()).toContain(result.status_id);
          expect(moment(result.from_date, dateFormat, true).isValid()).toBe(true);
          expect(mockData.getAllRequestDayValues()).toContain(result.from_date_type);
        });
      });

      describe('with some mandatory fields missing', function () {

        beforeEach(function () {
          errorObject = {
            is_error: 1,
            error_message: 'contact_id, from_date, status_id and from_date_type params are mandatory'
          };
          requestData = mockData.createRandomLeaveRequest();
          delete requestData.contact_id;
          promise = LeaveRequestAPI.create(requestData);
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('returns error', function () {
          promise.then(function (result) {
            expect(result).toEqual(errorObject);
          });
        });
      });
    });

    describe('create() to_date_type missing given from date', function () {

      beforeEach(function () {
        errorObject = {
          is_error: 1,
          error_message: 'to_date_type is mandatory'
        };
        requestData = mockData.createRandomLeaveRequest();
        delete requestData.to_date_type;
        promise = LeaveRequestAPI.create(requestData);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('returns error', function () {
        promise.then(function (result) {
          expect(result).toEqual(errorObject);
        });
      });
    });

    describe('isValid()', function () {

      describe('successful call', function () {

        beforeEach(function () {
          requestData = mockData.createRandomLeaveRequest();
          spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
          promise = LeaveRequestAPI.isValid(requestData);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('returns no errors', function () {
          promise.then(function (result) {
            expect(result.is_error).not.toBeDefined();
          });
        });
      });

      describe('error call', function () {

        beforeEach(function () {
          requestData = mockData.createRandomLeaveRequest();
          spyOn(LeaveRequestAPI, 'isValid').and.callFake(function (params) {
            return $q(function (resolve, reject) {
              resolve(mockData.getNotIsValid());
            });
          });
          promise = LeaveRequestAPI.isValid(requestData);
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('returns validation errors', function () {
          promise.then(function (result) {
            expect(result.count).toEqual(1);
          });
        });
      });
    });

    describe('update()', function () {

      beforeEach(function () {
        requestData = mockData.createRandomLeaveRequest();
        spyOn(LeaveRequestAPI, 'update').and.callThrough();
        promise = LeaveRequestAPI.update(requestData);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint', function () {
        promise.then(function (result) {
          expect(LeaveRequestAPI.update).toHaveBeenCalled();
        });
      });
    });
  });
});
