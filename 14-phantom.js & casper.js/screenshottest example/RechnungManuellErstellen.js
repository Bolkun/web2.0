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
capture('00 - vor-dem-Anlegen');
casper.thenClick('#edit-submit');

casper.waitForSelector('time');
capture('01 - nach-dem-Anlegen', true);

// 1. Position anlegen
casper.thenClick('#edit-button');
casper.waitForText('Enthaltener Rabatt [%]');
casper.then(function () {
    casper.fill('#rechnungsposition-add-form', {
        'name[0][value]': 'So heißt die Position',
        'betrag[0][value]': '43'
    });
});

capture('02 - Position hinzu - vor dem Speichern', true);

casper.thenClick('#edit-submit');
casper.waitForSelector('#edit-button');
capture('03 - genau eine Position - nach dem Speichern', true);


// 2. Position anlegen
casper.thenClick('#edit-button');
casper.waitForText('Enthaltener Rabatt [%]');
casper.then(function () {
    casper.fill('#rechnungsposition-add-form', {
        'name[0][value]': 'Dreißig Euro Aufschlag',
        'betrag[0][value]': '30'
    });
});
casper.thenClick('#edit-submit');
casper.waitForSelector('#edit-button');
capture('04 - zwei Positionen', true);

casper.thenClick('#edit-als-verschickt-markieren');
casper.waitForSelector('.messages--status a');
casper.thenClick('.messages--status a');
casper.waitForText('Teilnehmer, für den diese Rechnung ausgestellt wurde');
casper.thenEvaluate(function () {
    jQuery('#edit-verschickt-am-0-value-date').val('2017-04-02');
});
capture('05 - nach dem Versand', true);

casper.then(function () {
    casper.fill('#rechnung-edit-form', {
        'ausgleichsdatum[0][value][date]': '2037-12-31'
    }, true);
});
casper.waitForSelector('.messages--status a');
casper.thenClick('.messages--status a');
casper.waitForText('Teilnehmer, für den diese Rechnung ausgestellt wurde');
casper.thenEvaluate(function () {
    jQuery('#edit-verschickt-am-0-value-date').val('2017-04-02');
});
capture('06 - nach dem Begleichen', true);
casper.options.waitTimeout = 600000;
tools.downloadPDF(baseSiteUrl + 'intranet/rechnung/1/pdf', '08 - Rechnung PDF');
tools.downloadPDF(baseSiteUrl + 'intranet/mahnung-generieren/1/1/pdf', '09 - Mahnung1 PDF');
tools.downloadPDF(baseSiteUrl + 'intranet/mahnung-generieren/1/2/pdf', '10 - Mahnung2 PDF');
tools.downloadPDF(baseSiteUrl + 'intranet/mahnung-generieren/1/3/pdf', '11 - Mahnung3 PDF');

casper.thenClick('.region-content [href$=edit]');
casper.thenClick('[href$=rechnungen]');
casper.waitWhileVisible('td.empty.message');
tools.black_cells_with_currentdate();
capture('07 - Rechnungen des Klinikums Wiender Neustadt', true);

casper.run();
