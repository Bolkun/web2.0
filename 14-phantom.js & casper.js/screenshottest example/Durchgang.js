/* global casper */

require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/durchgang');
casper.thenEvaluate(function () {
    jQuery('[name="selected_rundversuch"]').val(1).change();
});
casper.then(function() {
    casper.wait(2000);
});
capture('Intranet Rundversuchsdurchgaenge 0-Uebersicht');

casper.thenClick('.action-links a.button');
casper.waitForUrl(baseSiteUrl + 'intranet/durchgang/add');

// Der folgende Test funktioniert nicht mehr und muss neu geschrieben werden
//casper.thenClick('#edit-submit');
//casper.waitForSelector('.messages--error');
//capture('Intranet Rundversuchsdurchgaenge 1-Datum fehlt');
//casper.thenOpen(baseSiteUrl + 'intranet/durchgang/add');

casper.thenEvaluate(function () {
    jQuery('#edit-probenaussendung-0-value-date').val('2020-10-10').change().trigger('input').blur();
});
capture('Intranet Rundversuchsdurchgaenge 2-vor dem Speichern');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsdurchgaenge 3-nach dem Speichern, Datum im Oktober');

casper.thenEvaluate(function () {
    jQuery('#edit-probenaussendung').val('2021-05-10').change().trigger('input').blur();
});
capture('Intranet Rundversuchsdurchgaenge 4-nur Aussendung im Mai, vor dem Speichern');
casper.thenClick('#edit-dateformsubmit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsdurchgaenge 5-beide Momente im Mai');

casper.thenOpen(baseSiteUrl + 'intranet/durchgang');
capture('Intranet Rundversuchsdurchgaenge 6-Uebersicht nach dem Anlegen');

casper.thenEvaluate(function () {
    jQuery('[name="selected_rundversuch"]').val(3).change();
});
casper.then(function() {
    casper.wait(2000);
});
capture('Intranet Rundversuchsdurchgaenge 7-Gerinnung');

casper.thenClick('.action-links a.button');
casper.waitForUrl(baseSiteUrl + 'intranet/durchgang/add');
capture('Intranet Rundversuchsdurchgaenge 8-erster Durchgang fuer erinnung');

casper.run();
