exports.config = {
  seleniumAddress: 'http://localhost:4444/wd/hub',
  beforeLaunch: 'lib/init.js',
  specs: ['*-spec.js']
};
