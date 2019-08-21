require('module/start');
var download = require('module/download');
var target = require('module/target');

[1, 2, 5, 6].forEach(function(dg) {
  var url = baseSiteUrl + 'intranet/durchgang/'
          + dg + '/view/ergebnisprotokolle';
  casper.thenOpen(url);
  casper.then(function() {
    var url = casper.getElementAttribute('[href*=Rohdaten]', 'href');
    var name = decodeURIComponent(url.match(/([^/]+).xls\?/)[1]);
    download(url, target(name, 'xls'));
  });
});

casper.run();
