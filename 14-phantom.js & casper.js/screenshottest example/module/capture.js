require = patchRequire(require);

var target = require('module/target');
var names = {};
var count = 1;


module.exports = function (name, mask_dates) {
  casper.thenEvaluate(function (mask_dates) {
    if (!mask_dates) {
      return;
    }

    (function mask_node(node) {

      if (node.nodeType === document.TEXT_NODE) {
        var m;
        var r = /\d\d\.\d\d\.\d{4}|\d{1,2}:\d\d/g;
        while (m = r.exec(node.data)) {
          // replace(/\d+:/g, 'XX:') fängt den Fall ab, dass die Stunde in der
          // Uhrzeit mal ein- und mal zweistellig ist
          // noinspection JSUndefinedPropertyAssignment
          node.data = node.data.replace(m[0], m[0].replace(/\d+:/g, 'XX:').replace(/\d/g, 'X'));
        }
      }
      else {
        var children = node.childNodes;
        for (var c = 0; c !== children.length; c++) {
          mask_node(children[c]);
        }
      }
    }(document));
  }, mask_dates);

  casper.then(function () {
    if (!name) {
      name = count++;
    }
    this.capture(target(name, 'png'));
    if (name in names) {
      casper.log('Duplicate screenshot: ' + name, 'warning');
    }
    names[name] = true;
  });
};
