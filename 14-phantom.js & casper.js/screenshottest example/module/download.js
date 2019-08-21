module.exports = function (url, target) {
  var done = false;

  var session;
  var cookiename;
  for (var c = 0; c !== phantom.cookies.length; c++) {
    if (/^SESS/.test(phantom.cookies[c].name)) {
      session = phantom.cookies[c].value;
      cookiename = phantom.cookies[c].name;
      break;
    }
  }

  var proc = require('child_process').spawn('curl', [
    '--silent', '--show-error', url,
    '-b', cookiename + '=' + session,
    '-o', target,
  ]);

  proc.on('exit', function () {
    casper.log('Downloaded ' + url + ' -> ' + target);
    done = true;
  });

  proc.stderr.on('data', function (data) {
    casper.log('curl: ' + data, 'warning');
  });

  casper.waitFor(function () {
    return done;
  }, null, function () {
    casper.log('Download of ' + url + ' timed out!', 'warning');
  });
};
