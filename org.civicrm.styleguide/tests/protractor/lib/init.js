// This initializes the test environment; specifically:
//   1. Ensure that the right extensions are active.
//   2. Load metadata about the local CiviCRM instance.

var cv = require('civicrm-cv')({mode: 'sync'});
cv('api extension.install keys=org.civicrm.styleguide,org.civicrm.bootstrapcivicrm');

/**
 * The _CV variable stores a cache of metadata about the local CiviCRM instance.
 *
 * Obtaining this data incurs a small overhead, so we only do it once - during initialization.
 *
 * Typical properties:
 * {
 *   "ADMIN_EMAIL": "admin@example.com",
 *   "ADMIN_PASS": "admin",
 *   "ADMIN_USER": "admin",
 *
 *   "DEMO_EMAIL": "demo@example.com",
 *   "DEMO_PASS": "demo",
 *   "DEMO_USER": "demo",
 *
 *   "CIVI_CORE": "/home/myuser/buildkit/build/dmaster/sites/all/modules/civicrm/",
 *   "CIVI_SETTINGS": "/home/myuser/buildkit/build/dmaster/sites/default/civicrm.settings.php",
 *   "CIVI_SITE_KEY": "t0ps3cr3t",
 *   "CIVI_UF": "Drupal",
 *   "CIVI_URL": "http://dmaster.l/sites/all/modules/civicrm/",
 *   "CIVI_VERSION": "4.7.13",
 *
 *   "CMS_ROOT": "/home/myuser/buildkit/build/dmaster/",
 *   "CMS_URL": "http://dmaster.l/",
 *   "CMS_VERSION": "7.41",
 *
 *   "CIVI_DB_ARGS": "-h 127.0.0.1 -u dmasterciv_jr7lx -pdmasterciv_jr7lx -P 3307 dmasterciv_jr7lx",
 *   "CIVI_DB_DSN": "mysql://dmasterciv_jr7lx:t0ps3cr3t@127.0.0.1:3307/dmasterciv_jr7lx?new_link=true",
 *   "CIVI_DB_HOST": "127.0.0.1",
 *   "CIVI_DB_NAME": "dmasterciv_jr7lx",
 *   "CIVI_DB_PASS": "t0ps3cr3t",
 *   "CIVI_DB_PORT": 3307,
 *   "CIVI_DB_USER": "dmasterciv_jr7lx",
 *
 *   "CMS_DB_ARGS": "-h 127.0.0.1 -u dmastercms_3vky1 -pdmastercms_3vky1 -P 3307 dmastercms_3vky1",
 *   "CMS_DB_DSN": "mysql://dmastercms_3vky1:t0ps3cr3t@127.0.0.1:3307/dmastercms_3vky1?new_link=true",
 *   "CMS_DB_HOST": "127.0.0.1",
 *   "CMS_DB_NAME": "dmastercms_3vky1",
 *   "CMS_DB_PASS": "t0ps3cr3t",
 *   "CMS_DB_PORT": 3307,
 *   "CMS_DB_USER": "dmastercms_3vky1",
 *
 *   "TEST_DB_ARGS": "-h 127.0.0.1 -u dmastertes_6n6z6 -pdmastertes_6n6z6 -P 3307 dmastertes_6n6z6",
 *   "TEST_DB_DSN": "mysql://dmastertes_6n6z6:t0ps3cr3t@127.0.0.1:3307/dmastertes_6n6z6?new_link=true",
 *   "TEST_DB_HOST": "127.0.0.1",
 *   "TEST_DB_NAME": "dmastertes_6n6z6",
 *   "TEST_DB_PASS": "t0ps3cr3t",
 *   "TEST_DB_PORT": 3307,
 *   "TEST_DB_USER": "dmastertes_6n6z6",
 * }
 */
global._CV = cv('vars:show');
