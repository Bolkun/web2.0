/* global casper */

require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'user/logout');
casper.wait(3000);
casper.then(function () {
  this.fill('form', {
    name: 'admin',
    pass: 'admin',
  });
});
casper.thenClick('#edit-submit');
casper.waitForUrl(baseSitePath + 'extranet');

casper.thenOpen(baseSiteUrl + 'update.php/run');
casper.waitForUrl(/update.php\/start/, null, function () {
  capture('db-update-error01');
}, 30000);
casper.waitForUrl(/update.php\/results$/, null, function () {
  capture('db-update-error02');
}, 120000);

casper.thenOpen(baseSiteUrl + 'entup.php/run');
casper.waitForUrl(/entup.php\/start/, null, function () {
  capture('db-update-error03');
}, 30000);
casper.waitForUrl(/entup.php\/results$/, null, function () {
  capture('db-update-error04');
}, 120000);

casper.run();
