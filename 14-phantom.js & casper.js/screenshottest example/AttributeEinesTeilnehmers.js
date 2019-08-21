require('module/start');
var capture = require('module/capture');

casper.thenOpen(baseSiteUrl + 'intranet/teilnehmerverwaltung'
  + '/43/attributkombination_eines_teilnehmers_erfassen');
casper.thenEvaluate(function() {
    var option = jQuery('option:contains(Klinische Chemie)');
    option.prop('selected', true).change();
});

casper.wait(5000);
capture('1-Teilnehmerattribute');

casper.run();
