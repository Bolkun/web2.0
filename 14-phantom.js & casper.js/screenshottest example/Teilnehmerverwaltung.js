/* global casper */

require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung');
casper.waitUntilVisible('input[name="selected_rows[]"]', null, null, 20000);
capture('Intranet Teilnehmerverwaltung 0-Uebersicht');

casper.thenClick('a.button-action');
casper.waitUntilVisible('a[href*=institution]');
casper.thenClick('a[href*=institution]');
casper.waitForUrl(baseSiteUrl + 'intranet/teilnehmerverwaltung/add?art=institution');
casper.then(function () {
  this.fill('form', {
    'labornummer[0][value]': 1234,
    'institution_name[0][value]': 'QuoData',
    'pin[0][value]': '111111'
  });
});
casper.thenEvaluate(function () {
  jQuery('#edit-teilnehmertyp').val('Institut').change();
  jQuery('#edit-verrechnungstyp').val('mitglied').change();
  jQuery('#edit-rechnungsversandart').val('Sonderfall').change();
});

// Es kommt Warnmeldung, weil die leere Mitglied-Chechbox sich mit dem
// Verrechnungstyp widerspricht:
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div[role=contentinfo]');
capture('Intranet Teilnehmerverwaltung 1-Fehler-kein Mitglied');

// Warnmeldung beseitigen:
casper.thenEvaluate(function setzeNormaltarif() {
  jQuery('#edit-verrechnungstyp').val('normal').change();
});
casper.thenClick('#edit-hauptadresse');
casper.waitUntilVisible('input[name=strasse_1]');
casper.then(function () {
  this.fill('form', {
    'institution_1': 'QuoData',
    'strasse_1': 'Prellerstr. 14',
    'plz_1': '01309',
    'ort_1': 'Dresden',
    'telefon2_1': '351',
    'telefon3_1': '102030'
  });
});
casper.thenEvaluate(function () {
  jQuery('#edit-anrede-1').val('Herr').change();
});

capture('Intranet Teilnehmerverwaltung 1-Neue Institution filled');

casper.thenClick('#edit-submit');
casper.wait(2000);
casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung');
casper.waitUntilVisible('input[name="selected_rows[]"]', null, null, 20000);
capture('Intranet Teilnehmerverwaltung 0-Uebersicht nach dem Anlegen');

casper.run();
