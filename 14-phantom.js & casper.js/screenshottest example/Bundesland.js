require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/globale_einstellungen/bundesland');
capture('Globale Einstellungen Bundeslaender 0-Uebersicht');

casper.thenClick('a.button');
casper.waitForUrl(baseSiteUrl + 'intranet/globale_einstellungen/bundesland/add');
capture('Globale Einstellungen Bundeslaender 1-Neues Bundesland Formular empty');

casper.then(function () {
  this.fill('form', {
    'name[0][value]': 'Sachsen',
    'name_kurz[0][value]': 'SN',
  });
});
casper.thenEvaluate(function () {
  jQuery('#edit-land').val('deutschland').change();
});
capture('Globale Einstellungen Laender 2-Neues Bundesland Formular filled');

casper.thenClick('#edit-submit');
casper.waitForUrl(baseSiteUrl + 'intranet/globale_einstellungen/bundesland');
capture('Globale Einstellungen Bundeslaender 3-Uebersicht nach dem Anlegen');

casper.run();
