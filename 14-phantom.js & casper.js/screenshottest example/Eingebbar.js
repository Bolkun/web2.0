/* global casper */

require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/durchgang/1/view');
casper.waitForSelector('#edit-dateformsubmit');
casper.thenClick('#edit-aeingebbar summary'); // aufklappen
casper.thenClick('#edit-aeingebbar-table-2-1'); // Rhesus Probe A abwählen
capture('Rhesus Probe A abgewaehlt Intranet');

casper.thenClick('#edit-dateformsubmit');
casper.waitForSelector('div.messages--status');

casper.thenOpen(baseSiteUrl + 'extranet/uebersicht_rundversuche/dateneingabe/1?maskiert_als=22');
casper.waitForSelector('#edit-submit');
capture('Rhesus Probe A abgewaehlt Dateneingabeseite');

casper.thenOpen(baseSiteUrl + 'intranet/durchgang/1/view');
casper.waitForSelector('#edit-dateformsubmit');
casper.thenClick('#edit-aeingebbar summary'); // aufklappen
casper.thenClick('#edit-aeingebbar-table-2-1'); // Rhesus Probe A wieder hinzufügen
casper.thenClick('#edit-dateformsubmit');
casper.waitForSelector('div.messages--status');

casper.run();
