Dcat.ready(function () {
    $('.dcat-admin-body').css('padding-right', '0');

    var fixedRight = $('.table-wrap.table-fixed.table-fixed-right');
    var height = fixedRight.css('max-height');

    if (height && height.indexOf('px') > -1) {
        fixedRight.css('max-height', (parseInt(height, 10) + 5) + 'px');
    }
});
