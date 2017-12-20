var exec = require('child_process').exec;

module.exports = function () {
  return {
    main: function (done) {
      exec('compass compile', { cwd: __dirname + '/../' }, function (_, stdout, stderr) {
        console.log(stdout);
        done();
      });
    }
  };
};
