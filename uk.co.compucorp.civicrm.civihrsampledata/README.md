## Intro
CiviHR Sample Data is an extensions that creates a sample data for CiviHR which
gives you a better way to experience CiviHR easily.

Sample data include:
 * Contacts data with photos
 * Vacancies
 * Job Contracts
 * Job Roles
 * Bank Details
 * Emergency Contacts

## Installation

To install the extension when creating a new buildkit instance then add *--with-hrsample-data* option.

*Example :*

```bash
civibuild create hr16 --civi-ver 4.7.9 --url http://localhost:8090 --with-hrsample-data
```

you can also enable it from the extension manager page , but please consider changing your php timeout
settings in php.ini file to 0 before the installation and then you can revert it back to default
once you are done.
