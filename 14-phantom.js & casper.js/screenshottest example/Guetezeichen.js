require('module/start');
var capture = require('module/capture');

[1, 2, 5, 6].forEach(function (dg) {
  casper.thenOpen(baseSiteUrl + 'intranet/durchgang/' +
    dg + '/view/messwerte_einfrieren');
  casper.thenClick('#edit-submit');
  casper.waitForUrl(/view$/);

  casper.thenOpen(baseSiteUrl + 'intranet/durchgang/' +
    dg + '/view/zielwerte_sperren');
  casper.thenClick('#edit-submit');
  casper.waitForUrl(/view$/);
});

var pages = [
  // ausschließlich nominal
  'Nominal - Probenoptionen',
  baseSiteUrl + 'intranet/durchgang/1/view',

  'Nominal - Zielwerte',
  baseSiteUrl + 'intranet/durchgang/1/view/zielwert',

  'Nominal - L1021 - GZ da alle Antworten korrekt',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/1?maskiert_als=22',

  'Nominal - L1022 - GZ da FP Kell positiv bei Probe 2 erlaubt',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/1?maskiert_als=23',

  'Nominal - L1023 - kein GZ da Blutgruppe Probe 2 falsch',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/1?maskiert_als=24',

  'Nominal - L1024 - kein GZ da Antwort fehlt',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/1?maskiert_als=25',

  'Nominal - L1026 - kein GZ da FP Rhesus positiv bei Probe 1 nicht erlaubt',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/1?maskiert_als=26',

  // ausschließlich metrisch mit absoluten AG
  'Feste AG - Probenoptionen',
  baseSiteUrl + 'intranet/durchgang/2/view',

  'Feste AG - Zielwerte',
  baseSiteUrl + 'intranet/durchgang/2/view/zielwert',

  'Feste AG - L1021 - GZ',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/2?maskiert_als=22',

  'Feste AG - L1022 - GZ da Grenze erlaubt',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/2?maskiert_als=23',

  'Feste AG - L1023 - kein GZ, da Glukose Probe 1 daneben',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/2?maskiert_als=24',

  'Feste AG - L1024 - kein GZ, da Cholestern Probe 1 fehlt',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/2?maskiert_als=25',

  // ausschließlich metrisch mit prozentualen AG
  'Prozentuale AG - Probenoptionen',
  baseSiteUrl + 'intranet/durchgang/5/view',

  'Prozentuale AG - Zielwerte',
  baseSiteUrl + 'intranet/durchgang/5/view/zielwert',

  'Prozentuale AG - Akzeptanzgrenzen',
  baseSiteUrl + 'intranet/rundversuchsprogramm/analyt',

  'Prozentuale AG - Scoregruppe',
  baseSiteUrl + 'intranet/rundversuchsprogramm/analyt/scoregruppe/2/edit',

  // alle Ergebnisse in der Mitte des Akzeptanzintervalls
  'Prozentuale AG - L1029 - Score 0.0',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/5?maskiert_als=30',

  // Cholesterin (B) 360 mg/L -> 2 x |-40/400| x 25/(25+75) x 1/2 = 1.25%
  //       T-Score-Multiplikator ^   ^^^^^^^^^   ^^^^^^^^^^   ^^^ Probenanzahl
  //                                 relative    normiertes
  //                                 Abweichung  Scoregewicht
  'Prozentuale AG - L1026 - Score 1.25',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/5?maskiert_als=27',

  // zusätzlich
  // Glukose (A) 520 mg/L -> |20/500| x 75/(25+75) x 1/2 = 1.5%
  'Prozentuale AG - L1025 - Score 2.75',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/5?maskiert_als=26',

  // Ebenfalls 0.0625, da Messwerte an Zielwerten gespiegelt
  'Prozentuale AG - L1024 - Score 2.75',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/5?maskiert_als=25',

  // Cholesterin (A) 96 mg/L, Glukose (B) 53 mg/L -> 2.75%
  'Prozentuale AG - L1022 - Score 2.75',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/5?maskiert_als=23',

  // Glukose (A) 575 mg/L, Glukose (B) 45 mg/L -> 9.375%
  'Prozentuale AG - L1027 - Score 9.375',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/5?maskiert_als=28',

  // Cholesterin (A) 125 mg/L, Cholesterin (B) 300 mg/L -> 6.25%
  'Prozentuale AG - L1021 - Score 6.25',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/5?maskiert_als=22',

  // Cholesterin (B) 299.9 mg/L -> Ausschluß
  'Prozentuale AG - L1023 - kein GZ',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/5?maskiert_als=24',

  // Glukose (B) kein Messwert -> Ausschluß
  'Prozentuale AG - L1028 - kein GZ',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/5?maskiert_als=29',

  // Erteilung der Gütezeichen nach T-Score:
  // NR LABOR Score
  // 1  L1029 0.0  < Die ceil(7 x 30%) = 3 besten TN erhalten ein GZ
  // 2  L1026 1.25 <
  // 3  L1025 2.75 <
  //
  // 4  L1024 2.75 < erhalten wegen identischem Score ebenfalls ein GZ
  // 5  L1022 2.75 <
  //
  // 6  L1027 9.375 < erhalten kein GZ
  // 7  L1021 6.25  <

  // gemischtes RVP
  'Gemischt - Probenoptionen',
  baseSiteUrl + 'intranet/durchgang/6/view',

  'Gemischt - Zielwerte',
  baseSiteUrl + 'intranet/durchgang/6/view/zielwert',

  'Gemischt mit prozentualen AG - Akzeptanzgrenzen',
  baseSiteUrl + 'intranet/rundversuchsprogramm/analyt',

  'Gemischt - L1021 - GZ',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/6?maskiert_als=22',

  'Gemischt - L1022 - GZ da Grenze erlaubt',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/6?maskiert_als=23',

  'Gemischt - L1023 - kein GZ da Glukose Probe 1 ausserhalb',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/6?maskiert_als=24',

  'Gemischt - L1024 - kein GZ da Blutgruppe Probe 2 falsch',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/6?maskiert_als=25',

  'Gemischt - L1025 - kein GZ da Cholesterin Probe 1 ausserhalb',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/6?maskiert_als=26',

  'Gemischt - L1026 - kein GZ da Cholesterin Probe 1 fehlt',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/6?maskiert_als=27',

  'Gemischt - L1026 - kein GZ da Glukose Probe 2 fehlt',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/6?maskiert_als=28',

  'Gemischt - L1026 - kein GZ da Blutgruppe Probe 2 fehlt',
  baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/6?maskiert_als=29',
];

