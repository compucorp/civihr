/* eslint-env amd */

define(function () {
  return {
    new_role: {
      department: null,
      is_edit: true,
      job_contract_id: null,
      level: null,
      location: null,
      newEndDate: null,
      newStartDate: null,
      title: 'Test'
    },
    contracts_data: {
      0: {
        end_date: '2016-01-31',
        id: '0',
        label: 'Test Contract 1 (01/01/2016 - 31/01/2016)',
        start_date: '2016-01-01',
        status: '',
        title: 'Test Contract 1'
      },
      1: {
        end_date: '2017-05-05',
        id: '1',
        label: 'Test Contract 2 (05/05/2016 - 05/05/2017)',
        start_date: '2016-05-05',
        status: '',
        title: 'Test Contract 2'
      },
      2: {
        id: '2',
        label: 'Test Contract 3 (01/02/2016 - Unspecified)',
        start_date: '2016-02-01',
        status: '',
        title: 'Test Contract 3'
      },
      3: {
        end_date: '2016-01-31',
        id: '3',
        label: 'Test Contract 3 (01/01/2016 - 31/01/2016)',
        start_date: '2016-01-01',
        status: '',
        title: 'Test Contract 3'
      }
    },
    form_data: {
      'start_date': {
        $error: []
      },
      'end_date': {
        $error: []
      },
      'title': {},
      'contract': {},
      'newLocation': {},
      'newRegion': {},
      'newDepartment': {},
      'newLevel': {},
      'description': {}
    },
    roles_data: {
      '0': {
        'title': 'Test',
        'job_contract_id': '1',
        'start_date': '2015-12-30 00:00:00',
        'end_date': null,
        'funders': [],
        'cost_centers': []
      },
      '1': {
        'title': 'Test',
        'job_contract_id': '1',
        'start_date': '2005-05-05 00:00:00',
        'end_date': '2006-05-05 00:00:00',
        'funders': [
          {
            id: 2,
            amount: '0',
            percentage: '1',
            type: '1',
            funder_id: {
              id: '1',
              title: 'Default Organization',
              weight: 1,
              is_active: 1
            }
          },
          {
            id: 4,
            amount: '1',
            percentage: '20',
            type: '0',
            funder_id: {
              id: '3',
              title: 'Bar Baz',
              weight: 3,
              is_active: 1
            }
          }
        ],
        'cost_centers': []
      },
      '2': {
        'title': 'Contract Dates',
        'job_contract_id': '3',
        'start_date': '2016-01-01',
        'end_date': '2016-01-31',
        'funders': [],
        'cost_centers': []
      },
      '3': {
        'title': 'Test filter',
        'job_contract_id': '1',
        'start_date': '2015-12-30 00:00:00',
        'end_date': null,
        'funders': [
          {
            id: 1,
            amount: '0',
            percentage: '2',
            type: '1',
            funder_id: ''
          },
          {
            id: 2,
            amount: '0',
            percentage: '1',
            type: '1',
            funder_id: {
              id: '1',
              title: 'Default Organization',
              weight: 1,
              is_active: 1
            }
          },
          {
            id: 3,
            amount: '0',
            percentage: '10',
            type: '1',
            funder_id: {
              id: '2',
              title: 'Foo Bar',
              weight: 2,
              is_active: 1
            }
          },
          {
            id: 4,
            amount: '1',
            percentage: '20',
            type: '0',
            funder_id: {
              id: '3',
              title: 'Bar Baz',
              weight: 3,
              is_active: 1
            }
          }
        ],
        'cost_centers': [
          {
            $$hashKey: 'object:845',
            amount: '0',
            cost_centre_id: '879',
            id: 1,
            percentage: '1',
            type: '1'
          },
          {
            $$hashKey: 'object:845',
            amount: '0',
            cost_centre_id: '890',
            id: 1,
            percentage: '0',
            type: '1'
          },
          {
            $$hashKey: 'object:845',
            amount: '2',
            cost_centre_id: '',
            id: 1,
            percentage: '0',
            type: '0'
          },
          {
            $$hashKey: 'object:845',
            amount: '2',
            cost_centre_id: '123',
            id: 1,
            percentage: '0',
            type: '0'
          }
        ]
      }
    },
    roles_data_from_api: {
      '0': {
        'amount_pay_cost_center': '|',
        'amount_pay_funder': '|',
        'cost_center': '|',
        'cost_center_val_type': '|',
        'end_date': null,
        'funder': '|',
        'funder_val_type': '|',
        'job_contract_id': '1',
        'percent_pay_cost_center': '|',
        'percent_pay_funder': '|',
        'start_date': '2015-12-30 00:00:00',
        'title': 'Test'
      },
      '1': {
        'amount_pay_cost_center': '|',
        'amount_pay_funder': '|0|1|',
        'cost_center': '|',
        'cost_center_val_type': '|',
        'end_date': '2006-05-05 00:00:00',
        'funder': '|1|3|',
        'funder_val_type': '|1|0|',
        'job_contract_id': '1',
        'percent_pay_cost_center': '|',
        'percent_pay_funder': '|1|0|',
        'start_date': '2005-05-05 00:00:00',
        'title': 'Test'
      },
      '2': {
        'amount_pay_cost_center': '|',
        'amount_pay_funder': '|',
        'cost_center': '|',
        'cost_center_val_type': '|',
        'end_date': '2016-01-31',
        'funder': '|',
        'funder_val_type': '|',
        'job_contract_id': '3',
        'percent_pay_cost_center': '|',
        'percent_pay_funder': '|',
        'start_date': '2016-01-01',
        'title': 'Contract Dates'
      },
      '3': {
        'amount_pay_cost_center': '|0|0|2|2|',
        'amount_pay_funder': '|0|0|0|1|',
        'cost_center': '|879|890|888|123|',
        'cost_center_val_type': '|1|1|0|0|',
        'end_date': null,
        'funder': '||1|2|3|',
        'funder_val_type': '|1|1|1|0',
        'job_contract_id': '1',
        'percent_pay_cost_center': '|1|1|0|0',
        'percent_pay_funder': '|0|0|0|20',
        'start_date': '2015-12-30 00:00:00',
        'title': 'Test filter'
      }
    },
    option_values: {
      'count': 8,
      'values': [
        {
          'id': '845',
          'option_group_id': '111',
          'label': 'Senior Manager',
          'value': 'Senior Manager',
          'name': 'Senior_Manager',
          'is_default': '0',
          'weight': '1',
          'is_optgroup': '0',
          'is_reserved': '0',
          'is_active': '1'
        },
        {
          'id': '879',
          'option_group_id': '124',
          'label': 'Other',
          'value': 'Other',
          'name': 'Other',
          'filter': '0',
          'is_default': '0',
          'weight': '3',
          'is_optgroup': '0',
          'is_reserved': '0',
          'is_active': '1'
        },
        {
          'id': '1045',
          'option_group_id': '124',
          'label': 'Test A',
          'value': '1',
          'name': 'Test A',
          'filter': '0',
          'is_default': '0',
          'weight': '2',
          'is_optgroup': '0',
          'is_reserved': '0',
          'is_active': '1'
        },
        {
          'id': '1046',
          'option_group_id': '124',
          'label': 'Test B',
          'value': '2',
          'name': 'Test B',
          'filter': '0',
          'is_default': '0',
          'weight': '1',
          'description': 'Test B',
          'is_optgroup': '0',
          'is_reserved': '0',
          'is_active': '1'
        },
        {
          'id': '846',
          'option_group_id': '231',
          'label': 'HR',
          'value': 'HR',
          'name': 'HR',
          'is_default': '0',
          'weight': '2',
          'is_optgroup': '0',
          'is_reserved': '0',
          'is_active': '1'
        },
        {
          'id': '847',
          'option_group_id': '102',
          'label': 'Headquarters',
          'value': 'Headquarters',
          'name': 'Headquarters',
          'is_default': '0',
          'weight': '1',
          'is_optgroup': '0',
          'is_reserved': '0',
          'is_active': '1'
        },
        {
          'id': '1160',
          'option_group_id': '107',
          'label': 'East',
          'value': 'East',
          'name': 'East',
          'is_default': '0',
          'weight': '1',
          'is_optgroup': '0',
          'is_reserved': '0',
          'is_active': '1'
        },
        {
          'id': '1293',
          'option_group_id': '145',
          'label': 'Compucorp',
          'value': '5',
          'name': 'Compucorp',
          'is_default': '0',
          'weight': '5',
          'is_optgroup': '0',
          'is_reserved': '0',
          'is_active': '1'
        }
      ],
      'optionGroupData': {
        'cost_centres': '124',
        'hrjc_department': '231',
        'hrjc_level_type': '111',
        'hrjc_location': '102',
        'hrjc_region': '107',
        'hrjc_funder': '145'
      }
    }
  };
});
