
require('module/start');
var capture = require('module/capture');

// alle Attribute von Hämatokrit [l/l] entfernen
casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/analyt/35/edit');
casper.thenEvaluate(function () {
  jQuery('.attribute-table :checkbox').prop('checked', false);
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages--status');

// neues S-Nominales Attribut für Hämatokrit [l/l] anlegen
casper.thenOpen(baseSiteUrl + 'intranet/attribut/add');
casper.thenEvaluate(function () {
  jQuery('#edit-analyte-box').prop('open', true);
});
casper.then(function () {
  casper.fill('.attribut-form', {
    'name[0][value]': 'Testattribut für Hämatokrit [l/l]',
    'skalierung': 'skala_snominal',
    'analyte[35]': 'checked',
  });
});
capture();

// und speichern
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages');
capture();

// Bearbeitungsseite des neuen Attributs öffnen
casper.thenClick('div.messages a');
casper.waitUntilVisible('input#edit-name-0-value');
capture();

// und URL für später merken
var edit_attribute_url;
casper.then(function () {
  edit_attribute_url = casper.getCurrentUrl();
});

// Seite zum hinzufügen einer Ausprägung öffnen
casper.thenClick('#edit-0');
casper.waitForUrl(new RegExp('attributauspraegung/add'));

// und URL ebenfalls merken
var add_occurrence_url;
casper.then(function () {
  add_occurrence_url = casper.getCurrentUrl();
});

// Ausprägungen anlegen
var edit_occurrence_url;
[
  'erste Ausprägung',
  'zweite Ausprägung',
  'dritte Ausprägung'
].forEach(function (occurrence, i) {
  casper.then(function () {
    casper.open(add_occurrence_url);
  });
  casper.then(function () {
    casper.fill('.attributauspraegung-form', {'name[0][value]': occurrence});
  });
  if (i === 0) {
    capture();
  }
  casper.thenClick('#edit-submit');
  casper.waitUntilVisible('div.messages--status');
  if (i === 0) {
    casper.then(function () {
      edit_occurrence_url = casper.getCurrentUrl();
    });
  }
  capture();
});

// Attributausprägungen zu Hämatokrit [l/l] hinzufügen
casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/analyt/35/edit/auspraegungen');
casper.thenEvaluate(function () {
  var form = jQuery('.auspraegungen-fuer-einen-analyten-waehlen-form');
  form.find(':checkbox').prop('checked', true);
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages--status');
capture();

// Hämatokrit [l/l] zum Klinische Chemie/Hospital Hohenems hinzufügen
casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung/43/angemeldete_analyte_aendern');
casper.thenEvaluate(function () {
  var option = jQuery('option:contains(Hämatologie)');
  option.prop('selected', true).change();
});
casper.wait(1000);
casper.thenEvaluate(function () {
  jQuery('#edit-table-35-angemeldet-').prop('checked', true);
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages--status');

// Seite zur Eingabe der Messverfahren öffnen
casper.thenOpen(baseSiteUrl + 'extranet/verwaltung_der_messverfahren');
casper.thenEvaluate(function () {
  var option = jQuery('option:contains(Hämatologie)');
  option.prop('selected', true).change();
});
casper.wait(1000);
// Verfügbare Ausprägungen in den error log schreiben
casper.then(function () {
  var occurrences = casper.evaluate(function () {
    return jQuery('#edit-table').find('option').map(function () {
      return jQuery(this).text();
    }).get();
  });
  casper.log(JSON.stringify(occurrences), 'warning');
});
capture();

// Feldtyp auf M-nominal umstellen
casper.then(function () {
  casper.open(edit_attribute_url);
});
casper.then(function () {
  casper.fill('.attribut-form', {skalierung: 'skala_mnominal'});
});
capture();

// und speichern
casper.thenClick('#edit-submit');
capture();

// nochmal Seite zur Eingabe der Messverfahren öffnen
casper.thenOpen(baseSiteUrl + 'extranet/verwaltung_der_messverfahren');
capture();

// Attributausprägungen von Hämatokrit [l/l] entfernen
casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/analyt/35/edit/auspraegungen');
casper.thenEvaluate(function () {
  var form = jQuery('.auspraegungen-fuer-einen-analyten-waehlen-form');
  form.find(':checkbox').prop('checked', false);
});
casper.thenClick('#edit-submit');
casper.waitUntilVisible('div.messages--status');
capture();

// Häkchen bei Hämatokrit [l/l] auf der Attributseite löschen
casper.then(function () {
  casper.open(edit_attribute_url);
});
casper.then(function () {
  casper.fill('.attribut-form', {'analyte[35]': ''});
});
casper.thenClick('#edit-submit');
capture();

// Log-Seiten prüfen
casper.then(function () {
  casper.open(edit_occurrence_url.replace('edit', 'datenaenderung_logeintrag'));
});
casper.waitUntilVisible('a.is-active[href$=logeintrag]');
capture('Automatisches Log fuer Attributauspraegungen', true);

casper.then(function () {
  casper.open(edit_attribute_url.replace('edit', 'datenaenderung_logeintrag'));
});
casper.waitUntilVisible('a.is-active[href$=logeintrag]');
capture('Automatisches Log fuer Attribute', true);

casper.run();
