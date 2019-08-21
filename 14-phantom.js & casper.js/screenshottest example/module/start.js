/* eslint-disable strict */
/* global casper */

casper.options.verbose = true;
casper.options.logLevel = 'debug';

casper.on('page.error', function (msg) {
  var url = casper.getCurrentUrl();
  casper.echo('[warning] [DOM] ' + msg + ' (' + url + ')');
});

casper.on('remote.message', function (msg) {
  var url = casper.getCurrentUrl();
  casper.echo('[remote.message] ' + msg + ' (' + url + ')');
});

baseSiteHost = 'http://localhost';
baseSitePath = '/oequasta/web/';
baseSiteUrl = baseSiteHost + baseSitePath;
casper.start(baseSiteUrl);

casper.viewport(1024, 768);

casper.then(function () {
  this.fill('form', {
    name: 'organizer',
    pass: 'trillerhasen'
  });
});

casper.thenClick('#edit-submit');
casper.waitForUrl(baseSitePath + 'extranet');
