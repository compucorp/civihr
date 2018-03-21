module.exports = function (chromy, scenario, vp) {
  console.log('--------------------------------------------');
  console.log('Running Scenario "' + scenario.label + '" ' + scenario.count);

  require('./loadCookies')(chromy, scenario);

  // IGNORE ANY CERT WARNINGS
  chromy.ignoreCertificateErrors();
};
