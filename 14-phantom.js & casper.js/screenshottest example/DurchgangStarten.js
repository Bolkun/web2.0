/* global casper */

require('module/start');
var capture = require('module/capture');

// erstmal Datenbank anpassen: einen Subtyp löschen
casper.thenOpen(baseSiteUrl + 'intranet/subtyp/1/teilnehmer');
casper.waitForSelector('[name*=selected_rows]');
casper.thenEvaluate(function () {
  jQuery('[name*=selected_rows]').prop('checked', true);
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
casper.thenOpen(baseSiteUrl + 'intranet/subtyp/1/edit');
casper.waitForSelector('#edit-subtyp-deaktiviert-value');
casper.thenEvaluate(function () {
  jQuery('#edit-subtyp-deaktiviert-value').prop('checked', true);
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
casper.thenOpen(baseSiteUrl + 'intranet/subtyp/3/edit');
casper.waitForSelector('#edit-analyt-tabelle-33-drin');
casper.thenEvaluate(function () {
  jQuery('#edit-analyt-tabelle-33-drin').prop('checked', true);
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');

// jetzt gehts los
casper.thenOpen(baseSiteUrl + 'intranet/durchgang');
casper.thenEvaluate(function () {
  jQuery('[name="selected_rundversuch"]').val(1).change();
});
casper.then(function() {
  casper.wait(2000);
});
capture('00 - Uebersicht Durchgaenge');

casper.thenClick('a[href="' + baseSitePath + 'intranet/durchgang/15/view"]');
casper.waitUntilVisible('#edit-probenaussendung');
capture('01 - Uebersicht Subtypen');

casper.thenClick('a[href="' + baseSitePath + 'intranet/durchgang/15/view/matching_und_versandstart/3"]');
casper.thenEvaluate(function () {
  jQuery('#edit-probenoptionen-table-1-drin').prop('checked', false);
  jQuery('#edit-probenoptionen-table-3-drin').prop('checked', false);
});
capture('03 - Subtyp 3');

casper.thenClick('#edit-proben-speichern-button');
casper.waitForUrl(/batch\?id=\d+\&op=start/);
casper.waitUntilVisible('#edit-button');
casper.thenEvaluate(function () {
  // Spalte Ansprechpartner verstecken, da Adressen ja zufällig sind
  jQuery('#edit-proben-institution-tabelle').DataTable().column('Ansprechpartner:name').visible(false);
});
capture('04 - vor dem Starten');

casper.thenClick('#edit-button');
casper.waitUntilVisible('div.messages--error');
capture('05 - Fehler keine Adressen');

casper.thenOpen(baseSiteUrl + 'user/logout');
casper.wait(3000);
casper.then(function () {
  this.fill('form', {
    name: 'admin',
    pass: 'admin',
  });
});
casper.thenClick('#edit-submit');
casper.waitForSelector('a[href="' + baseSitePath + 'extranet"]');
casper.thenOpen(baseSiteUrl + 'admin/config/teilnehmerverwaltung/adressen_generieren');
casper.waitUntilVisible('#edit-submit.button--danger');
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages', null, null, 30000);
capture('06 - Adressen generiert');

casper.thenOpen(baseSiteUrl + 'user/logout');
casper.wait(3000);
casper.then(function () {
  this.fill('form', {
    name: 'organizer',
    pass: 'trillerhasen',
  });
});
casper.thenClick('#edit-submit');
casper.waitForSelector('a[href="' + baseSitePath + 'extranet"]');
casper.thenOpen(baseSiteUrl + 'intranet/durchgang/15/view/matching_und_versandstart/3');
casper.waitForUrl(baseSiteUrl + 'intranet/durchgang/15/view/matching_und_versandstart/3');
casper.thenClick('#edit-button');
casper.waitForText('Excel');
capture('07 - nach dem Starten');

casper.thenOpen(baseSiteUrl + 'intranet/durchgang/15/view');
casper.waitUntilVisible('#edit-probenaussendung');
capture('08 - Tab Bearbeiten');

casper.thenClick('a[href$=zielwert]');
casper.waitUntilVisible('a.is-active[href$=zielwert]');
capture('09 - Tab Zielwerte');

casper.thenClick('a[href$=ergebnisprotokolle]');
casper.waitUntilVisible('a.is-active[href$=ergebnisprotokolle]');
capture('10 - Tab Ergebnisse');

casper.thenClick('a[href$=log]');
casper.waitUntilVisible('a.is-active[href$=log]');
casper.waitForText('Keine');
capture('11 - Tab Log');

casper.thenClick('a[href$=kollektivbildung]');
casper.waitUntilVisible('a.is-active[href$=kollektivbildung]');
capture('12 - Tab Kollektive');

casper.thenClick('a[href$=pdf_erstellen]');
casper.waitUntilVisible('a.is-active[href$=pdf_erstellen]');
capture('13 - Tab PDFs');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/15?maskiert_als=13');
casper.waitForUrl(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/15?maskiert_als=13');
capture('15 - Dateneingabe 0013');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/15?maskiert_als=43');
casper.waitForUrl(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/15?maskiert_als=43');
capture('16 - Dateneingabe 1042');

casper.run();
