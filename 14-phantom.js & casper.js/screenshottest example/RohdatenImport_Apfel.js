/* global casper */

require('module/start');
var capture = require('module/capture');
var rohdatenImport = require('module/rohdatenImport');

// Dateien:
var fileNameExcelApfel = 'TestDaten/RohdatenImport_Apfelkuchen.xls';

// URL:
var urlRohdatenImport = baseSiteUrl + 'intranet/durchgang/import_rohdaten';

// Neues Rundversuchsprogramm erstellen
casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm');
capture('110 RVP-Uebersicht');

casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/add');
casper.then(function () {
  rohdatenImport.fillRvpForm(9, 'Apfelkuchen');
});
capture('111 RVP Apfelkuchen-nach Eingabe');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('112 RVP Apfelkuchen-nach Speichern');

// Neuen Durchgang erstellen (RVP "Apfelkuchen" hat ID=14)
casper.then(function () { // iRVP, iDurchgang, iIndex
  rohdatenImport.erstelleNeuenDurchgang(14, 1, 120);
});

casper.then(function () { // iDurchgang, iIndex, excelFilename, bRemoveOldExcelFile
  rohdatenImport.rohdatenImportVorbereiten(47, 123, fileNameExcelApfel, false);
});

// Auswahl des RVP: Apfelkuchen und des 1.Durchgangs
capture('131 Form ausgefuellt');
casper.thenClick('#edit-submit');

// Warten bis batch-skripte fertig
casper.wait(700);
casper.waitForUrl(urlRohdatenImport, null, null, 3600 * 1000);

capture('132 Form abgeschickt');

casper.then(function () {                         // iDurchgang, iIndex, iLadobID
  rohdatenImport.maskiereAlsTeilnehmerUebersichtRundversucheDateneingabe(47, 140, 107);
});

// Alle M-Nominalen Felder leeren
casper.thenClick('#edit-table-5-1-5');
casper.thenClick('#edit-table-5-1-6');
casper.thenClick('#edit-table-5-1-7');

casper.thenClick('#edit-table-5-2-5');
casper.thenClick('#edit-table-5-2-6');

//wait for autosave
casper.wait(3000);

// fill(String selector, Object values[, Boolean submit])
casper.then(function () {
  casper.fill('form', {
    'table[1][1]': '',
    'table[1][2]': '',
    'table[2][1]': '',
    'table[2][2]': '',
    'table[3][1]': '',
    'table[3][2]': '',
  }, true);
});

casper.waitUntilVisible('div.messages');
capture('143 Uebersicht Rundversuche QD-Labor - nach Speichern');
casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche');
capture('144 Uebersicht Rundversuche QD-Labor - nach Leeren der Felder');

// Ergebnisprotokolle anzeigen
casper.then(function () {
  rohdatenImport.gotoErgebnisprotokolle(47, 150);
});

casper.run();
