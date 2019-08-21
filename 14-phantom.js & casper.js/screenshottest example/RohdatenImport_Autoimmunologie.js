/* global casper */

require('module/start');
var capture = require('module/capture');
var rohdatenImport = require('module/rohdatenImport');

// Dateien:
var fileNameExcelAutoimmunologie = 'TestDaten/405_Autoimmunologie_Rohdaten.xls';

// URL:
var urlRohdatenImport = baseSiteUrl + 'intranet/durchgang/import_rohdaten';

// Autoimmunologie
// Neues Rundversuchsprogramm erstellen
casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm');
capture('310 RVP-Uebersicht');

casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/add');
casper.then(function () {
  rohdatenImport.fillRvpForm(10, 'Autoimmunologie');
});
capture('311 RVP Autoimmunologie-nach Eingabe');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('312 RVP Autoimmunologie-nach Speichern');

// Neuen Durchgang erstellen (RVP "Autoimmunologie" hat ID=15)
casper.then(function () { // iRVP, iDurchgang, iIndex
  rohdatenImport.erstelleNeuenDurchgang(15, 1, 320);
});

casper.then(function () { // iDurchgang, iIndex, excelFilename, bRemoveOldExcelFile
  rohdatenImport.rohdatenImportVorbereiten(49, 323, fileNameExcelAutoimmunologie, true);
});

// Auswahl des RVP: Autoimmunologie und des 1.Durchgangs
capture('331 Form ausgefuellt');
casper.thenClick('#edit-submit');

// Warten bis batch-skripte fertig
casper.wait(700);
casper.waitForUrl(urlRohdatenImport, null, null, 2 * 3600 * 1000);

capture('332 Form abgeschickt');

casper.then(function () {                         // iDurchgang, iIndex, iLadobID
  rohdatenImport.maskiereAlsTeilnehmerUebersichtRundversucheDateneingabe(49, 340, 178);
});

// Ergebnisprotokolle anzeigen
casper.then(function () {
  rohdatenImport.gotoErgebnisprotokolle(49, 351);
});

// Zwei Labore etwas genauer kontrolieren
casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/49?maskiert_als=171');
capture('352 Labor-2809 Dateneingabe');

// Alle Felder fuellen
casper.then(function () {
  casper.fill('form', {
    'table[66][8]': '1',
    'table[66][9]': '2',
    'table[67][8]': '3',
    'table[67][9]': '4',
    'table[68][8]': '5',
    'table[68][9]': '6',
    'table[69][8]': '7',
    'table[69][9]': '8',
    'table[73][8]': '9',
    'table[73][9]': '10',
  }, true);
});

casper.waitUntilVisible('div.messages');
capture('353 Labor-2809 Dateneingabe gespeichert');
casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche');
capture('354 Uebersicht Rundversuche Labor-2809 - nach Fuellen der Felder');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/49?maskiert_als=170');
capture('360 Labor-1777 Dateneingabe');

// Alle Felder leeren
casper.then(function () {
  casper.fill('form', {
    'table[13][8]': '',
    'table[13][9]': '',
    'table[29][8]': '',
    'table[29][9]': '',
    'table[30][8]': '',
    'table[30][9]': '',
    'table[66][8]': '',
    'table[66][9]': '',
    'table[67][8]': '',
    'table[67][9]': '',
    'table[68][8]': '',
    'table[68][9]': '',
    'table[69][8]': '',
    'table[69][9]': '',
    'table[71][8]': '',
    'table[71][9]': '',
  }, true);
});

casper.waitUntilVisible('div.messages');
capture('361 Labor-1777 Dateneingabe gespeichert');
casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche');
capture('362 Uebersicht Rundversuche Labor-1777 - nach Fuellen der Felder');

// Ergebnisprotokolle anzeigen
casper.then(function () {
  rohdatenImport.gotoErgebnisprotokolle(49, 370);
});

casper.run();
