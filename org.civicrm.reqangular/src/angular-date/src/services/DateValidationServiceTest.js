describe('Unit: DateValidationService', function() {

    var Service;

    beforeEach(module('angular-date'));

    beforeEach(inject(function(DateValidationService) {
        Service = DateValidationService;
    }));

    it('Should be defined.', function() {
        expect(Service).toBeDefined();
    });

    it('SetOptions method extends options object.', function() {
        expect(Object.keys(Service.options).length).toEqual(0);

        Service.setOptions({
            test: 1,
            test2: 2
        });

        expect(Object.keys(Service.options).length).toEqual(2);

        Service.setOptions({
            test3: 1,
            test9: 2
        });

        expect(Object.keys(Service.options).length).toEqual(2+2);

        Service.setOptions({
            test8: 1,
            test: 2
        });

        expect(Object.keys(Service.options).length).toEqual(2+3);
    });

    it('_error() should throw error if custom function is not appiled', function() {
        var shouldThrow = function(){
            Service._error('Test', ['1', '2']);
        };
        
        expect(shouldThrow).toThrow(new Error('Test'));
    });

});