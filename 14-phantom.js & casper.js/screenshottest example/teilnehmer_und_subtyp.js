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
capture('Intranet Subtypen Teilnehmer hinzufuegen 0-Uebersicht');

// Das wird nicht klappen, weil noch keine Chechbox angehakt ist
casper.thenClick('input#edit-submit');
casper.waitForSelector('.messages--error');
capture('Intranet Subtypen Teilnehmer hinzufuegen 1-kein Teilnehmer hinzu');

// erste Zeile anhaken
casper.thenOpen(baseSiteUrl + 'intranet/subtyp/block_teilnehmer_hinzu');
casper.thenEvaluate(function () {
    jQuery('tbody tr:eq(0) td:eq(0)>>input').prop('checked', true);
});
capture('Intranet Subtypen Teilnehmer hinzufuegen 2-erste Zeile angehakt');

casper.thenClick('input#edit-submit');
casper.waitForSelector('.messages--status');
tools.black_cells_with_currentdate();
capture('Intranet Subtypen Teilnehmer hinzufuegen 3-nach dem Hinzufuegen von einem Teilnehmer');

casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/1/teilnahmen_rvp');
capture('Intranet teilnehmerverwaltung teilnahmen_rvp');

casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/1/teilnahmen_subtyp');
casper.thenEvaluate(function() {
    var option = jQuery('option:contains(Hämatologie)');
    option.prop('selected', true).change();
});
casper.wait(1000);
capture('Intranet Teilnehmerverwaltung teilnahmen_subtyp');

casper.run();
