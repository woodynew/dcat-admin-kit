<?php

namespace Woodynew\DcatAdminKit\Grid\RowActions;

use Dcat\Admin\Grid\RowAction;
use InvalidArgumentException;

class OpenIFrameTab extends RowAction
{
    protected $title = 'OpenIFrameTab';

    private $params = [];

    public function __construct($title = null, $params = [])
    {
        if (empty($params['toUrl'])) {
            throw new InvalidArgumentException('OpenIFrameTab requires a non-empty toUrl parameter.');
        }

        $this->params = $params;

        if (empty($this->params['tabUrl'])) {
            $this->params['tabUrl'] = explode('?', $this->params['toUrl'], 2)[0];
        }

        parent::__construct($title);
    }

    protected function script()
    {
        $selector = json_encode($this->selector(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $fallbackTitle = json_encode(
            (string) ($this->params['title'] ?? $this->title),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
        );

        return <<<JS
$('body')
    .off('click.dcatAdminKitIframeTab', {$selector})
    .on('click.dcatAdminKitIframeTab', {$selector}, function (event) {
        event.preventDefault();

        var target = $(this);
        var tabUrl = String(target.attr('data-tab-url') || '');
        var toUrl = String(target.attr('data-to-url') || '');

        try {
            var parentWindow = window.parent;
            var iframeTabParent = parentWindow && parentWindow.iframeTabParent;

            if (!iframeTabParent || !iframeTabParent.iframeTab || !iframeTabParent.elements) {
                window.location.href = toUrl;
                return;
            }

            var id = iframeTabParent.iframeTab.generateID(tabUrl);
            var iframeHomeId = 'iframe-home-' + id;
            var existingTab = iframeTabParent.elements.iframe_tab.find('#' + iframeHomeId);

            if (existingTab.length) {
                iframeTabParent.elements.iframe_tabContent.find('#iframe-' + id + ' iframe').attr('src', toUrl);
                existingTab.click();
                return;
            }

            var title = {$fallbackTitle};
            var links = iframeTabParent.elements.menu_link || [];

            for (var index = 0; index < links.length; index++) {
                if (links[index].href && links[index].href.indexOf(tabUrl) !== -1) {
                    title = links[index].innerText.replace(/[\\n  ]/g, '');
                    break;
                }
            }

            var pageHtml = '&nbsp;<i class="fa fa-fw feather icon-circle"></i>&nbsp;<p>' + $('<div>').text(title).html() + '</p>';
            var active = iframeTabParent.iframeTab.findIframeTabActiveElement();
            iframeTabParent.swiper.appendSlide(iframeTabParent.iframeTabTemplate.tabItem(pageHtml, id));
            iframeTabParent.elements.iframe_tabContent.append(iframeTabParent.iframeTabTemplate.tabContentItem(toUrl, id));
            iframeTabParent.swiper.updateSlides();
            iframeTabParent.iframeTab.removeTabBarStyle();
            iframeTabParent.iframeTab.cacheUpdateTabBar(active);
            iframeTabParent.elements.iframe_tab.find('#' + iframeHomeId).click();
        } catch (error) {
            window.location.href = toUrl;
        }
    });
JS;
    }

    public function html()
    {
        $this->setHtmlAttribute([
            'data-id' => $this->getKey(),
            'data-tab-url' => $this->params['tabUrl'],
            'data-to-url' => $this->params['toUrl'],
        ]);

        return parent::html();
    }
}
