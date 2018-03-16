module.exports = function (chromy, scenario, vp) {
  console.log('SCENARIO > ' + scenario.label);
  require('./loadCookies')(chromy, scenario);

  // IGNORE ANY CERT WARNINGS
  chromy.ignoreCertificateErrors();
};
