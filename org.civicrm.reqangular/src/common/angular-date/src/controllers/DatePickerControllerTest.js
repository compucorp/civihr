describe('Unit: DateControllerTest', function() {

    var Controller, $scope;

    beforeEach(module('angular-date'));

    beforeEach(inject(function($controller, $rootScope) {

        $scope = $rootScope.$new();

        Controller = $controller('DatePickerController', {
            $scope: $scope,
            $attrs: {}
        });
    }));

    it('Should be defined', function() {
        expect(Controller).toBeDefined();
    });

    it('Check if Controller extends DatepickerController', function() {
        expect(Controller.modes).toBeDefined();
        expect($scope.implements).toEqual('DatepickerController');
    });

    it('We can override methods', function() {
        expect(function(){
            Controller.render();
        }).not.toThrow();
    });

    it('We remove offset', function() {
        expect(Controller.parseDate(new Date(2003, 2, 11, 1, 2, 2)).toString()).toBe('Tue Mar 11 2003 00:00:00 GMT+0100 (CET)');
    });

});