var p = 1;
var url;
while (pages.length) {
  var title = ('0' + p++).slice(-2) + '.' + pages.shift();
  url = pages.shift();
  casper.thenOpen(url);
  casper.thenEvaluate(function () {
    jQuery('#edit-auspraegungeingefroren').val('1999-09-09');
    jQuery('#edit-datenbank-gelockt').val('1999-09-09');
    jQuery('div[role=alert]:contains(geschlossen) em').text('<jetzt>');
  });
  capture(title, true);
}

casper.options.waitTimeout = 60 * 1000;

casper.thenOpen(baseSiteUrl + 'intranet/guetezeichen/2018');
casper.waitForUrl(/batch\?id=\d+&op=start/);
casper.waitForUrl(baseSiteUrl + 'intranet/guetezeichen/2018');
capture(p++ + '.Erteilte Guetezeichen');

casper.thenClick('[name=p13-t22]');
casper.waitForUrl(/batch\?id=\d+&op=start/);
casper.waitForUrl(baseSiteUrl + 'intranet/guetezeichen/2018');
capture(p++ + '.PDF fuer L1021', true);

url = baseSiteUrl + 'guetezeichen/13/2018/22/pdf';
require('module/tools').downloadPDF(url, p++ + '.Guetezeichen Nominales RVP Ole Ugaret');

// Test Download Excel-Datei für Ärztekammer
casper.thenOpen(baseSiteUrl + 'intranet/jahresauswertung_aerztekammer/2018');
casper.waitForSelector('#edit-table-5-generieren-button');
casper.thenClick('#edit-table-5-generieren-button');
casper.waitForSelector('div.progress');
casper.waitForSelector('a[href$="/5/excel"]');
casper.then(function () {
  url = baseSiteUrl + 'intranet/jahresauswertung/2018/5/excel';
  var target = require('module/target')(p++ + '.Jahresauswertung', 'xls');
  require('module/download')(url, target)
});

casper.thenOpen(baseSiteUrl + 'intranet/globale_einstellungen/aerztekammer/4/edit/jahresauswertung');
casper.waitForSelector('#edit-jahresauswertungfueraerztekammer-2018-generieren-button');
capture('Jahresauswertung für genau eine Ärztekammer', true);
casper.thenClick('#edit-jahresauswertungfueraerztekammer-2018-generieren-button');
casper.waitForSelector('a[href$="/4/excel"]');

casper.run();
