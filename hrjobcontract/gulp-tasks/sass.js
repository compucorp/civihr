var exec = require('child_process').exec;

module.exports = function () {
  return {
    // @NOTE this will replace the original "sass:main" task, as job contract
    // needs a completely different logic for compiling sass
    main: function (done) {
      exec('compass compile', { cwd: __dirname + '/../' }, function (_, stdout, stderr) {
        console.log(stdout);
        done();
      });
    }
  };
};
