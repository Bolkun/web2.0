/* global casper */

require('module/start');
var capture = require('module/capture');

//Zufällige Hauptadressen generieren um den Durchgang starten zu können
casper.thenOpen(baseSiteUrl + 'user/logout');
casper.wait(3000);
casper.then(function () {
  this.fill('form', {
    name: 'admin',
    pass: 'admin',
  });
});
casper.thenClick('#edit-submit');
casper.waitForSelector('a[href="' + baseSitePath + 'extranet"]');
casper.thenOpen(baseSiteUrl + 'admin/config/teilnehmerverwaltung/adressen_generieren');
casper.waitUntilVisible('#edit-submit.button--danger', null, null, 10000);
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages', null, null, 30000);

//Durchgang starten
casper.thenOpen(baseSiteUrl + 'intranet/durchgang/7/view/matching_und_versandstart/1');
casper.waitForSelector('#edit-proben-speichern-button');
casper.thenEvaluate(function () {
  jQuery('#edit-probenoptionen-table-1-drin').prop('checked', false);
  jQuery('#edit-probenoptionen-table-2-drin').prop('checked', false);
  jQuery('#edit-probenoptionen-table-3-drin').prop('checked', true);
});
casper.thenClick('#edit-proben-speichern-button');
casper.waitUntilVisible('div.messages--error');
casper.thenEvaluate(function () {
  jQuery('#edit-probenoptionen-table-1-drin').prop('checked', false);
  jQuery('#edit-probenoptionen-table-2-drin').prop('checked', false);
  jQuery('#edit-probenoptionen-table-3-drin').prop('checked', true);
});
casper.thenClick('#edit-proben-speichern-button');
casper.waitForSelector('#edit-button', null, null, 30000);
casper.thenClick('#edit-button');
casper.waitForSelector('#edit-button');

//Dateneingabe von Teilnehmer 0013 anschauen
casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/7?maskiert_als=13');
casper.waitUntilVisible('#edit-table');
capture('01 - Alle Analyte');

//Abmeldung von 2 archivierten Analyten
casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/13/angemeldete_analyte_aendern/7');
casper.thenClick('#edit-table-2-angemeldet-');
casper.thenClick('#edit-table-3-angemeldet-');
casper.thenClick('#edit-submit');
capture('02 - Abmeldung');

//Dateneingabe von Teilnehmer 0013 nach Abmeldung
casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/7?maskiert_als=13');
casper.waitUntilVisible('#edit-table');
capture('03 - Analyten abgemeldet');

//Wieder anmelden
casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/13/angemeldete_analyte_aendern/7');
casper.thenClick('#edit-table-2-angemeldet-');
casper.thenClick('#edit-table-3-angemeldet-');
casper.thenClick('#edit-submit');
capture('04 - Anmeldung');

casper.run();
