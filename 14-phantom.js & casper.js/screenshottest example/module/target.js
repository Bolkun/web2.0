var path = 'C:/xampp/htdocs/DelphiScreenshotTestsuite/html/Bilder/OEQUASTA/';

module.exports = function (name, ext) {
  var test = casper.cli.get(0).replace(/\.js$/, '');
  return path + test + '.' + name + '-ist.' + ext;
};
