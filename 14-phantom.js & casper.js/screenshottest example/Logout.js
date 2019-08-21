/* global casper */

require('module/start');
var capture = require('module/capture');
casper.thenEvaluate(function () {
    jQuery('.profile').hide();
});
capture('Admin Welcome Page');

casper.thenOpen(baseSiteUrl + 'user/logout');
casper.waitForUrl(baseSiteUrl);
capture('Login Page');

casper.run();
