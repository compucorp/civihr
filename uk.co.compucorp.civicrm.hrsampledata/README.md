## Intro
CiviHR Sample Data is an extension that creates sample data for CiviHR which
gives you a better way to experience CiviHR easily.

The Sample data includes:
 * Contacts data with photos
 * Vacancies
 * Job Contracts
 * Job Roles
 * Bank Details
 * Emergency Contacts

## Installation

To install the extension when creating a new buildkit instance then you need to set
*WITH_HR_SAMPLE* environment variable to *1* before running *civibuild create* command

*Example :*

```bash
export WITH_HR_SAMPLE=1
civibuild create hr16 --civi-ver 4.7.9 --url http://localhost:8090
```

You can also install it from the extension manager page or via CiviCRM drush API :

```bash
drush cvapi Extension.install keys=uk.co.compucorp.civicrm.hrsampledata debug=1
```
