/* global casper */

require('module/start');
var capture = require('module/capture');
var tools = require('module/tools');
var x = require('casper').selectXPath;

casper.thenOpen(baseSiteUrl + 'intranet/probenoption');

capture('Intranet Rundversuchsprogramme Probenoptionen 0-Uebersicht');

casper.thenClick('a.button[href="' + baseSitePath + 'intranet/probenoption/add"]');
casper.waitForUrl(baseSiteUrl + 'intranet/probenoption/add');
capture('Intranet Rundversuchsprogramme Probenoptionen 1-Hinzufuegen leer');

casper.thenEvaluate(function () {
    jQuery('#edit-rundversuch').val(1);
    jQuery('#edit-name-0-value').val('Neue Probenoption');
});

capture('Intranet Rundversuchsprogramme Probenoptionen 2-Hinzufuegen gefuellt');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramme Probenoptionen 3-Hinzufuegen gespeichert');

tools.fill_inputs();
casper.thenEvaluate(function () {
    jQuery('#edit-name-0-value').val('Neue Probenoption');
});
capture('Intranet Rundversuchsprogramme Probenoptionen 4-Bearbeiten vor Speichern');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramme Probenoptionen 5-Bearbeiten gespeichert');

casper.waitForText("Glucosespiegel");

casper.thenEvaluate(function () {
    var tr = jQuery('tr:contains("Neue Probenoption")');
    location.href = jQuery('a:contains("Löschen")', tr).attr('href');
});
casper.waitUntilVisible('#edit-cancel');
capture('Intranet Rundversuchsprogramme Probenoptionen 6-Loeschen bestaetigen');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramme Probenoptionen 7-Loeschen fertig');

casper.run();
