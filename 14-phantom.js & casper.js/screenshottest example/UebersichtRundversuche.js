require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/43/teilnahmen_subtyp');
casper.thenEvaluate(function() {
  var option = jQuery('option:contains(Gerinnung)');
  option.prop('selected', true).change();
});
casper.waitUntilVisible('#edit-table-4-angemeldet-');
casper.thenClick('#edit-table-4-angemeldet-');
casper.thenClick('#edit-submit');
capture('1-Hospital Hohenems von F3 abgemeldet');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche');
capture('2-Uebersicht ohne Gerinnung 117');

casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/43/teilnahmen_subtyp');
casper.thenClick('#edit-table-4-angemeldet-');
casper.thenClick('#edit-submit');
capture('3-Hospital Hohenems an F3 angemeldet');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche');
capture('4-Uebersicht mit Gerinnung 117');

casper.run();
