/* global casper */

require('module/start');
var capture = require('module/capture');
var tools = require('module/tools');

casper.thenOpen(baseSiteUrl + 'intranet/subtyp/block_teilnehmer_hinzu');
casper.thenEvaluate(function () {
  // Hämatologie Subtyp 2 auswählen
  jQuery('#rundversuchsprogramm_subtyp_select_form [name="selected_rundversuch_subtyp"]').val(2).change();
});
casper.wait(4000);
capture('0-Teilnehmerliste');

// Abschicken ohne anhaken sollte Fehlermeldung "Sie haben keinen Teilnehmer ausgewählt" produzieren:
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('1-Teilnehmerliste-mit-Fehler');

casper.thenOpen(baseSiteUrl + 'intranet/subtyp/block_teilnehmer_hinzu');
casper.thenEvaluate(function () {
    // alle 10 Teilnehmer der ersten Seite anhaken
    jQuery('[name*=selected_row]').prop('checked', true);
    jQuery('#edit-wann-radios-zukunft').click();
    jQuery('#edit-anmeldedatum').val('2019-10-21').change().trigger('input').blur();
});
capture('2-Teilnehmerliste vor dem Abschicken');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages', null, null, 20000);
tools.black_cells_with_currentdate();
capture('3-nach dem Abschicken');

casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/23/teilnahmen_subtyp');
casper.waitForSelector('#edit-table input[type=date]');
casper.thenEvaluate(function () {
    jQuery('#edit-table input[type=date]').val('2019-10-21').change().trigger('input').blur();
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('3b-Abmeldung vorher');

casper.thenOpen(baseSiteUrl + 'user/logout');
casper.then(function () {
  this.fill('form', {
    name: 'admin',
    pass: 'admin'
  });
});
casper.thenClick('#edit-submit');
casper.wait(4000);
casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/geplante_feldanpassung/geplante_anpassungen_ausfuehren');
casper.waitUntilVisible('#edit-simuliertes-heutiges-datum');
capture('4a-nach-login');
casper.thenEvaluate(function () {
  jQuery('#edit-simuliertes-heutiges-datum').val('2019-10-21').change().trigger('input').blur();
});
capture('4-geplante_anpassungen_ausfuehren-vor dem Test');

casper.thenClick('#edit-submit');
casper.options.waitTimeout = 30000;
casper.waitUntilVisible('div.messages');
capture('5-geplante_anpassungen_ausfuehren-nach dem Test');

casper.thenOpen(baseSiteUrl + 'intranet/subtyp/block_teilnehmer_hinzu');
casper.thenEvaluate(function () {
  // Hämatologie Subtyp 2 auswählen
  jQuery('#rundversuchsprogramm_subtyp_select_form [name="selected_rundversuch_subtyp"]').val(2).change();
});
casper.wait(4000);
tools.black_cells_with_currentdate();
capture('6-Teilnehmerliste nach dem Test - sollte leer sein');

casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/23/teilnahmen_subtyp');
casper.waitForSelector('#edit-table input[type=checkbox]');
casper.thenEvaluate(function () {
  // Hämatologie
  jQuery('[name="selected_rundversuch"]').val(2).change();
});
casper.wait(4000);
capture('7-Abmeldung nachher');

casper.run();
