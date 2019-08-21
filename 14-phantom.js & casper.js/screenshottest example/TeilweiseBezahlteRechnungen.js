/* global casper */

require('module/start');
var capture = require('module/capture');
var tools = require('module/tools');

casper.thenOpen(baseSiteUrl + 'intranet/rechnung/add');
casper.thenEvaluate(function () {
  jQuery('#edit-teilnehmer-0-target-id').val('QuoData (107)').change();
});
casper.then(function () {
  casper.wait(2000);
});
casper.thenClick('#edit-submit');

// Position anlegen
casper.waitForSelector('#edit-button');
casper.thenClick('#edit-button');
casper.waitForText('Enthaltener Rabatt [%]');
casper.then(function () {
  casper.fill('#rechnungsposition-add-form', {
    'name[0][value]': 'Ist gleichzeitig Gesamtbetrag',
    'betrag[0][value]': '100'
  });
});

casper.thenClick('#edit-submit');

// Speichern
casper.waitForText('Teilnehmer, für den diese Rechnung ausgestellt wurde');
casper.thenClick('#edit-als-verschickt-markieren');
casper.waitForSelector('.messages--status a');
casper.thenClick('.messages--status a');

casper.then(function () {
  casper.fill('#rechnung-edit-form', {
    'gezahlter_betrag[0][value]': '50'
  }, true);
});
casper.waitForSelector('.messages--status a');
casper.thenClick('.messages--status a');
casper.waitForText('Teilnehmer, für den diese Rechnung ausgestellt wurde');
casper.thenEvaluate(function () {
    jQuery('#edit-verschickt-am-0-value-date').val('2017-04-02');
});
capture('00 - Neue Rechnung', true);

casper.thenOpen(baseSiteUrl + 'intranet/rechnung/teilbezahlte_rechnungen');
casper.waitForText('1234');
tools.black_cells_with_currentdate();
capture('01 - Uebersicht teilbezahlter Rechnungen');

// Rechnung begleichen
casper.back();
casper.waitForText('Teilnehmer, für den diese Rechnung ausgestellt wurde');
casper.then(function () {
  casper.fill('#rechnung-edit-form', {
    'gezahlter_betrag[0][value]': '100',
    'ausgleichsdatum[0][value][date]': '2037-12-31'
  }, true);
});
casper.waitForSelector('.messages--status a');
casper.thenClick('.messages--status a');
casper.waitForText('Teilnehmer, für den diese Rechnung ausgestellt wurde');
casper.thenEvaluate(function () {
    jQuery('#edit-verschickt-am-0-value-date').val('2017-04-02');
});
capture('02 - Beglichene Rechnung', true);

// Prüfen, dass Rechnung nicht mehr gezeigt wird
casper.thenOpen(baseSiteUrl + 'intranet/rechnung/teilbezahlte_rechnungen');
casper.waitForText('Keine Daten'); // Kritische Stelle: Wenn durch anderen Test
// bereits teilweise bezahlte Rechnungen eingefügt wurden, gibts hier Fehler!
capture('03 - Beglichene Rechnung nicht mehr gezeigt');
casper.run();
