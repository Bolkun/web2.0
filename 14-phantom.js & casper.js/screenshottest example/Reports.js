/* global casper */

require('module/start');
var capture = require('module/capture');
var tools = require('module/tools');

casper.options.waitTimeout = 600000;

var fileIndex = 1;

function getFileName(s) {
  return ('0' + fileIndex++).slice(-2) + ' - ' + s;
}

function pdf(url) {
  var names = Array.prototype.slice.apply(arguments, [1]);
  var name = names.join(' ');
  capture(getFileName(name + ' - Status'), true);
  tools.downloadPDF(url, getFileName(name));
}

var disposable_docs = [];

var rounds = {1: 'Klinische Chemie', 2: 'Autoimmunologie'};
for (dg in rounds) {
  var url = baseSiteUrl + 'intranet/durchgang/' + dg + '/view/';

  // Kollektive aktualisieren
  casper.thenOpen(url + 'kollektivbildung');
  casper.waitForSelector('#edit-submit');
  casper.thenClick('#edit-submit');
  casper.waitForSelector('div.progress');
  casper.waitForSelector('#edit-submit', null, null, 1800000);

  // Kollektive erzeugen
  var urls = [];
  casper.then(function () {
    urls = casper.getElementsAttribute('a.kollektiv-link', 'href');
  });

  casper.then(function next() {
    var url = urls.shift();
    if (!url) {
      return;
    }
    disposable_docs.push(url.replace(/[^/]*$/, ''));

    casper.thenOpen(url);
    casper.wait(500);
    casper.thenClick('[name=create-from-occurrences]');
    casper.thenEvaluate(function() {
      jQuery('#create-from-attribute :checkbox:first').prop('checked', true).change(); 
    });
    casper.thenClick('#create-from-attribute [type=submit]');
    casper.waitWhileVisible('#create-from-attribute');

    next();
  });

  // Zielwerte einfrieren
  casper.thenOpen(url + 'pdf_erstellen');
  casper.waitForSelector('#edit-button');
  casper.thenClick('#edit-button');
  casper.waitForUrl(/zielwerte_sperren/);
  casper.thenClick('#edit-submit');

  // Gesamtbericht
  casper.waitForSelector('#edit-gaw-generieren-button');
  casper.thenClick('#edit-gaw-generieren-button');
  casper.waitForSelector('div.progress');
  casper.waitForSelector('div.messages', null, null, 3600000);
  pdf(baseSiteUrl + 'GAW/' + dg + '/pdf',
    rounds[dg], 'Gesamtauswertung');

  // Kurzbericht
  casper.thenClick('#edit-kurzbericht');
  casper.waitUntilVisible('#edit-sortierungproben');
  casper.thenClick('#edit-sortierungproben');
  casper.thenClick('#edit-gaw-generieren-button');
  casper.waitForSelector('div.progress');
  casper.waitForSelector('div.messages', null, null, 3600000);
  pdf(baseSiteUrl + 'GAW/' + dg + '/pdf',
    rounds[dg], 'Kurzbericht');

  // Individualauswertung
  casper.thenOpen(url + 'pdf_erstellen/individualauswertung');
  casper.waitForSelector('[name=t4]');
  casper.thenClick('[name=t4]');
  casper.waitForSelector('div.progress');
  casper.waitForSelector('div.messages', null, null, 1800000);
  pdf(baseSiteUrl + 'individualauswertung/' + dg + '/4/pdf',
    rounds[dg], 'Individualauswertung');
}

// Jahresbestätigungen
casper.thenOpen(baseSiteUrl + 'intranet/jahresbestaetigung_teilnehmer/2017');
casper.waitForSelector('[name=t4]');
casper.thenClick('[name=t4]');
casper.waitForSelector('div.progress');
casper.waitForSelector('div.messages');
pdf(baseSiteUrl + 'jahresbestaetigung/2017/4/pdf', 'Jahresauswertung');

// PIN-Brief
casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/1');
casper.waitForSelector('#edit-pin-brief');
pdf(baseSiteUrl + 'intranet/teilnehmerverwaltung/1/pinbrief', 'pinbrief');

// Messverfahren
casper.thenOpen(baseSiteUrl + 'intranet/attribut/messverfahren_pdf/2017');
casper.waitForSelector('[name=t4]');
casper.thenClick('[name=t4]');
casper.waitForSelector('div.progress');
casper.waitForSelector('div.messages');
pdf(baseSiteUrl + 'messverfahren/2017/4/pdf', 'Messverfahren');

casper.then(function() {
  for (var d = 0; d !== disposable_docs.length; d++)
    casper.thenOpen(disposable_docs[d], {method: 'delete'});
});

casper.run();
