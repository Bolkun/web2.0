/* global casper */

require('module/start');
var capture = require('module/capture');
var rohdatenImport = require('module/rohdatenImport');

var fileNameExcelRoeteln = 'TestDaten/RohdatenImport_Roeteln.xls';

// URL:
var urlRohdatenImport = baseSiteUrl + 'intranet/durchgang/import_rohdaten';

// Roeteln-Antikoerper
// Neuen Durchgang erstellen (RVP "Roeteln-Antikoerper" hat ID=8)
casper.then(function () { // iRVP, iDurchgang, iIndex
  rohdatenImport.erstelleNeuenDurchgang(8, 68, 210);
});

casper.then(function () { // iDurchgang, iIndex, excelFilename, bRemoveOldExcelFile
  rohdatenImport.rohdatenImportVorbereiten(48, 213, fileNameExcelRoeteln, true);
});

// Auswahl des RVP: Roeteln und des 68.Durchgangs
capture('221 Form ausgefuellt');
casper.thenClick('#edit-submit');


// Warten bis batch-skripte fertig
casper.wait(700);
casper.waitForUrl(urlRohdatenImport, null, null, 3600 * 1000);
capture('222 Form abgeschickt');

casper.then(function () {                         // iDurchgang, iIndex, iLadobID
  rohdatenImport.maskiereAlsTeilnehmerUebersichtRundversucheDateneingabe(48, 230, 107);
});

// Alles auf negativ setzten.

casper.thenClick('#edit-table-6-3-9');
casper.thenClick('#edit-table-6-4-9');
casper.thenClick('#edit-table-6-5-9');
casper.thenClick('#edit-table-6-6-9');
casper.thenClick('#edit-table-6-7-9');
//wait for autosave
casper.wait(3000);

// Alle Felder leeren
casper.then(function () {
  casper.fill('form', {
    'table[7][3]': '',
    'table[7][4]': '',
    'table[7][5]': '',
    'table[7][6]': '',
    'table[7][7]': '',
  }, true);
});

casper.waitUntilVisible('div.messages');
capture('234 Uebersicht Rundversuche QD-Labor - nach Speichern');
casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche');
capture('235 Uebersicht Rundversuche QD-Labor - nach Leeren der Felder');

// Ergebnisprotokolle anzeigen
casper.then(function () {
  rohdatenImport.gotoErgebnisprotokolle(48, 240);
});

casper.run();
