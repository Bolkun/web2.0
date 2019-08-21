/* global casper */

require('module/start');
var capture = require('module/capture');

// Datei:
var fileName = 'TestDaten/RECOscan_Metrisches_RVP_mit_absoluten_AG.csv';

casper.thenOpen(baseSiteUrl + 'intranet/durchgang/recoscan_upload');
casper.waitForUrl(baseSiteUrl + 'intranet/durchgang/recoscan_upload');
casper.then(function () {
  casper.fill('#rundversuchsprogramm_select_form', {
    'selected_rundversuch': '14'
  }, false);
  casper.waitForSelector('#reco-scan-upload-form');
});

casper.then(function () {
  casper.fill('#reco-scan-upload-form', {
    'files[csvdateiupload]': fileName,
    'ueberschreiben_recoscan': true,
    'ueberschreiben_extranet': true,
    'ueberschreiben_rohdaten': true
  }, false);
  capture('RECOscan_upload');
  casper.thenClick('#edit-submit');
});

capture('RECOscan_upload_submited');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/2?maskiert_als=25');
casper.waitForUrl(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/2?maskiert_als=25');

capture('Analyt_Proben');

casper.run();
