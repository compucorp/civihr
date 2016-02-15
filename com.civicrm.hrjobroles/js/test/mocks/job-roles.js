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
            }
        }
    };
});
