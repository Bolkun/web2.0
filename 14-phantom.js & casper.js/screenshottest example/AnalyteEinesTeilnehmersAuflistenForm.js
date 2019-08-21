/* global casper */

require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/13/angemeldete_analyte_aendern');
capture('Analytliste 0-Uebersicht');

// Festlegen zukünftiger Analyte
casper.thenClick('#edit-table-36-angemeldet-');
casper.thenClick('#edit-table-37-angemeldet-');
casper.then(function() {
    casper.fill('#angemeldete-analyte-eines-teilnehmers-aender-form', {
        'table[36][abmelden_____]': '2030-12-31'
    }, true);
});
casper.waitForSelector('.messages__list');
capture('Analytliste nach dem Speichern', true);

casper.run();
