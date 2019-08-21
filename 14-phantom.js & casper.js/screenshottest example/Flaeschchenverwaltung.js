require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/probenoption/flasche');
capture('0-Uebersicht');

casper.thenClick('.region-content a[href$=add]');
casper.then(function() {
  casper.fill('.flasche-form', {
    'rundversuch': '3',
    'name[0][value]': 'Flaeschchen 2 fuer Gerinnung',
  });
});
capture('1-vor dem Speichern');

casper.thenClick("#edit-submit");
casper.waitUntilVisible('div.messages--status');
capture('2-nach dem Speichern');

casper.thenClick('#edit-analyt-tabelle-36-drin');
casper.thenClick('#edit-analyt-tabelle-38-drin');
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages--status');
casper.thenClick('table tbody tr:last-child [href$=edit]');
capture('3-mit Analyt TPZ und aPTT im Flaeschchen');

casper.run();
