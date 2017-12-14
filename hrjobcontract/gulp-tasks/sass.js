var exec = require('child_process').exec;

module.exports = function (SubTask) {
  return function (cb) {
    exec('compass compile', { cwd: __dirname + '/../' }, function (_, stdout, stderr) {
      console.log(stdout);
      cb();
    });
  };
};
