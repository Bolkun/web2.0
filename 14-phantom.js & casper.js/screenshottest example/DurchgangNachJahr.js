/* global casper */

require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/durchgang/nach_jahr/2017');
casper.waitForUrl(baseSiteUrl + 'intranet/durchgang/nach_jahr/2017');

capture('DurchgangNachJahr2017');

casper.then(function () {
  casper.fill('#jahr_select_form', {
    'selected_jahr': '2018'
  }, false);
});

casper.waitUntilVisible('#DataTables_Table_0');

capture('DurchgangNachJahr2018');

casper.run();
