require('module/start');
var capture = require('module/capture');

casper.thenOpen([
  baseSiteUrl + 'intranet/attribut',
  'posteingang_neue_auspraegungen',
  'mailversand_bei_neuen_messverfahren',
].join('/'));
capture('0-Uebersicht');

casper.thenEvaluate(function() {
  jQuery(':checkbox[id*=edit-e-mail-empfaenger]').first().prop('checked', true);
  jQuery(':checkbox[id*=edit-e-mail-empfaenger]').last().prop('checked', true);
});
casper.thenClick('#edit-submit');
capture('1-erster und letzter ausgewaehlt');

casper.thenEvaluate(function() {
  jQuery(':checkbox').prop('checked', false);
});
casper.thenClick('#edit-submit');

casper.run();
