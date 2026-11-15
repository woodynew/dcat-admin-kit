<style>
.dcat-kit-locale-control {
    position: relative;
    display: inline-flex;
    color: #64748b;
}
.dcat-kit-locale-control::after {
    content: '';
    position: absolute;
    right: 12px;
    top: 50%;
    width: 6px;
    height: 6px;
    border-right: 1.5px solid currentColor;
    border-bottom: 1.5px solid currentColor;
    transform: translateY(-70%) rotate(45deg);
    pointer-events: none;
}
body.dark-mode .dcat-kit-locale-control {
    color: #c5cddd;
}
select.dcat-kit-locale.form-control {
    box-sizing: border-box;
    height: 34px;
    min-height: 34px;
    min-width: 120px;
    width: auto;
    padding: 4px 34px 4px 12px;
    border-radius: 6px;
    font-size: 14px;
    line-height: normal;
    box-shadow: none;
    -webkit-appearance: none;
    appearance: none;
    background-image: none;
    cursor: pointer;
}
@media (forced-colors: active) {
    select.dcat-kit-locale.form-control { appearance: auto; }
    .dcat-kit-locale-control::after { display: none; }
}
</style>
<ul class="nav navbar-nav float-right">
    <li class="nav-item d-flex align-items-center px-1">
        <span class="dcat-kit-locale-control">
        <select class="form-control dcat-kit-locale" aria-label="{{ trans('woodynew.dcat-admin-kit::kit.language') }}"
                title="{{ trans('woodynew.dcat-admin-kit::kit.language_reload') }}"
                data-url="{{ admin_url('kit/locale') }}" data-current="{{ $locale }}">
            @foreach($locales as $value => $label)
                <option value="{{ $value }}" {{ $locale === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        </span>
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
