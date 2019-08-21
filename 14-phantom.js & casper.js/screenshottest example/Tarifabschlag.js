require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/globale_einstellungen/tarifabschlag');
capture('Globale Einstellungen Tarifabschlag 0-Voreinstellungen');

casper.then(function() {
    this.fill('form', {
        manipulationsgebuehr: 25,
        abschlag_mitglieder: '0',
        abschlag_bulk: 50,
        abschlag_ohneAuswertung: 100
    });
});
capture('Globale Einstellungen Tarifabschlag 1-vor dem Speichern');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Globale Einstellungen Tarifabschlag 2-nach dem Speichern');

casper.thenOpen(baseSiteUrl + 'intranet/globale_einstellungen/tarifabschlag');
casper.then(function() {
    this.fill('form', {
        manipulationsgebuehr: -25,
        abschlag_mitglieder: -10,
        abschlag_bulk: -50,
        abschlag_ohneAuswertung: -100
    });
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Globale Einstellungen Tarifabschlag 3-nach dem Speichern negative Zahlen');

casper.thenOpen(baseSiteUrl + 'intranet/globale_einstellungen/tarifabschlag');
casper.then(function() {
    this.fill('form', {
        manipulationsgebuehr: 101,
        abschlag_mitglieder: 102,
        abschlag_bulk: 103,
        abschlag_ohneAuswertung: 104
    });
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Globale Einstellungen Tarifabschlag 3-nach dem Speichern ueber 100 Prozent');

casper.run();
