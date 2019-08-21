require = patchRequire(require);

exports.fillRvpForm = function (iTyp, sName) {
  casper.fill('form', {
    'typnr[0][value]': iTyp,
    'name[0][value]': sName,
    'frequenzpa[0][value]': 4,
    'ruecksendefrist[0][value]': 14,
    'korrekturfrist[0][value]': 7,
    'fachlicher_versuchsleiter[0][value]': 'David Heik',
    'email[0][value]': 'david.heik@quodata.de',
    'technischer_versuchsleiter[0][value]': 'David Heik'
  });
};

exports.gotoErgebnisprotokolle = function (iDurchgang, iIndex) {
  casper.thenOpen(baseSiteUrl + 'intranet/durchgang/' + iDurchgang + '/view/ergebnisprotokolle');
  casper.thenEvaluate(function () {
    var ergebnisprotokolle = jQuery('[name="edit-table_length"]');
    ergebnisprotokolle.val('100');
    ergebnisprotokolle.change();
  });
  casper.then(function () {
    casper.wait(2000);
  });
  capture(iIndex + ' Uebersicht Ergebnisprotokolle');
};

exports.maskiereAlsTeilnehmerUebersichtRundversucheDateneingabe = function (iDurchgang, iIndex, iLadobID) {
  // Als QD-Labor 1 Maskieren
  casper.thenOpen(baseSiteUrl + 'extranet/jahresauswertung?maskiert_als=' + iLadobID);
  capture(iIndex + ' maskieren als Labor' + iLadobID + ' erfolgreich');
  iIndex++;
  casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche');
  capture(iIndex + ' Uebersicht Rundversuche Labor' + iLadobID + ' - nach RohdatenImort');
  iIndex++;
  casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/' + iDurchgang);
  capture(iIndex + ' Dateneingabe Labor' + iLadobID + ' - nach RohdatenImort');
};

exports.erstelleNeuenDurchgang = function (iRVP, iDurchgang, iIndex) {
  casper.thenOpen(baseSiteUrl + 'intranet/durchgang');
  casper.thenEvaluate(function (iRVP) {
    var durchgangErstellen = jQuery('[name="selected_rundversuch"]');
    durchgangErstellen.val(iRVP);
    durchgangErstellen.change();
  }, iRVP);
  casper.then(function () {
    casper.wait(2000);
  });
  capture(iIndex + ' Durchgaenge-Uebersicht');

  casper.thenClick('a.button-action');
  casper.waitForUrl(baseSiteUrl + 'intranet/durchgang/add');

  casper.then(function () {
    casper.fill('.durchgang-form', {
      'rundversuchsdurchgangsnummer[0][value]': iDurchgang,
    }, true);
  });

  casper.then(function () {
    casper.wait(300);
  });
  casper.thenEvaluate(function () {
    var probenaussendung = jQuery('#edit-probenaussendung-0-value-date');
    probenaussendung.val('2017-11-06');
    probenaussendung.change();
    probenaussendung.trigger('input');
    probenaussendung.blur();
  });
  casper.then(function () {
    casper.wait(300);
  });

  iIndex++;
  capture(iIndex + ' Durchgang-vor dem Speichern');
  iIndex++;
  casper.thenClick('#edit-submit');
  casper.waitUntilVisible('div.messages');
  capture(iIndex + ' Durchgang-nach dem Speichern');
};

exports.rohdatenImportVorbereiten = function (iDurchgang, iIndex, excelFilename, bRemoveOldExcelFile) {
  // alte Datei entfernen
  if (bRemoveOldExcelFile == true) {
    casper.thenOpen(baseSiteUrl + 'intranet/durchgang/import_rohdaten');
    capture(iIndex + ' alte Datei noch da');
    casper.thenClick('#edit-excel-upload-remove-button');
    casper.wait(1000);
    iIndex++;
    capture(iIndex + ' alte Datei noch weg');
  }


  // RohdatenImport
  casper.thenOpen(baseSiteUrl + 'intranet/durchgang/import_rohdaten', function () {
    this.page.uploadFile('input[type="file"]', excelFilename);
  });
  casper.wait(10000);
  iIndex++;
  capture(iIndex + ' Upload der Datei');
  // Auswahl des Durchgangs
  casper.thenEvaluate(function (iDurchgang) {
    var durchgang = jQuery('#edit-durchgang');
    durchgang.val(iDurchgang);
    durchgang.selected();
  }, iDurchgang);
};
