require = patchRequire(require);

var target = require('module/target');

exports.fill_inputs = function () {
  casper.thenEvaluate(function () {
    var offset = 1;

    // Text
    jQuery('input[type="text"]:enabled').each(function (i) {
      jQuery(this).val('Text ' + (offset + i + 1));
    });

    // Memo
    jQuery('textarea:enabled').each(function (i) {
      jQuery(this).val('Textarea-Text ' + (offset + i + 1));
    });

    // Zahl
    jQuery('input[type="number"]:enabled').each(function (i) {
      jQuery(this).val(offset + i + 1);
    });

    // Datum
    jQuery('input[type="date"]:enabled').each(function (i) {
      var iDayBetween1And31 = (offset + i) % 31 + 1;
      jQuery(this).val(iDayBetween1And31 + '.01.2015');
    });

    // Radio-Buttons
    jQuery('div:has(:radio):enabled').each(function (i) {
      var radios = jQuery(this).find(':radio');
      jQuery(radios[(i + offset) % 2]).attr('checked', true);
    });

    // Checkboxen
    var aCheckbox = jQuery('input[type="checkbox"]:enabled');
    jQuery(aCheckbox).each(function (i) {
      i += offset;
      if (i % 2 === 0) {
        jQuery(this).attr('checked', true);
      }
    });

    // Dropdown-Menu
    var aDropdown = jQuery('select:enabled');
    aDropdown.each(function (i) {
      var option = jQuery(this).find('option');
      jQuery(this).val(jQuery(option[(offset + i + 1) % option.length]).val()).change();
    });
  });
};

exports.black_cells_with_currentdate = function () {
  casper.thenEvaluate(function () {
    jQuery('td:contains(' + (new Date()).getFullYear() + ')').css({
      'color': 'black',
      'background-color': 'black',
    });
  });
};

exports.downloadPDF = function (url, name) {
  var dest = target(name, 'pdf');
  casper.then(function () {
    require('module/download')(url, dest);
  });
};
