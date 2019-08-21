require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/rundversuchsprogramm/analyt/scoregruppe/');
casper.thenEvaluate(function () {
  jQuery('select[name="selected_rundversuch"]').val(2).change();
});
casper.wait(10000);
capture('0-Uebersicht');

casper.thenClick('.region-content a[href$=add]');
casper.then(function() {
  casper.fill('.scoregruppe-form', {
    'rundversuch': '2',
    'bezeichnung[0][value]': 'Scoregruppe für Hämatologie',
  });
});
capture('1-vor dem Anlegen');

casper.thenClick("#edit-submit");
capture('2-nach dem Anlegen');

casper.thenEvaluate(function () {
  jQuery(':checkbox[id*=edit-analyt-tabelle]').click();
  jQuery('[type=number][id*=edit-analyt-tabelle]').eq(0).val(5)
  jQuery('[type=number][id*=edit-analyt-tabelle]').eq(1).val(8)
});
casper.thenClick('#edit-submit');
capture('4-nach dem Speichern');

casper.thenEvaluate(function () {
  jQuery('select[name="selected_rundversuch"]').val(2).change();
});
casper.wait(10000);
casper.thenClick('table tbody tr:last-child [href$=edit]');
capture('5-mit Gewichtung 5 zu 8');

casper.run();
