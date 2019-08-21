/* eslint-disable strict */
/* global casper */

require('module/start');
var capture = require('module/capture');
var tools = require('module/tools');

casper.thenOpen(baseSiteUrl + 'intranet/rechnung');
casper.waitWhileVisible('td.empty.message');
tools.black_cells_with_currentdate();
capture('0 - Uebersicht');

casper.thenClick('.button[href*=jahresrechnungen]');
casper.waitForUrl(baseSiteUrl + 'intranet/rechnung/jahresrechnungen');
capture('1 - vor-dem-Anlegen', true);

casper.thenClick('#edit-submit');
casper.waitForSelector('.messages--status', null, null, 1e6);
capture('2 - nach-dem-Anlegen', true);

casper.thenOpen(baseSiteUrl + 'intranet/rechnung/4/edit');
capture('3 - eine Rechung', true);

casper.then(function () {
  var urls = casper.evaluate(function () {
    return jQuery('a[href*="edit/"]:contains("Bearbeiten")').map(function () {
      return jQuery(this).prop('href');
    }).get();
  });

  var sUrlMitgliedsbeitrag = urls[1];
  var sUrlTeilnahme = urls[2];

  casper.thenOpen(sUrlMitgliedsbeitrag);
  capture('4 - Mitgliedsbeitrag', true);

  casper.thenOpen(sUrlTeilnahme);
  capture('5 - Position Teilnahme an einem RV', true);
});

casper.thenOpen(baseSiteUrl + 'intranet/rechnung/je_subtyp');
casper.thenEvaluate(function () {
  var option = jQuery('option:contains("Gerinnung: Subtyp F3")');
  option.prop('selected', true).change();
});
casper.waitForText('Bearbeiten');
capture('7 - Rechnungen fuer Gerinnung Subtyp F3');

casper.run();
