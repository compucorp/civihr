describe('Unit: CustomDateFilter', function() {

    var Filter, Factory;

    beforeEach(module('angular-date'));

    beforeEach(inject(function(CustomDateFilter, DateFactory) {
        Filter = CustomDateFilter;
        Factory = DateFactory;
    }));

    it('Should be defined.', function() {
        expect(Filter).toBeDefined();
    });

    it('Should not modify good date.', function() {
        expect(Filter('12/11/2013')).toEqual('12/11/2013');
    });


    it('Should convert other formats.', function() {
        expect(Filter('12-11-2013')).toEqual('12/11/2013');
        expect(Filter('12-11-2013 00:00:00')).toEqual('12/11/2013');
        expect(Filter('2013-11-12')).toEqual('12/11/2013');
        expect(Filter(1448031278000)).toEqual('20/11/2015');
    });

    it('Invalid date Should return null.', function() {
        expect(Filter('10/91/2012')).toEqual('Unspecified');

        expect(Filter(undefined)).toEqual('Unspecified');
        expect(Filter(null)).toEqual('Unspecified');
    });

    it('Empty date Should return "Unspecified".', function() {
        expect(Filter('')).toEqual('Unspecified');
        expect(Filter('0000-00-00 00:00:00')).toEqual('Unspecified');
        expect(Filter('testString')).toEqual('Unspecified');
    });

});