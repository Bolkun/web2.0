require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung');
casper.waitForText('Institution 1');
casper.thenEvaluate(function() {
    // nach Teilnehmer 9 suchen
    jQuery('input.col-search:eq(1)').val('9').change();
});
casper.waitForText('Institution 19');
capture('Intranet Teilnehmerverwaltung nach 9 gesucht');

// Suche wieder aufheben
casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung');
casper.waitForText('Institution 10');
casper.thenEvaluate(function() {
    // Teilnehmer 6 und 8 auswählen
    jQuery('table :checkbox:eq(5), table :checkbox:eq(7)').click();
});
capture('Intranet Teilnehmerverwaltung 2 Teilnehmer angehakt');

casper.thenClick('input[value="Angehakte Teilnehmer in E-Mail-Versandliste aufnehmen"]');
casper.waitForUrl(baseSiteUrl + 'intranet/teilnehmerverwaltung/email_versandliste_form');

casper.thenEvaluate(function() {
    // Teilnehmerliste aufklappen
    jQuery('#edit-teilnehmer-in-der-liste summary').click();
});
casper.wait(1500); // warten, bis die Animation abgeschlossen ist
capture('Intranet Teilnehmerverwaltung E-Mail-Versandliste');

casper.thenClick('input#edit-editlist');
casper.waitUntilVisible('#edit-teilnehmer-target-id');
capture('Intranet Teilnehmerverwaltung E-Mail-Versandliste Test-Mailing');

casper.thenEvaluate(function() {
    // die 2 neuen Teilnehmer 6 und 8 wieder entfernen
    jQuery('input#edit-teilnehmer-target-id').val('Institution 2 (2), Institution 3 (3), Institution 4 (4), Institution 5 (5)');
    jQuery('input#edit-betreff-0-value').val('Test-Mailing');
    jQuery('textarea#edit-body-0-value').val('kwt');
});
capture('Intranet Teilnehmerverwaltung E-Mail-Versandliste Test-Mailing gefuellt');
casper.thenClick('input#edit-submit');
casper.waitForUrl(baseSiteUrl + 'intranet/teilnehmerverwaltung/email_versandliste');
casper.waitUntilVisible('div.region-content table');
capture('Intranet Teilnehmerverwaltung E-Mail-Versandliste Liste');

casper.run();
