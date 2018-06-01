const _ = require('lodash');
const argv = require('yargs').argv;
const backstopjs = require('backstopjs');
const clean = require('gulp-clean');
const execSync = require('child_process').execSync;
const file = require('gulp-file');
const fs = require('fs');
const gulp = require('gulp');
const notify = require('gulp-notify');
const path = require('path');
const puppeteer = require('puppeteer');
const Promise = require('es6-promise').Promise;

const utils = require('../utils');

const BACKSTOP_DIR = path.join(__dirname, '..', '..', 'backstop_data');
const DEFAULT_USER = 'civihr_admin';
const USERS = ['admin', 'civihr_admin', 'civihr_manager', 'civihr_staff'];
const CONFIG_TPL = { 'url': 'http://%{site-host}' };
const FILES = {
  siteConfig: path.join(BACKSTOP_DIR, 'site-config.json'),
  temp: path.join(BACKSTOP_DIR, 'backstop.temp.json'),
  tpl: path.join(BACKSTOP_DIR, 'backstop.tpl.json')
};

module.exports = ['reference', 'test', 'openReport', 'approve'].map(action => {
  return {
    name: `backstopjs:${action}`,
    fn: () => runBackstopJS(action)
  };
});

/**
 * Concatenates all the scenarios (if no specific scenario file is specified)
 *
 * @param  {Object} usersIds
 * @return {Array}
 */
function buildScenariosList (usersIds) {
  const config = siteConfig();
  const dirPath = path.join(BACKSTOP_DIR, 'scenarios');

  return _(fs.readdirSync(dirPath))
    .filter(scenario => {
      return argv.configFile ? scenario === argv.configFile : true && scenario.endsWith('.json');
    })
    .map(scenarioFile => {
      const scenarioPath = path.join(dirPath, scenarioFile);

      return JSON.parse(fs.readFileSync(scenarioPath)).scenarios;
    })
    .flatten()
    .map((scenario, index, scenarios) => {
      const user = scenario.user || DEFAULT_USER;

      return _.assign(scenario, {
        cookiePath: path.join(BACKSTOP_DIR, 'cookies', `${user}.json`),
        count: `(${(index + 1)} of ${scenarios.length})`,
        url: constructScenarioUrl(config.url, scenario.url, usersIds)
      });
    })
    .value();
}

/**
 * Removes the temp config file and sends a notification
 * based on the given outcome from BackstopJS
 *
 * @param {Boolean} success
 */
function cleanUpAndNotify (success) {
  gulp
    .src(FILES.temp, { read: false })
    .pipe(clean())
    .pipe(notify({
      message: success ? 'Success' : 'Error',
      title: 'BackstopJS',
      sound: 'Beep'
    }));
}

/**
 * Constructs URL for BackstopJS scenario based on
 * site URL, scenario config URL and contact "roles" and IDs map
 *
 * @param  {String} siteUrl
 * @param  {String} scenarioUrl
 * @param  {Object} usersIds
 * @return {String}
 */
function constructScenarioUrl (siteUrl, scenarioUrl, usersIds) {
  return scenarioUrl
    .replace('{{siteUrl}}', siteUrl)
    .replace(/\{\{contactId:([^}]+)\}\}/g, (__, user) => usersIds[user].civi);
}

/**
 * Creates the content of the config temporary file that will be fed to BackstopJS
 * The content is the mix of the config template and the list of scenarios
 * under the scenarios/ folder
 *
 * @return {String}
 */
function createTempConfig () {
  const userIds = getUsersIds();
  const list = buildScenariosList(userIds);
  const content = JSON.parse(fs.readFileSync(FILES.tpl));

  content.scenarios = list;

  return JSON.stringify(content);
}

/**
 * Given a set of UF matches, it finds the contact with the specified drupal id
 *
 * @param  {Array} ufMatches
 * @param  {Number} drupalId
 * @return {Object}
 */
function findContactByDrupalId (ufMatches, drupalId) {
  return _.find(ufMatches, match => match.uf_id === drupalId);
}

