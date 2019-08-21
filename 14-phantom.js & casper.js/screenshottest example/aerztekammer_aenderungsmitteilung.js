/* global casper */

require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/globale_einstellungen/aerztekammer/uebersicht');
capture('Aerztekammer-Aenderungen 0-Uebersicht');

casper.run();
