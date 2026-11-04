{{-- Driver-Only PWA iOS Safari Install Guide & Service Worker Registration --}}
<div id="ios-pwa-banner" style="display: none; position: fixed; bottom: 20px; left: 16px; right: 16px; margin: 0 auto; max-width: 440px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 12px 30px rgba(0, 0, 0, 0.16); padding: 18px; z-index: 999999; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="{{ asset('assets/images/logo/carsafe-icon-192.png') }}" alt="CarSafe" style="width: 46px; height: 46px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div>
                <h6 style="margin: 0; font-size: 15px; font-weight: 700; color: #1e293b;">Install CarSafe Driver App</h6>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;">Get full-screen access and faster trip loading</p>
            </div>
        </div>
        <button id="dismiss-ios-banner" type="button" aria-label="Close" style="background: none; border: none; font-size: 22px; line-height: 1; color: #94a3b8; cursor: pointer; padding: 0 4px;">&times;</button>
    </div>

    <div style="margin-top: 14px; padding: 12px; background: #f8fafc; border-radius: 10px; font-size: 13px; color: #334155; line-height: 1.5;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <span>1. Tap the Safari <strong>Share</strong> button below</span>
            <!-- iOS Safari Share Box-Arrow Icon -->
            <svg style="width: 20px; height: 20px; fill: #007aff; flex-shrink: 0;" viewBox="0 0 24 24">
                <path d="M12 2a1 1 0 0 1 1 1v10.59l2.3-2.3a1 1 0 1 1 1.4 1.42l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 1.4-1.42L11 13.59V3a1 1 0 0 1 1-1zM4 14a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-4a1 1 0 1 1 2 0v4a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-4a1 1 0 0 1 1-1z"/>
            </svg>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span>2. Scroll down and tap <strong>'Add to Home Screen'</strong></span>
            <!-- Add to Home Screen Plus Square Icon -->
            <svg style="width: 18px; height: 18px; fill: #334155; flex-shrink: 0;" viewBox="0 0 24 24">
                <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-1 16H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1zm-5-10h-2v3H8v2h3v3h2v-3h3v-2h-3V9z"/>
            </svg>
        </div>
    </div>
</div>

<script>
    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(function(reg) {
                    console.log('[PWA] Service Worker registered for Driver. Scope:', reg.scope);
                })
                .catch(function(err) {
                    console.error('[PWA] Service Worker registration failed:', err);
                });
        });
    }

    // 2. iOS Safari Custom Install Prompt
    document.addEventListener("DOMContentLoaded", function () {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                      (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

        const isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(navigator.userAgent);
        const isInStandalone = ('standalone' in window.navigator && window.navigator.standalone === true) ||
                               window.matchMedia('(display-mode: standalone)').matches;

        const dismissedTime = localStorage.getItem('carsafe_driver_ios_dismissed');
        const sevenDays = 7 * 24 * 60 * 60 * 1000;
        const isRecentlyDismissed = dismissedTime && (Date.now() - parseInt(dismissedTime, 10) < sevenDays);

        // Show banner only if on iOS Safari, not already installed, and not dismissed within 7 days
        if (isIOS && isSafari && !isInStandalone && !isRecentlyDismissed) {
            const banner = document.getElementById('ios-pwa-banner');
            if (banner) {
                // Show after brief delay so driver page loads smoothly first
                setTimeout(function() {
                    banner.style.display = 'block';
                }, 1500);

                document.getElementById('dismiss-ios-banner').addEventListener('click', function () {
                    banner.style.display = 'none';
                    localStorage.setItem('carsafe_driver_ios_dismissed', Date.now().toString());
                });
            }
        }
    });
</script>
