/* global casper */

require('module/start');
var capture = require('module/capture');

function anredeFotografieren(sTitel, sAnrede, sVorname, sNachname, sFoto1, sFoto2) {
    casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/107/edit');
    casper.waitForSelector('#edit-ansprechpartner', null, null, 15000);


    casper.then(function () {
        var gefunden = casper.evaluate(function () {
            var button = jQuery('#edit-hauptadresse').click();
            return button.length;
        });
        if (gefunden) casper.wait(4000);
    });

    casper.thenEvaluate(function (sTitel, sAnrede, sVorname, sNachname) {
        jQuery('[id^="edit-titel-pre-"]').val(sTitel).change();
        jQuery('[id^="edit-anrede-"]').val(sAnrede).change();
        jQuery('[id^="edit-vorname-"]').val(sVorname).change();
        jQuery('[id^="edit-nachname-"]').val(sNachname).change();
        jQuery('[id^="edit-strasse"]').val('Hörlgasse 18').change();
        jQuery('[id^="edit-plz-"]').val('0393').change();
        jQuery('[id^="edit-ort-"]').val('Wien').change();
        jQuery('[id^="edit-telefon2"]').val('34').change();
        jQuery('[id^="edit-telefon3"]').val('456778').change();
        jQuery('#edit-pin-0-value').css({
            'color': 'black',
            'background-color': 'black',
        });
    }, sTitel, sAnrede, sVorname, sNachname);
    if (sFoto1)
        capture(sFoto1);

    casper.thenClick('#edit-submit');
    casper.waitForUrl(baseSiteUrl + 'intranet/teilnehmerverwaltung');

    casper.thenEvaluate(function (baseSiteUrl) {
        jQuery('body').html('<pre>Bitte warten...</pre>');
        jQuery('pre').load(baseSiteUrl + 'intranet/teilnehmerverwaltung/107/anrede');
    }, baseSiteUrl);
    casper.waitForText('"Sehr geehrt');

    capture(sFoto2);
}

anredeFotografieren('', 'Herr', 'Maximilian', 'Hinz', 'Formular', 'JSON.Maximilian');
anredeFotografieren('Dr.', 'Frau', 'Beate', 'Togelt', '', 'JSON.Beate');

casper.run();
