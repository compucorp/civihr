define(function () {
  return {
    values: [
      {
        'id': '1',
        'contact_id': '1',
        'is_primary': '1',
        'deleted': '1',
        'is_current': '1',
        'period_start_date': '2016-01-01',
        'period_end_date': '2016-12-31',
        'title': 'Title',
        'api.HRJobContractRevision.get': {
          'is_error': 0,
          'version': 3,
          'count': 1,
          'id': 1,
          'values': [
            {
              'id': '1',
              'jobcontract_id': '1',
              'api.HRJobDetails.getsingle': {
                'id': '1',
                'position': 'Position 1',
                'title': 'Title 1',
                'contract_type': 'Type #1',
                'period_start_date': '2016-01-01',
                'period_end_date': '2016-05-31',
                'jobcontract_revision_id': '1'
              }
            },
            {
              'id': '2',
              'jobcontract_id': '1',
              'api.HRJobDetails.getsingle': {
                'id': '2',
                'position': 'Position 2',
                'title': 'Title 2',
                'contract_type': 'Type #2',
                'period_start_date': '2016-01-01',
                'period_end_date': '2016-12-31',
                'jobcontract_revision_id': '2'
              }
            }
          ]
        }
      }
    ]
  };
})
