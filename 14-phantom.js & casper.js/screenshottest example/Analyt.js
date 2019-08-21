require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/analyt');
casper.thenEvaluate(function () {
  var option = jQuery('option:contains(Gerinnung)');
  option.prop('selected', true).change();
});
casper.wait(500);
capture('Intranet Rundversuchsprogramme Analyte 0-Uebersicht');

casper.thenClick('a.button');
casper.waitForUrl(baseSiteUrl + 'intranet/rundversuchsprogramm/analyt/add');

casper.then(function () {
  this.fill('form', {
    'bezeichnung[0][value]': 'Auto',
    'bezeichnungbericht[0][value]': 'DasAuto',
    'inhaltsfrequenz[0][value]': 4,
    'prozentualeakzeptanzgrenze[0][value]': 25,
    'umrechnungstabelle[0][value]': 'blabla',
    'nachkommastellen[0][value]': 4,
  });
});

casper.thenEvaluate(function () {
  jQuery('#edit-skalierung-skala-snominal').click();
  jQuery('#edit-analytsperren-value').click();
  jQuery('#edit-listederattribute').val('verfahren');
});

casper.waitUntilVisible('#edit-name-der-nominalgruppe-0-value');
capture('Intranet Rundversuchsprogramme Analyte 1-nach Eingabe');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramme Analyte 2-nach Speichern');

casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/analyt/1/edit/translation');
capture('Intranet Rundversuchsprogramme Analyte 3-Uebersetzungsformular');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramme Analyte 4-nach Speichern der Uebersetzung');

casper.run();
