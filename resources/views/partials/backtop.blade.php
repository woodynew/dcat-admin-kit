@php($backToTopAsset = admin_asset('@woodynew.dcat-admin-kit.path/images/gotop.svg'))
<script>
Dcat.ready(function () {
    var id = 'dcat-admin-kit-back-to-top';
    var selector = '#' + id;

    if (!$(selector).length) {
        $('body').append(
            $('<button>', {
                id: id,
                type: 'button',
                title: 'Back to top',
                'aria-label': 'Back to top'
            }).css({
                position: 'fixed',
                padding: '0',
                border: '0',
                right: '5px',
                bottom: '300px',
                zIndex: 1000,
                display: 'none',
                opacity: 0.9,
                background: 'transparent',
                cursor: 'pointer'
            }).append($('<img>', {width: 60, src: @json($backToTopAsset), alt: ''}))
        );
    }

    $(window)
        .off('scroll.dcatAdminKitBackToTop')
        .on('scroll.dcatAdminKitBackToTop', function () {
            $(selector).toggle($(window).scrollTop() > window.screen.availHeight / 2);
        })
        .triggerHandler('scroll.dcatAdminKitBackToTop');

    $('body')
        .off('click.dcatAdminKitBackToTop', selector)
        .on('click.dcatAdminKitBackToTop', selector, function () {
            $('html, body').stop(true).animate({scrollTop: 0}, 250);
        });
});
</script>
