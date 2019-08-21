/* global casper */

require('module/start');
var capture = require('module/capture');
var tools = require('module/tools');
var x = require('casper').selectXPath;

casper.thenOpen(baseSiteUrl + 'intranet/subtyp');
casper.thenEvaluate(function () {
    jQuery('[name="selected_rundversuch"]').val(1).change();
});
capture('Intranet Rundversuchsprogramme Subtypen 0-Uebersicht');

casper.thenClick('a.button[href="' + baseSitePath + 'intranet/subtyp/add"]');
casper.waitForUrl(baseSiteUrl + 'intranet/subtyp/add');
capture('Intranet Rundversuchsprogramme Subtypen 1-Hinzufuegen leer');

tools.fill_inputs();
casper.thenEvaluate(function () {
    jQuery('#edit-rundversuch').val(1);
    jQuery('#edit-name-0-value').val('Neuer Subtyp');
});

capture('Intranet Rundversuchsprogramme Subtypen 2-Hinzufuegen gefuellt');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramme Subtypen 3-Hinzufuegen gespeichert');
casper.thenClick('input.button[value="Alle Zeilen wählen"]');
capture('Intranet Rundversuchsprogramme Subtypen 5-Bearbeiten vor Speichern');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramme Subtypen 6-Bearbeiten gespeichert');

casper.thenClick(x('//table//a[contains(text(), "Neuer Subtyp")]/../..//a[contains(text(), "Löschen")]'));
casper.waitUntilVisible('#edit-cancel');
capture('Intranet Rundversuchsprogramme Subtypen 7-Loeschen bestaetigen');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramme Subtypen 8-Loeschen fertig');

casper.run();
