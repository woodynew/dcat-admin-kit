<style>
select.dcat-kit-locale.form-control {
    box-sizing: border-box;
    height: 34px;
    min-height: 34px;
    min-width: 120px;
    width: auto;
    padding: 4px 26px 4px 10px;
    font-size: 14px;
    line-height: normal;
    box-shadow: none;
    appearance: auto;
}
</style>
<ul class="nav navbar-nav float-right">
    <li class="nav-item d-flex align-items-center px-1">
        <select class="form-control dcat-kit-locale" aria-label="{{ trans('woodynew.dcat-admin-kit::kit.language') }}"
                title="{{ trans('woodynew.dcat-admin-kit::kit.language_reload') }}"
                data-url="{{ admin_url('kit/locale') }}" data-current="{{ $locale }}">
            @foreach($locales as $value => $label)
                <option value="{{ $value }}" {{ $locale === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </li>
</ul>
<script>
$(document).off('change.dcatKitLocale', '.dcat-kit-locale').on('change.dcatKitLocale', '.dcat-kit-locale', function () {
    var select = $(this);
    select.prop('disabled', true);
    $.ajax({
        url: select.data('url'),
        method: 'POST',
        dataType: 'json',
        data: {locale: select.val(), _token: {!! json_encode(csrf_token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}},
        success: function () {
            var target = window;
            try {
                if (window.top.location.origin === window.location.origin) target = window.top;
            } catch (error) {}
            target.location.reload();
        },
        error: function () {
            select.val(select.data('current')).prop('disabled', false);
            Dcat.error({!! json_encode(trans('woodynew.dcat-admin-kit::kit.language_failed'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!});
        }
    });
});
</script>
