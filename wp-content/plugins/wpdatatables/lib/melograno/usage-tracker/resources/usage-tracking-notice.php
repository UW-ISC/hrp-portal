<?php

namespace WPDT;

\defined('ABSPATH') or die('No script kiddies please!');
/** @var \Melograno\UsageTracker\Collectors\ConsentNoticeCollectorInterface|null $usageTrackingCollector */
$usageTrackingCollector = $usageTrackingCollector ?? null;
/** @var array<string, mixed> $usageTrackingNoticePresentation */
$usageTrackingNoticePresentation = $usageTrackingNoticePresentation ?? [];
$usageTrackingAjaxPrefix = $usageTrackingCollector->getConsentNoticeAjaxPrefix();
$usageTrackingConsentNonce = \wp_create_nonce($usageTrackingAjaxPrefix . '_usage_tracking_consent');
$presentation = \array_merge(['accentColor' => '#4a3bd6', 'iconUrl' => '', 'iconAlt' => '', 'title' => '', 'description' => '', 'enableLabel' => '', 'learnMoreLabel' => '', 'learnMoreUrl' => '', 'dismissLabel' => \__('Dismiss this notice.', 'default'), 'spaPageId' => null, 'spaAppRootSelector' => null], $usageTrackingNoticePresentation);
$spaPageId = \is_string($presentation['spaPageId']) && $presentation['spaPageId'] !== '' ? $presentation['spaPageId'] : null;
$spaAppRootSelector = \is_string($presentation['spaAppRootSelector']) && $presentation['spaAppRootSelector'] !== '' ? $presentation['spaAppRootSelector'] : null;
$usageTrackingNoticeId = 'melograno-usage-tracking-notice-' . $usageTrackingAjaxPrefix;
?>
<div id="<?php 
echo \esc_attr($usageTrackingNoticeId);
?>" class="melograno-usage-tracking-notice melograno-usage-tracking-notice--<?php 
echo \esc_attr($usageTrackingAjaxPrefix);
?> melograno-usage-tracking-notice--pending">
<div
    class="melograno-usage-tracking-banner"
    role="region"
    aria-label="<?php 
\esc_attr_e('Usage tracking notice', 'default');
?>"
    style="--melograno-usage-tracking-accent: <?php 
echo \esc_attr($presentation['accentColor']);
?>"
>
    <div class="melograno-usage-tracking-banner__bar" aria-hidden="true"></div>

    <div class="melograno-usage-tracking-banner__inner">
        <div class="melograno-usage-tracking-banner__brand">
            <img
                src="<?php 
echo \esc_url($presentation['iconUrl']);
?>"
                alt="<?php 
echo \esc_attr($presentation['iconAlt']);
?>"
                width="32"
                height="32"
            />
        </div>

        <div class="melograno-usage-tracking-banner__content">
            <p class="melograno-usage-tracking-banner__title">
                <?php 
echo \esc_html($presentation['title']);
?>
            </p>
            <p class="melograno-usage-tracking-banner__description">
                <?php 
echo \esc_html($presentation['description']);
?>
            </p>
            <div class="melograno-usage-tracking-banner__actions">
                <button type="button" class="melograno-usage-tracking-banner__enable">
                    <?php 
echo \esc_html($presentation['enableLabel']);
?>
                </button>
                <a
                    class="melograno-usage-tracking-banner__learn-more"
                    href="<?php 
echo \esc_url($presentation['learnMoreUrl']);
?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php 
echo \esc_html($presentation['learnMoreLabel']);
?>
                </a>
            </div>
        </div>

        <button type="button" class="melograno-usage-tracking-banner__dismiss" aria-label="<?php 