/**
 * Creates and returns a mapping of users to their drupal and civi ids
 *
 * To fetch the drupal ids, the `drush user-information` command is used. Those
 * ids are used to fetch the civi ids by using the UFMatch api
 *
 * @return {Promise} resolved with {Object}, ex. { civihr_staff: { drupal: 1, civi: 2 } }
 */
function getUsersIds () {
  const userInfoCmd = `drush user-information ${USERS.join(',')} --format=json`;

  let usersIds, ufMatches;
  let ufMatchCmd = 'echo \'{ "uf_id": { "IN":[%{uids}] } }\' | cv api UFMatch.get sequential=1';

  usersIds = _.transform(JSON.parse(execSync(userInfoCmd)), (result, user) => {
    result[user.name] = { drupal: user.uid };
  });

  ufMatchCmd = ufMatchCmd.replace('%{uids}', _.map(usersIds, 'drupal').join(','));
  ufMatches = JSON.parse(execSync(ufMatchCmd)).values;

  usersIds = _.transform(usersIds, (result, userIds, name) => {
    userIds.civi = findContactByDrupalId(ufMatches, userIds.drupal).contact_id;
    result[name] = userIds;
  });

  return usersIds;
}

/**
 * Runs backstopJS with the given command.
 *
 * It fills the template file with the list of scenarios, creates a temp
 * file passed to backstopJS, then removes the temp file once the command is completed
 *
 * @param  {String} command
 * @return {Promise}
 */
function runBackstopJS (command) {
  if (touchSiteConfigFile()) {
    utils.throwError(
      'No site-config.json file detected!\n' +
      `\tOne has been created for you under ${path.basename(BACKSTOP_DIR)}/\n` +
      '\tPlease insert the real value for each placeholder and try again'
    );
  }

  return new Promise((resolve, reject) => {
    let success = false;

    gulp.src(FILES.tpl)
      .pipe(file(path.basename(FILES.temp), createTempConfig()))
      .pipe(gulp.dest(BACKSTOP_DIR))
      .on('end', async () => {
        try {
          await writeCookies();
          await backstopjs(command, { configPath: FILES.temp, filter: argv.filter });

          success = true;
        } finally {
          cleanUpAndNotify(success);

          success ? resolve() : reject(new Error('BackstopJS error'));
        }
      });
  })
    .catch(err => {
      utils.throwError(err.message);
    });
}

/**
 * Returns the content of site config file
 *
 * @return {Object}
 */
function siteConfig () {
  return JSON.parse(fs.readFileSync(FILES.siteConfig));
}

/**
 * Creates the site config file is in the backstopjs folder, if it doesn't exists yet
 *
 * @return {Boolean} Whether the file had to be created or not
 */
function touchSiteConfigFile () {
  let created = false;

  try {
    fs.readFileSync(FILES.siteConfig);
  } catch (err) {
    fs.writeFileSync(FILES.siteConfig, JSON.stringify(CONFIG_TPL, null, 2));

    created = true;
  }

  return created;
}

/**
 * Writes the session cookie files that will be used to log in as different users
 *
 * It uses the [`drush uli`](https://drushcommands.com/drush-7x/user/user-login/)
 * command to generate a one-time login url, the browser then go to that url
 * which then creates the session cookie
 *
 * The cookie is then stored in a json file which is used by the BackstopJS scenarios
 * to log in
 *
 * @return {Promise}
 */
async function writeCookies () {
  const cookiesDir = path.join(BACKSTOP_DIR, 'cookies');
  const config = siteConfig();

  !fs.existsSync(cookiesDir) && fs.mkdirSync(cookiesDir);

  await Promise.all(USERS.map(async user => {
    let cookieFilePath = path.join(cookiesDir, `${user}.json`);
    let loginUrl = execSync(`drush uli --name=${user} --uri=${config.url} --browser=0`, { encoding: 'utf8' });

    let browser = await puppeteer.launch();
    let page = await browser.newPage();
    await page.goto(loginUrl);
    let cookies = await page.cookies();

    fs.existsSync(cookieFilePath) && fs.unlinkSync(cookieFilePath);
    fs.writeFileSync(cookieFilePath, JSON.stringify(cookies));

    await browser.close();
  }));
}
