/* global casper */

require('module/start');
var capture = require('module/capture');
var x = require('casper').selectXPath;

casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/analytklasse');
casper.thenEvaluate(function () {
    jQuery('[name="selected_rundversuch_subtyp"]').val(1).change();
});
capture('Intranet Rundversuchsprogramme Analytklassen 0-Uebersicht');

casper.thenClick('a.button[href="' + baseSitePath + 'intranet/rundversuchsprogramm/analytklasse/add"]');
casper.waitForUrl(baseSiteUrl + 'intranet/rundversuchsprogramm/analytklasse/add');
capture('Intranet Rundversuchsprogramme Analytklassen 1-Hinzufuegen leer');

casper.thenEvaluate(function () {
    jQuery('#edit-analytklassencode-0-value').val('256');
    jQuery('#edit-name-0-value').val('Neue Analytklasse');
    jQuery('#edit-subtyp').val(1);
});

capture('Intranet Rundversuchsprogramme Analytklassen 2-Hinzufuegen gefuellt');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramme Analytklassen 3-Hinzufuegen gespeichert');
casper.thenEvaluate(function () {
    jQuery('input[id^="edit-analyte"]').first().prop('checked', true);
});
capture('Intranet Rundversuchsprogramme Analytklassen 5-Bearbeiten vor Speichern');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramme Analytklassen 6-Bearbeiten gespeichert');

casper.run();
