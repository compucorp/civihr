define([
  'mocks/data/leave-request-data',
  'mocks/data/sickness-leave-request-data',
  'mocks/data/toil-leave-request-data',
  'common/moment',
  'mocks/helpers/helper',
  'mocks/data/absence-type-data',
  'mocks/data/option-group-mock-data',
  'leave-absences/shared/apis/leave-request-api',
  'leave-absences/shared/modules/shared-settings',
], function (mockData, sicknessMockData, toilMockData, moment, helper, absenceTypeData, optionGroupMock) {
  'use strict';

  describe('LeaveRequestAPI', function () {
    var LeaveRequestAPI, $httpBackend, $rootScope, $q, $log, sharedSettings,
      promise, requestData, errorMessage;

    beforeEach(module('leave-absences.apis', 'leave-absences.settings'));

    beforeEach(inject(['LeaveRequestAPI', '$httpBackend', '$rootScope', '$q', '$log', 'shared-settings',
      function (_LeaveRequestAPI_, _$httpBackend_, _$rootScope_, _$q_, _$log_, _sharedSettings_) {
      LeaveRequestAPI = _LeaveRequestAPI_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;
      sharedSettings = _sharedSettings_;
      $q = _$q_;
      $log = _$log_;

      interceptHTTP();
    }]));

    describe('all()', function () {
      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'getAll').and.callThrough();
        promise = LeaveRequestAPI.all();
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the getAll() method', function () {
        expect(LeaveRequestAPI.getAll.calls.mostRecent().args[0]).toBe('LeaveRequest');
        expect(LeaveRequestAPI.getAll.calls.mostRecent().args[5]).toBe('getFull');
      });

      it('returns all the data', function () {
        promise.then(function (response) {
          expect(response.list).toEqual(mockData.all().values);
        });
      });
    });

    describe('all() sickness request', function () {
      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'getAll').and.callThrough();
        promise = LeaveRequestAPI.all(null, null, null, {}, false, 'sick');
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the getAll() method', function () {
        expect(LeaveRequestAPI.getAll.calls.mostRecent().args[0]).toBe('SicknessRequest');
        expect(LeaveRequestAPI.getAll.calls.mostRecent().args[5]).toBe('getFull');
      });
    });

    describe('all() toil request', function() {
      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'getAll').and.callThrough();
        promise = LeaveRequestAPI.all(null, null, null, {}, false, 'toil');
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the getAll() method', function () {
        expect(LeaveRequestAPI.getAll.calls.mostRecent().args[0]).toBe('TOILRequest');
        expect(LeaveRequestAPI.getAll.calls.mostRecent().args[5]).toBe('getFull');
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
          expect(data).toBe('contact_id and period_id are mandatory');
        }

        it('throws error if contact_id is blank', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(null, jasmine.any(String))
            .catch(commonExpect);
        });

        it('throws error if periodId is blank', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), null)
            .catch(commonExpect);
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
          }, false);
        });

        it('sends as `public_holiday` the original value if truthy value had been passed', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String), jasmine.any(Array), true);

          expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', jasmine.objectContaining({
            public_holiday: true
          }), false);
        });

        it('sends as `statuses` an "IN" list if the original value is an array', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String), jasmine.any(Array), jasmine.any(Boolean));

          expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', jasmine.objectContaining({
            statuses: { "IN": jasmine.any(Array) },
          }), false);
        });
      });

      it('contains expected data', function () {
        LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String), jasmine.any(Array), true).then(function (response) {
          expect(response).toEqual(mockData.balanceChangeByAbsenceType().values);
        });

        $httpBackend.flush();
      });


    });

    describe('balanceChangeByAbsenceType() with error from server', function () {
      var error;

      beforeEach(function () {
        error = mockData.singleDataError();

        spyOn(LeaveRequestAPI, 'sendGET').and.callFake(function () {
          return $q.resolve(error);
        });

        requestData = helper.createRandomLeaveRequest();
        promise = LeaveRequestAPI.balanceChangeByAbsenceType(requestData);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('returns error message', function () {
        promise.catch(function (result) {
          expect(result).toEqual(error.error_message);
        });
      });
    });

    describe('calculateBalanceChange()', function () {

      beforeEach(function () {
        requestData = helper.createRandomLeaveRequest();
        //todo --> will be removed once from_type will change to from_date_type
        requestData = _.mapKeys(requestData, function (value, key) {
          if (key === 'from_date_type') {
            return 'from_type';
          } else if (key === 'to_date_type') {
            return 'to_type';
          }

          return key;
        });

        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.calculateBalanceChange(requestData);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint', function () {
        promise.then(function (result) {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalled();
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith(jasmine.any(String),
            jasmine.any(String), jasmine.any(Object));
        });
      });

      it('returns expected data keys', function () {
        promise.then(function (result) {
          //returns an object(associative array) and not an array
          var breakdown = result.breakdown[0];
          var breakdownType = breakdown.type;

          expect(result.amount).toBeDefined();
          expect(result.breakdown).toBeDefined();
          expect(breakdown.date).toBeDefined();
          expect(breakdown.amount).toBeDefined();
          expect(breakdown.type).toBeDefined();
          expect(breakdownType.id).toBeDefined();
          expect(breakdownType.value).toBeDefined();
          expect(breakdownType.label).toBeDefined();
        });
      });

      it('returns expected values', function () {
        promise.then(function (result) {
          var breakdown = result.breakdown[0];
          var breakdownType = breakdown.type;

          expect(result.amount).toEqual(jasmine.any(Number));
          expect(result.breakdown).toEqual(jasmine.any(Object));
          expect(breakdown.amount).toEqual(jasmine.any(Number));
          expect(moment(breakdown.date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
          expect(breakdown.type).toEqual(jasmine.any(Object));
          expect(breakdownType.id).toEqual(jasmine.any(Number));
          expect(breakdownType.value).toEqual(jasmine.any(Number));
          expect(absenceTypeData.getAllAbsenceTypesTitles()).toContain(breakdownType.label);
        });
      });

      describe('when mandatory field is missing', function () {

        beforeEach(function () {
          errorMessage = 'contact_id, from_date and from_type in params are mandatory';
          requestData = {};
          promise = LeaveRequestAPI.calculateBalanceChange(requestData);
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('throws an error', function () {
          promise.catch(function (result) {
            expect(result).toBe(errorMessage);
          });
        });
      });
    });

    describe('calculateBalanceChange() with error from server', function () {
      var error;

      beforeEach(function () {
        error = mockData.singleDataError();

        spyOn(LeaveRequestAPI, 'sendGET').and.callFake(function () {
          return $q.resolve(error);
        });

        requestData = helper.createRandomLeaveRequest();
        promise = LeaveRequestAPI.calculateBalanceChange(requestData);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('returns error message', function () {
        promise.catch(function (result) {
          expect(result).toEqual(error.error_message);
        });
      });
    });

    describe('create()', function () {

      beforeEach(function () {
        requestData = helper.createRandomLeaveRequest();
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.create(requestData);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest', 'create', requestData);
        });
      });

      it('returns expected keys', function () {
        promise.then(function (result) {
          expect(result.id).toBeDefined();
          expect(result.type_id).toBeDefined();
          expect(result.contact_id).toBeDefined();
          expect(result.status_id).toBeDefined();
          expect(result.from_date).toBeDefined();
          expect(moment(result.from_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
          expect(result.from_date_type).toBeDefined();
        });
      });

      it('returns expected values', function () {
        promise.then(function (result) {
          expect(result.id).toEqual(jasmine.any(String));
          expect(result.type_id).toBeDefined();
          expect(absenceTypeData.getAllAbsenceTypesIds()).toContain(result.type_id);
          expect(result.contact_id).toEqual(jasmine.any(String));
          expect(optionGroupMock.getAllRequestStatusesValues()).toContain(result.status_id);
          expect(moment(result.from_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
          expect(optionGroupMock.getAllRequestDayValues()).toContain(result.from_date_type);
        });
      });

      describe('with mandatory field missing', function () {

        beforeEach(function () {
          errorMessage = 'contact_id, from_date, status_id and from_date_type params are mandatory';
          requestData = helper.createRandomLeaveRequest();
          delete requestData.contact_id;
          promise = LeaveRequestAPI.create(requestData);
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('returns error', function () {
          promise.catch(function (result) {
            expect(result).toBe(errorMessage);
          });
        });
      });

      describe('missing to date type value, given to date', function () {

        beforeEach(function () {
          errorMessage = 'to_date_type is mandatory';
          requestData = helper.createRandomLeaveRequest();
          delete requestData.to_date_type;
          promise = LeaveRequestAPI.create(requestData);
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('returns error', function () {
          promise.catch(function (result) {
            expect(result).toBe(errorMessage);
          });
        });
      });
    });

    describe('create() with error from server', function () {
      var error;

      beforeEach(function () {
        error = mockData.singleDataError();

        spyOn(LeaveRequestAPI, 'sendPOST').and.callFake(function () {
          return $q.resolve(error);
        });

        requestData = helper.createRandomLeaveRequest();
        promise = LeaveRequestAPI.create(requestData);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('returns error message', function () {
        promise.catch(function (result) {
          expect(result).toEqual(error.error_message);
        });
      });
    });

    describe('create() for sickness request', function () {
      beforeEach(function () {
        requestData = helper.createRandomSicknessRequest();
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.create(requestData, 'sick');
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith( 'SicknessRequest',
            'create', requestData);
        });
      });

      it('sets leave request', function () {
        promise.then(function (result) {
          expect(result.leave_request_id).toEqual(jasmine.any(String));
        });
      });
    });

    describe('create() for toil request', function() {
      beforeEach(function () {
        requestData = helper.createRandomTOILRequest();
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.create(requestData, 'toil');
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith( 'TOILRequest',
            'create', requestData);
        });
      });

      it('sets leave request', function () {
        promise.then(function (result) {
          expect(result.leave_request_id).toEqual(jasmine.any(String));
        });
      });
    });

    describe('isValid()', function () {
      describe('when called with valid data', function () {
        beforeEach(function () {
          requestData = helper.createRandomSicknessRequest();
          spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
          promise = LeaveRequestAPI.isValid(requestData);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls endpoint', function () {
          promise.then(function () {
            expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest', 'isValid', requestData);
          });
        });

        it('returns no errors', function () {
          promise.then(function (result) {
            expect(result).toEqual([]);
          });
        });
      });

      describe('when called with invalid data', function () {
        beforeEach(function () {
          requestData = helper.createRandomSicknessRequest();
          spyOn(LeaveRequestAPI, 'isValid').and.callFake(function (params) {
            return $q.reject(mockData.getNotIsValid());
          });
          promise = LeaveRequestAPI.isValid(requestData);
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('returns validation errors', function () {
          promise.catch(function (result) {
            expect(result.count).toEqual(1);
          });
        });
      });
    });

    describe('isValid() with error from server', function () {
      var error;

      beforeEach(function () {
        error = mockData.singleDataError();

        spyOn(LeaveRequestAPI, 'sendPOST').and.callFake(function () {
          return $q.resolve(error);
        });

        requestData = helper.createRandomLeaveRequest();
        promise = LeaveRequestAPI.isValid(requestData);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('returns error message', function () {
        promise.catch(function (result) {
          expect(result).toEqual(error.error_message);
        });
      });
    });

    describe('isValid() for sickness request', function () {
      describe('when called with valid data', function () {
        beforeEach(function () {
          requestData = helper.createRandomSicknessRequest();
          spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
          promise = LeaveRequestAPI.isValid(requestData, 'sick');
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls endpoint', function () {
          promise.then(function () {
            expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('SicknessRequest', 'isValid', requestData);
          });
        });

        it('returns no errors', function () {
          promise.then(function (result) {
            expect(result).toEqual([]);
          });
        });
      });

      describe('when called with invalid data', function () {
        beforeEach(function () {
          requestData = helper.createRandomSicknessRequest();
          spyOn(LeaveRequestAPI, 'isValid').and.callFake(function (params) {
            return $q.reject(mockData.getNotIsValid());
          });
          promise = LeaveRequestAPI.isValid(requestData, 'sick');
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('returns validation errors', function () {
          promise.catch(function (result) {
            expect(result.count).toEqual(1);
          });
        });
      });
    });

    describe('isValid() for toil request', function () {
      describe('when called with valid data', function () {
        beforeEach(function () {
          requestData = helper.createRandomTOILRequest();
          spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
          promise = LeaveRequestAPI.isValid(requestData, 'toil');
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls endpoint', function () {
          promise.then(function () {
            expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('TOILRequest', 'isValid', requestData);
          });
        });

        it('returns no errors', function () {
          promise.then(function (result) {
            expect(result).toEqual([]);
          });
        });
      });

      describe('when called with invalid data', function () {
        beforeEach(function () {
          requestData = helper.createRandomTOILRequest();
          spyOn(LeaveRequestAPI, 'isValid').and.callFake(function (params) {
            return $q.reject(mockData.getNotIsValid());
          });
          promise = LeaveRequestAPI.isValid(requestData, 'toil');
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('returns validation errors', function () {
          promise.catch(function (result) {
            expect(result.count).toEqual(1);
          });
        });
      });
    });

    describe('update()', function () {
      var updatedRequestData = {};

      beforeEach(function () {
        var changedStatusId = {
          status_id: mockData.all().values[5].status_id
        };
        requestData = mockData.all().values[0];
        requestData = _.assign(updatedRequestData, requestData, changedStatusId);
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.update(updatedRequestData);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest', 'create', requestData);
        });
      });

      it('returns updated leave request', function () {
        promise.then(function (result) {
          expect(result.id).toBeDefined();
        });
      });

      describe('when id is not set', function () {
        beforeEach(function () {
          errorMessage = 'id is mandatory field';
          //remove id
          delete updatedRequestData.id;
          promise = LeaveRequestAPI.update(updatedRequestData);
        });

        afterEach(function () {
          //resolves to local promise hence no need to flush http call
          $rootScope.$apply();
        });

        it('returns error', function () {
          promise.catch(function (result) {
            expect(result).toBe(errorMessage);
          });
        });
      });
    });

    describe('update() with error from server', function () {
      var error;

      beforeEach(function () {
        error = mockData.singleDataError();

        spyOn(LeaveRequestAPI, 'sendPOST').and.callFake(function () {
          return $q.resolve(error);
        });

        requestData = mockData.all().values[0];
        promise = LeaveRequestAPI.update(requestData);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('returns error message', function () {
        promise.catch(function (result) {
          expect(result).toEqual(error.error_message);
        });
      });
    });

    describe('update() for sickness request', function () {
      var updatedRequestData = {};

      beforeEach(function () {
        var changedStatusId = {
          status_id: mockData.all().values[5].status_id
        };
        requestData = mockData.all().values[0];
        requestData = _.assign(updatedRequestData, requestData, changedStatusId);
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.update(updatedRequestData, 'sick');
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('SicknessRequest', 'create', requestData);
        });
      });

      it('returns updated leave request', function () {
        promise.then(function (result) {
          expect(result.id).toBeDefined();
        });
      });

      describe('when id is not set', function () {
        beforeEach(function () {
          errorMessage = 'id is mandatory field';
          //remove id
          delete updatedRequestData.id;
          promise = LeaveRequestAPI.update(updatedRequestData, 'sick');
        });

        afterEach(function () {
          //resolves to local promise hence no need to flush http call
          $rootScope.$apply();
        });

        it('returns error', function () {
          promise.catch(function (result) {
            expect(result).toBe(errorMessage);
          });
        });
      });
    });

    describe('update() for toil request', function () {
      var updatedRequestData = {};

      beforeEach(function () {
        var changedStatusId = {
          status_id: mockData.all().values[5].status_id
        };
        requestData = mockData.all().values[0];
        requestData = _.assign(updatedRequestData, requestData, changedStatusId);
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.update(updatedRequestData, 'toil');
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('TOILRequest', 'create', requestData);
        });
      });

      it('returns updated leave request', function () {
        promise.then(function (result) {
          expect(result.id).toBeDefined();
        });
      });

      describe('when id is not set', function () {
        beforeEach(function () {
          errorMessage = 'id is mandatory field';
          //remove id
          delete updatedRequestData.id;
          promise = LeaveRequestAPI.update(updatedRequestData, 'sick');
        });

        afterEach(function () {
          //resolves to local promise hence no need to flush http call
          $rootScope.$apply();
        });

        it('returns error', function () {
          promise.catch(function (result) {
            expect(result).toBe(errorMessage);
          });
        });
      });
    });

    describe('isManagedBy()', function () {

      var leaveRequestID = '101',
        contactID = '102';

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.isManagedBy(leaveRequestID, contactID);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint with leaveRequestID and contactID', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest',
            'isManagedBy', jasmine.objectContaining({
              leave_request_id: leaveRequestID,
              contact_id: contactID
            }));
        });
      });

      it('returns data', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.isManagedBy().values);
        });
      })
    });

    /**
     * Intercept HTTP calls to be handled by httpBackend
     **/
    function interceptHTTP() {
      //Intercept backend calls for LeaveRequest.all
      $httpBackend.whenGET(/action\=getFull&entity\=LeaveRequest/)
        .respond(mockData.all());

      //Intercept backend calls for SicknessRequest.all
      $httpBackend.whenGET(/action\=getFull&entity\=SicknessRequest/)
        .respond(sicknessMockData.all());

      //Intercept backend calls for TOILRequest.all
      $httpBackend.whenGET(/action\=getFull&entity\=TOILRequest/)
        .respond(toilMockData.all());

      //Intercept backend calls for LeaveRequest.balanceChangeByAbsenceType
      $httpBackend.whenGET(/action\=getbalancechangebyabsencetype&entity\=LeaveRequest/)
        .respond(mockData.balanceChangeByAbsenceType());

      //Intercept backend calls for LeaveRequest.create in POST
      $httpBackend.whenPOST(/\/civicrm\/ajax\/rest/)
        .respond(function (method, url, data, headers, params) {

          if (helper.isEntityActionInPost(data, 'LeaveRequest', 'create')) {
            return [201, mockData.all()];
          } else if (helper.isEntityActionInPost(data, 'LeaveRequest', 'calculatebalancechange')) {
            return [200, mockData.calculateBalanceChange()];
          } else if (helper.isEntityActionInPost(data, 'LeaveRequest', 'isValid')) {
            return [200, mockData.getisValid()];
          } else if (helper.isEntityActionInPost(data, 'LeaveRequest', 'isManagedBy')) {
            return [200, mockData.isManagedBy()];
          } else if (helper.isEntityActionInPost(data, 'SicknessRequest', 'create')) {
            return [201, sicknessMockData.all()];
          } else if (helper.isEntityActionInPost(data, 'SicknessRequest', 'isValid')) {
            return [200, mockData.getisValid()];
          }
          else if (helper.isEntityActionInPost(data, 'TOILRequest', 'create')) {
            return [201, toilMockData.all()];
          }
          else if (helper.isEntityActionInPost(data, 'TOILRequest', 'isValid')) {
            return [200, mockData.getisValid()];
          }
        });
    }
  });
});
