module.exports = async (puppet, scenario, vp) => {
  console.log('--------------------------------------------');
  console.log('Running Scenario "' + scenario.label + '" ' + scenario.count);

  await require('./loadCookies')(puppet, scenario);
};
