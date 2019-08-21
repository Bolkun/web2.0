/* eslint-disable strict */
/* global casper */

require('module/start');
var capture = require('module/capture');

// Dateien:
var fileNameApfel = 'TestDaten/RECOscan_Apfelkuchen_Durchgang1.csv';
var fileNameRoeteln = 'TestDaten/RECOscan_Roeteln_0801_068.csv';

// als QD-Labor Maskieren
casper.thenOpen(baseSiteUrl + 'extranet/jahresauswertung?maskiert_als=107');
casper.thenOpen(baseSiteUrl + 'intranet/durchgang/recoscan_explanation');
casper.thenEvaluate(function () {
  jQuery('[name="selected_rundversuch"]').val('14').change();
});
casper.then(function () {
  casper.fill('#reco-scan-csv-column-explanation-form', {
    durchgangsnummer: 1
  }, true);
});
casper.thenClick('#edit-submit');
casper.wait(2000);
capture('01 Durchgang ausgewaehlt');

function uploadToRecoScan(fileName, RVP) {
  casper.thenOpen(baseSiteUrl + 'intranet/durchgang/recoscan_upload');
  casper.thenEvaluate(function (RVP) {
    var option = jQuery('option:contains(' + RVP + ')');
    option.prop('selected', true).change();
  }, RVP);
  casper.then(function () {
    this.page.uploadFile('input[type="file"]', fileName);
  });
  casper.wait(1000);
}

// RecoScan CSV-Upload
// RecoScan-Formular vorbereiten
uploadToRecoScan(fileNameApfel, '1. Apfelkuchen');

capture('20 Formular ausgefuellt ohne Rechte');
casper.thenClick('#edit-submit');
capture('21 Formular abgeschickt ohne Rechte');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/47');
capture('30 Dateneingabe QD-Labor - nach RecoScan');

uploadToRecoScan(fileNameApfel, '1. Apfelkuchen');

casper.thenClick('#edit-ueberschreiben-recoscan');
casper.thenClick('#edit-ueberschreiben-extranet');
casper.thenClick('#edit-ueberschreiben-rohdaten');

capture('40 Formular ausgefuellt mit Rechten');
casper.thenClick('#edit-submit');
capture('41 Formular abgeschickt mit Rechten');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/47');
capture('50 Dateneingabe QD-Labor - nach RecoScan');

// Roeteln-Antikoerper
casper.thenOpen(baseSiteUrl + 'intranet/durchgang/recoscan_explanation');
casper.thenEvaluate(function () {
  jQuery('[name="selected_rundversuch"]').val('8').change();
});
casper.wait(2000);
casper.then(function () {
  casper.fill('.reco-scan-csv-column-explanation-form', {
    durchgangsnummer: 68
  }, true);
});
casper.thenClick('#edit-submit');
casper.wait(2000);
capture('60 - Roeteln-Antikoerper Durchgang ausgewaehlt');

// RecoScan CSV-Upload
uploadToRecoScan(fileNameRoeteln, '68. Roeteln-Antikoerper');

// Da nun die letzte einstellung gespeichert wird, wieder hacken entfernen
casper.thenClick('#edit-ueberschreiben-recoscan');
casper.thenClick('#edit-ueberschreiben-extranet');
casper.thenClick('#edit-ueberschreiben-rohdaten');
capture('70 - Roeteln-Antikoerper Formular ausgefuellt ohne Rechte');
casper.thenClick('#edit-submit');
capture('71 - Roeteln-Antikoerper Formular abgeschickt ohne Rechte');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/48');
capture('80 - Roeteln-Antikoerper Dateneingabe QD-Labor - nach RecoScan');

uploadToRecoScan(fileNameRoeteln, '68. Roeteln-Antikoerper');

casper.thenClick('#edit-ueberschreiben-recoscan');
casper.thenClick('#edit-ueberschreiben-extranet');
casper.thenClick('#edit-ueberschreiben-rohdaten');

capture('90 - Roeteln-Antikoerper Formular ausgefuellt mit Rechten');
casper.thenClick('#edit-submit');
capture('91 - Roeteln-Antikoerper Formular abgeschickt mit Rechten');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/48');
capture('92 - Roeteln-Antikoerper Dateneingabe QD-Labor - nach RecoScan');

casper.run();
