require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'user/logout');
casper.waitForUrl(baseSiteUrl);
casper.then(function () {
  casper.fill('#user-login-form', {
    'name': '0002',
    'pass': '483335'
  }, true);
});

casper.thenOpen(baseSiteUrl + 'extranet/stammdaten');
casper.waitForUrl(baseSiteUrl + 'extranet/stammdaten');

capture('ExtranetStammdaten', true);

casper.run();
