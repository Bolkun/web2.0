/* eslint-disable strict */
/* global casper */

require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/analyt');
casper.thenClick('a.button');
casper.waitForUrl(baseSiteUrl + 'intranet/rundversuchsprogramm/analyt/add');
casper.then(function () {
  this.fill('form', {
    'bezeichnung[0][value]': 'Hepatitis',
    'skalierung': 'skala_snominal'
  });
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('1 - Analyt Hepatitis angelegt');

casper.thenClick('a.button');
casper.waitForUrl(/rundversuchsprogramm.analyt.\d+.edit.nominale_antworten.add/);
casper.then(function () {
  this.fill('form', {
    'name[0][value]': 'negativ'
  });
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');

casper.thenClick('a.button');
casper.waitForUrl(/rundversuchsprogramm.analyt.\d+.edit.nominale_antworten.add/);
casper.then(function () {
  this.fill('form', {
    'name[0][value]': 'positiv'
  });
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('2 - Nominale Antwort Hepatitis positiv angelegt');

casper.thenClick('div.messages a');
casper.waitUntilVisible('a[href$=translation]');
casper.thenClick('a[href$=translation]');
casper.thenEvaluate(function () {
  var suffix = 1;
  jQuery('input[type=text]').each(function () {
    jQuery(this).val('Text ' + suffix);
    suffix++;
  });
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('3 - Uebersetzungen gespeichert');

casper.run();
