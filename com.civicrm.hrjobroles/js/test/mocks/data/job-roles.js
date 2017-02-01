/**
 * Created by kkalamarski on 15/02/16.
 */
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
            title: "Test"
        },
        contracts_data: {
            0: {
                end_date: "2016-01-31",
                id: "0",
                label: "Test Contract 1 (01/01/2016 - 31/01/2016)",
                start_date: "2016-01-01",
                status: "",
                title: "Test Contract 1"
            },
            1: {
                end_date: "2017-05-05",
                id: "1",
                label: "Test Contract 2 (05/05/2016 - 05/05/2017)",
                start_date: "2016-05-05",
                status: "",
                title: "Test Contract 2"
            },
            2: {
                id: "2",
                label: "Test Contract 3 (01/02/2016 - Unspecified)",
                start_date: "2016-02-01",
                status: "",
                title: "Test Contract 3"
            },
            3: {
                end_date: "2016-01-31",
                id: "3",
                label: "Test Contract 3 (01/01/2016 - 31/01/2016)",
                start_date: "2016-01-01",
                status: "",
                title: "Test Contract 3"
            }
        },
        form_data: {
            "start_date": {
                $error: []
            },
            "end_date": {
                $error: []
            },
            "title": {},
            "contract": {},
            "newLocation": {},
            "newRegion": {},
            "newDepartment": {},
            "newLevel": {},
            "description": {}
        },
        roles_data: {
            "0": {
                "title": "Test",
                "job_contract_id": "1",
                "start_date": "2015-12-30 00:00:00",
                "end_date": null,
                "funders": [],
                "cost_centers": []
            },
            "1": {
              "title": "Test",
              "job_contract_id": "1",
              "start_date": "2005-05-05 00:00:00",
              "end_date": "2006-05-05 00:00:00",
              "funders": [
                {
                  id: 2,
                  amount: "0",
                  percentage: "1",
                  type: "1",
                  funder_id: {
                    id: "1",
                    sort_name: "Default Organization"
                  }
                },
                {
                  id: 4,
                  amount: "1",
                  percentage: "20",
                  type: "0",
                  funder_id: {
                    id: "3",
                    sort_name: "Bar Baz"
                  }
                }
              ],
              "cost_centers": []
            },
            "2": {
                "title": "Contract Dates",
                "job_contract_id": "3",
                "start_date": "2016-01-01",
                "end_date": "2016-01-31",
                "funders": [],
                "cost_centers": []
            },
            "3": {
              "title": "Test filter",
              "job_contract_id": "1",
              "start_date": "2015-12-30 00:00:00",
              "end_date": null,
              "funders": [
                {
                  id: 1,
                  amount: "0",
                  percentage: "2",
                  type: "1",
                  funder_id: ""
                },
                {
                  id: 2,
                  amount: "0",
                  percentage: "1",
                  type: "1",
                  funder_id: {
                    id: "1",
                    sort_name: "Default Organization"
                  }
                },
                {
                  id: 3,
                  amount: "0",
                  percentage: "10",
                  type: "1",
                  funder_id: {
                    id: "2",
                    sort_name: "Foo Bar"
                  }
                },
                {
                  id: 4,
                  amount: "1",
                  percentage: "20",
                  type: "0",
                  funder_id: {
                    id: "3",
                    sort_name: "Bar Baz"
                  }
                }
              ],
              "cost_centers": [
                {
                  $$hashKey:"object:845",
                  amount:"0",
                  cost_centre_id:"879",
                  id:1,
                  percentage:"1",
                  type:"1",
                },
                {
                  $$hashKey:"object:845",
                  amount:"0",
                  cost_centre_id:"890",
                  id:1,
                  percentage:"0",
                  type:"1",
                },
                {
                  $$hashKey:"object:845",
                  amount:"2",
                  cost_centre_id:"",
                  id:1,
                  percentage:"0",
                  type:"0",
                },
                {
                  $$hashKey:"object:845",
                  amount:"2",
                  cost_centre_id:"123",
                  id:1,
                  percentage:"0",
                  type:"0",
                }
              ]
            }
        }
    };
});