echo \esc_attr($presentation['dismissLabel']);
?>">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
</div>
<script>
    (function ($) {
        var noticeId = <?php 
echo \wp_json_encode($usageTrackingNoticeId);
?>;
        var spaPageId = <?php 
echo \wp_json_encode($spaPageId);
?>;
        var spaAppRootSelector = <?php 
echo \wp_json_encode($spaAppRootSelector);
?>;

        function findMountTarget() {
            if (spaPageId) {
                var spaPage = document.getElementById(spaPageId);

                if (spaPage) {
                    return spaPage;
                }
            }

            return document.querySelector('#wpbody-content .wrap') || document.getElementById('wpbody-content');
        }

        function resolveAppRoot(pageElement) {
            if (!spaAppRootSelector) {
                return pageElement;
            }

            var descendant = pageElement.querySelector(spaAppRootSelector);

            if (descendant) {
                return descendant;
            }

            if (pageElement.matches && pageElement.matches(spaAppRootSelector)) {
                return pageElement;
            }

            return null;
        }

        function showNotice(root) {
            var target = findMountTarget();

            if (!target) {
                return;
            }

            root.classList.remove('melograno-usage-tracking-notice--pending');

            if (root.parentNode !== target) {
                target.insertBefore(root, target.firstChild);
            }
        }

        function mountNotice() {
            var SPA_READY_TIMEOUT_MS = 10000;
            var root = document.getElementById(noticeId);

            if (!root) {
                return;
            }

            if (!spaPageId) {
                showNotice(root);
                return;
            }

            var redesignPage = document.getElementById(spaPageId);

            if (!redesignPage) {
                return;
            }

            var revealed = false;
            var observers = [];
            var timeoutId;

            function stopWatching() {
                observers.forEach(function (observer) {
                    observer.disconnect();
                });
                observers = [];

                if (timeoutId) {
                    window.clearTimeout(timeoutId);
                    timeoutId = null;
                }
            }

            function revealWhenSpaReady() {
                if (revealed) {
                    return;
                }

                revealed = true;
                stopWatching();
                showNotice(root);
            }

            function watchAppRoot(appRoot) {
                if (appRoot.children.length > 0) {
                    revealWhenSpaReady();
                    return;
                }

                var appObserver = new MutationObserver(function () {
                    if (appRoot.children.length > 0) {
                        revealWhenSpaReady();
                    }
                });

                appObserver.observe(appRoot, { childList: true });
                observers.push(appObserver);
            }

            var appRoot = resolveAppRoot(redesignPage);

            if (!appRoot) {
                if (redesignPage.children.length > 0) {
                    revealWhenSpaReady();
                    return;
                }

                var pageObserver = new MutationObserver(function () {
                    var lateAppRoot = resolveAppRoot(redesignPage);

                    if (!lateAppRoot) {
                        return;
                    }

                    pageObserver.disconnect();
                    observers = observers.filter(function (observer) {
                        return observer !== pageObserver;
                    });
                    watchAppRoot(lateAppRoot);
                });

                pageObserver.observe(redesignPage, { childList: true, subtree: true });
                observers.push(pageObserver);
            } else {
                watchAppRoot(appRoot);

                if (revealed) {
                    return;
                }
            }

            timeoutId = window.setTimeout(revealWhenSpaReady, SPA_READY_TIMEOUT_MS);
        }

        $(function () {
            mountNotice();

            var $root = $(document.getElementById(noticeId));
            var $banner = $root.find('.melograno-usage-tracking-banner');

            $banner.find('.melograno-usage-tracking-banner__dismiss').on('click', function (e) {
                e.preventDefault();

                $.post(ajaxurl, {
                    action: '<?php 
echo \esc_js($usageTrackingAjaxPrefix);
?>_dismiss_usage_tracking_notice',
                    _ajax_nonce: '<?php 
echo \esc_js($usageTrackingConsentNonce);
?>'
                }).done(function () {
                    $root.slideUp('fast');
                });
            });

            $banner.find('.melograno-usage-tracking-banner__enable').on('click', function (e) {
                e.preventDefault();

                $.post(ajaxurl, {
                    action: '<?php 
echo \esc_js($usageTrackingAjaxPrefix);
?>_enable_usage_tracking',
                    _ajax_nonce: '<?php 
echo \esc_js($usageTrackingConsentNonce);
?>'
                }).done(function () {
                    $root.slideUp('fast');
                });
            });
        });
    })(jQuery);
</script>
<?php 
