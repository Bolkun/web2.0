/* global casper */

require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'nutzerkonto');
capture('Intranet Passwort aendern 0-vorher');

casper.thenClick('input#edit-submit');
casper.waitForSelector('.messages--error');
capture('Intranet Passwort aendern 1-Fehler Passwort leer');

casper.thenEvaluate(function () {
    jQuery('[type=password]').val('admin').change(); // zu kurz!
});
capture('Intranet Passwort aendern 2-Fehler Passwort nur 5 Zeichen');

// das klappt zum ersten Mal:
casper.thenOpen(baseSiteUrl + 'nutzerkonto');
casper.thenEvaluate(function () {
    jQuery('[type=password]').val('passwort').trigger('input');
});
casper.waitUntilVisible('span.ok');
casper.wait(500);
capture('Intranet Passwort aendern 3-vor-dem-Abschicken');

casper.thenClick('input#edit-submit');
casper.waitForSelector('.messages--status');
capture('Intranet Passwort aendern 4-nach-dem-Abschicken');

// Ausloggen
casper.thenOpen(baseSiteUrl + 'user/logout');
casper.waitForUrl(baseSiteUrl + '');

casper.thenOpen(baseSiteUrl + 'nutzerkonto');
capture('Intranet Passwort aendern 5-geschuetzte Seite');

// mit neuem Passwort wieder einloggen
casper.thenEvaluate(function () {
    jQuery('#edit-name').val('organizer');
    jQuery('#edit-pass').val('passwort');
});
casper.thenClick('input#edit-submit');
casper.waitUntilVisible('a[href="' + baseSitePath + 'user/logout"]');
casper.thenEvaluate(function () {
    jQuery('.profile').hide();
});
capture('Intranet Passwort aendern 6-nach-dem-Neueinloggen mit neuem Passwort');

// Passwort zurückändern
casper.thenOpen(baseSiteUrl + 'user/7/edit');
casper.thenEvaluate(function () {
    jQuery('#edit-current-pass').val('passwort').change();
    jQuery('#edit-pass-pass1').val('trillerhasen');
    jQuery('#edit-pass-pass2').val('trillerhasen');
});
casper.thenClick('input#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Passwort aendern 7-nach-dem-Zurueckaendern');

casper.run();
