/* global casper */

require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm');
capture('Intranet Rundversuchsprogramm 0-Uebersicht');

casper.thenClick('a.button');
casper.waitForUrl(baseSiteUrl + 'intranet/rundversuchsprogramm/add');

function fillRvpForm(iTyp) {
    casper.fill('form', {
        'typnr[0][value]': iTyp,
        'name[0][value]': 'pharmazeutische Chemie',
        'frequenzpa[0][value]': 3,
        'ruecksendefrist[0][value]': 14,
        'korrekturfrist[0][value]': 12,
        'fachlicher_versuchsleiter[0][value]': 'Bob',
        'email[0][value]': 'bob@bobbington.com',
        'technischer_versuchsleiter[0][value]': 'Stuart'
    });

    casper.thenEvaluate(function () {
        jQuery('#edit-kooperationspartner-ja').click();
        jQuery('#edit-checkanmerkungen-value').click();
    });
}
casper.then(function () { fillRvpForm(2); });
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramm 1-Fehler-Typ-wiederholt');

casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/add');
casper.then(function () { fillRvpForm(35); });
capture('Intranet Rundversuchsprogramm 1-nach Eingabe');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Intranet Rundversuchsprogramm 2-nach Speichern');

// wieder löschen
casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/13/delete');
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');

casper.run();
