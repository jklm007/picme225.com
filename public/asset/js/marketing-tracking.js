/**
 * PicMe Pro — Marketing Tracking & Events
 * 
 * Tracks: whatsapp_click, call_click, cta_click
 * Captures UTM params from URL
 * 100% independent from core app JS
 */

(function() {
    'use strict';

    // --- Helper: send GA4 event ---
    function trackEvent(eventName, params) {
        params = params || {};
        if (typeof gtag === 'function') {
            gtag('event', eventName, params);
        }
        // Also push to dataLayer for GTM
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': eventName,
            ...params
        });
    }

    // --- Track all WhatsApp clicks ---
    document.addEventListener('click', function(e) {
        var target = e.target.closest('a');
        if (!target) return;

        var href = target.getAttribute('href') || '';

        // WhatsApp click
        if (href.indexOf('wa.me') !== -1 || href.indexOf('whatsapp') !== -1) {
            trackEvent('whatsapp_click', {
                'event_category': 'conversion',
                'event_label': target.id || 'whatsapp_link',
                'link_url': href,
                'page_title': document.title
            });
        }

        // Phone call click
        if (href.indexOf('tel:') === 0) {
            trackEvent('call_click', {
                'event_category': 'conversion',
                'event_label': target.id || 'call_link',
                'phone_number': href.replace('tel:', ''),
                'page_title': document.title
            });
        }
    });

    // --- UTM Parameter Capture ---
    function getUTM(param) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param) || '';
    }

    // Store UTMs in sessionStorage for cross-page tracking
    var utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
    utmKeys.forEach(function(key) {
        var val = getUTM(key);
        if (val) {
            sessionStorage.setItem(key, val);
        }
    });

    // --- Track page view with service type ---
    var pageType = document.querySelector('meta[name="service-type"]');
    if (pageType) {
        trackEvent('marketing_page_view', {
            'service_type': pageType.getAttribute('content'),
            'utm_source': sessionStorage.getItem('utm_source') || 'direct',
            'utm_campaign': sessionStorage.getItem('utm_campaign') || ''
        });
    }

    // --- Scroll depth tracking (25%, 50%, 75%, 100%) ---
    var scrollMarks = { 25: false, 50: false, 75: false, 100: false };
    window.addEventListener('scroll', function() {
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var docHeight = document.documentElement.scrollHeight - window.innerHeight;
        if (docHeight <= 0) return;
        var scrollPercent = Math.round((scrollTop / docHeight) * 100);

        [25, 50, 75, 100].forEach(function(mark) {
            if (scrollPercent >= mark && !scrollMarks[mark]) {
                scrollMarks[mark] = true;
                trackEvent('scroll_depth', {
                    'event_category': 'engagement',
                    'event_label': mark + '%',
                    'value': mark
                });
            }
        });
    });

})();
