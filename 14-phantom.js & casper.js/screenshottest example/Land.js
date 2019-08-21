require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/globale_einstellungen/land');
capture('Globale Einstellungen Laender 0-Uebersicht');

casper.thenClick('a.button');
casper.waitForUrl(baseSiteUrl + 'intranet/globale_einstellungen/land/add');
capture('Globale Einstellungen Laender 1-Neues Land Formular empty');

casper.thenEvaluate(function() {
    jQuery('[name="label"]').val('Deutschland').change();
    
    jQuery('.visually-hidden').removeClass('visually-hidden');
});

// sonst klappt das Einfügen des Maschinenlesbarer Namens nicht:
casper.wait(1000);
casper.thenEvaluate(function() {
    jQuery('[name="id"]').val('deutschland');
});
capture('Globale Einstellungen Laender 2-Neues Land Formular filled');

casper.thenClick('#edit-submit');
casper.waitForUrl(baseSiteUrl + 'intranet/globale_einstellungen/land');
capture('Globale Einstellungen Laender 3-Uebersicht nach dem Anlegen');

casper.run();
