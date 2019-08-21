require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/globale_einstellungen/extranet_zugriff');
capture('Globale Einstellungen Zugriffsdauer Extranet 0-Voreinstellungen');

casper.then(function() {
    this.fill('form', {
        zugriffsdauer: 5,
    });
});
capture('Globale Einstellungen Zugriffsdauer Extranet 1-vor dem Speichern');

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Globale Einstellungen Zugriffsdauer Extranet 2-nach dem Speichern');


casper.thenOpen(baseSiteUrl + 'intranet/globale_einstellungen/extranet_zugriff');
casper.then(function() {
    this.fill('form', {
        zugriffsdauer: -5,
    });
});

casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture('Globale Einstellungen Zugriffsdauer Extranet 3-nach dem Speichern negative Zahlen');

casper.run();
