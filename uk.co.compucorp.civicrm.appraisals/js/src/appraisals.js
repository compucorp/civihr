require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
        'appraisals': CRM.vars.appraisals.baseURL + '/js/src/appraisals'
    }
});

require(['appraisals/app']);
