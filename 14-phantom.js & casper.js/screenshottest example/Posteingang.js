/* eslint-disable comma-dangle,strict */
/* global casper */

require('module/start');
var capture = require('module/capture');

// Attribute Metrisch, Freitext (einzeilig), Freitext (mehrzeilig)
// zum Analyt Glukose [mg/dl] hinzufügen und Methode entfernen.
casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/analyt/1/edit');
casper.then(function () {
  casper.fill('.analyt-form', {
    'attribute[table][2][enabled]': '',
    'attribute[table][5][enabled]': 'checked',
    'attribute[table][6][enabled]': 'checked',
    'attribute[table][7][enabled]': 'checked',
  });
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages--status');

// keine Aktualisierungsprüfung der Kombination bei Gerät
casper.thenOpen(baseSiteUrl + 'intranet/attribut/1/edit');
casper.then(function () {
  casper.fill('.attribut-form', {
    'aktualisierungspruefung_kombination[value]': ''
  });
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages--status');

// Glukose [mg/dl] dem Teilnehmer zuordnen
casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/43/angemeldete_analyte_aendern');
casper.thenEvaluate(function () {
  var option = jQuery('option:contains(Klinische Chemie)');
  option.prop('selected', true).change();
});
casper.wait(1000);
casper.thenEvaluate(function () {
  jQuery('#edit-table-1-angemeldet-').prop('checked', true);
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages--status');

// Dateneingabe der Klinischen Chemie aufrufen
casper.thenOpen(baseSiteUrl + 'extranet/verwaltung_der_messverfahren');
casper.thenEvaluate(function () {
  var option = jQuery('option:contains(Klinische Chemie)');
  option.prop('selected', true).change();
});
casper.wait(500);
capture();

// 'mehrzeiliger Freitext' in einzeiligem Freitext eingeben
casper.then(function () {
  casper.fill('.verwaltung-der-messverfahren-form', {
    'table[1][auspraegung][6]': 'mehrzeiliger Freitext',
  });
});
capture();

// und abschicken
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages--status');
capture(null, true);

// Posteingang für neue Ausprägungen anzeigen
casper.thenOpen(baseSiteUrl + 'intranet/attribut/posteingang_neue_auspraegungen');
capture(null, true);

// Kommentar zur Attributänderung eintragen
casper.thenClick('.region-content [href]');
casper.then(function () {
  casper.fill('.region-content form', {bemerkungen: 'Abgelehnt!!'});
});
capture(null, true);

// und Attributänderung ablehnen
casper.thenClick('[name=button_ablehnen]');
capture();

// nochmal Dateneingabe aufrufen
casper.thenOpen(baseSiteUrl + 'extranet/verwaltung_der_messverfahren');
capture();

// 'einzelliger Freitext' in einzeiligem Freitext eingeben
casper.then(function () {
  casper.fill('.verwaltung-der-messverfahren-form', {
    'table[1][auspraegung][1]': '1',
    'table[1][auspraegung][5]': '-12.34',
    'table[1][auspraegung][6]': 'einzelliger Freitext',
    'table[1][auspraegung][7]': 'mehrzeiliger\nFreitext',
  });
});
capture();

// und abschicken - Foto 9
casper.thenClick('#edit-submit');
casper.waitForText('bei der ÖQUASTA.');
capture(null, true);

// beantragte Attributänderung anzeigen
casper.thenOpen(baseSiteUrl + 'intranet/attribut/posteingang_neue_auspraegungen');
casper.thenClick('.region-content [href]');
capture(null, true);

// Rechtschreibfehler korrigieren (mit Hinweis)
casper.then(function () {
  casper.fill('.region-content form', {
    analyt1_attribut6: 'einzeiliger Freitext',
    bemerkungen: 'Bitte immer die Rechtschreibung prüfen!!',
  });
});
capture(null, true);

// und Attributänderung akzeptieren
casper.thenClick('[name=button_akzeptieren]');
capture();

// Dateneingabe mit korrigiertem Wert anzeigen
casper.thenOpen(baseSiteUrl + 'extranet/verwaltung_der_messverfahren');
capture();

// Liste der Attributausprägungen aufrufen
casper.thenOpen(baseSiteUrl + 'intranet/attribut/attributauspraegung');
capture();

casper.run();
