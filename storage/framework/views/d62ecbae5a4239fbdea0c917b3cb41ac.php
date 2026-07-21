<?php
    $playStoreUrl = Setting::get('store_link_android', 'https://play.google.com/store/apps');
    $appStoreUrl = Setting::get('store_link_ios', '#');
?>

<!-- PWA Installer Component Container -->
<div id="pwa-installer-root" style="display: none;">
    <!-- ── 1. BOTTOM SHEET (MODAL INSTALL) ── -->
    <div id="pwa-bottom-sheet-overlay" class="pwa-overlay" onclick="closePwaBottomSheet()"></div>
    <div id="pwa-bottom-sheet" class="pwa-bottom-sheet">
        <div class="pwa-sheet-handle"></div>
        <div class="pwa-sheet-content">
            <div class="pwa-app-info">
                <img src="<?php echo e(asset('logo.png')); ?>" alt="PicMe225 Logo" class="pwa-sheet-logo">
                <div>
                    <h3 class="pwa-sheet-title">Installer PicMe225</h3>
                    <p class="pwa-sheet-subtitle">Profitez d'une expérience plus rapide, sans réseau et en plein écran.</p>
                </div>
            </div>

            <!-- Content tailored dynamically by JS based on platform -->
            <div id="pwa-platform-content">
                <!-- Android with Play Store -->
                <div class="pwa-platform-section" id="pwa-android-section" style="display: none;">
                    <a href="<?php echo e($playStoreUrl); ?>" target="_blank" class="pwa-btn pwa-btn-primary pwa-ripple" onclick="trackInstall('play_store')">
                        <i class="fa fa-android"></i> Télécharger depuis Google Play
                    </a>
                    <button class="pwa-btn pwa-btn-secondary pwa-ripple" onclick="triggerPwaInstall()">
                        <i class="fa fa-download"></i> Installer la version Web (PWA)
                    </button>
                    <p class="pwa-text-note">Vous pouvez également utiliser la version Web instantanément.</p>
                </div>

                <!-- iOS Safari -->
                <div class="pwa-platform-section" id="pwa-ios-section" style="display: none;">
                    <div class="pwa-ios-instructions">
                        <p class="pwa-ios-step"><span class="pwa-badge-number">1</span> Appuyez sur le bouton de partage <i class="fa fa-share-square-o" style="color:#C9A84C; font-size:16px;"></i> dans Safari.</p>
                        <p class="pwa-ios-step"><span class="pwa-badge-number">2</span> Sélectionnez <strong>Sur l'écran d'accueil</strong> <i class="fa fa-plus-square-o" style="color:#C9A84C; font-size:16px;"></i> dans la liste.</p>
                    </div>
                </div>

                <!-- Desktop / Other -->
                <div class="pwa-platform-section" id="pwa-desktop-section" style="display: none;">
                    <button class="pwa-btn pwa-btn-primary pwa-ripple" onclick="triggerPwaInstall()">
                        <i class="fa fa-desktop"></i> Installer maintenant
                    </button>
                </div>
            </div>

            <div class="pwa-sheet-actions">
                <button class="pwa-btn pwa-btn-flat" onclick="closePwaBottomSheet()">Plus tard</button>
            </div>
        </div>
    </div>

    <!-- ── 2. DISCRETE BANNER (TRIGGERS AFTER 30s) ── -->
    <div id="pwa-discrete-banner" class="pwa-discrete-banner">
        <div class="pwa-banner-left">
            <div class="pwa-banner-icon-wrap">
                <img src="<?php echo e(asset('logo.png')); ?>" alt="Logo" class="pwa-banner-logo">
                <div class="pwa-banner-icon-pulse"></div>
            </div>
            <div class="pwa-banner-text">
                <strong>Installez PicMe225</strong>
                <span>✓ Plus rapide ✓ Plein écran ✓ Notifications</span>
            </div>
        </div>
        <div class="pwa-banner-right">
            <button class="pwa-btn-banner-close" onclick="closePwaDiscreteBanner()"><i class="fa fa-times"></i></button>
            <button class="pwa-btn-banner-action pwa-ripple" onclick="openPwaBottomSheet('banner')">Installer</button>
        </div>
    </div>

    <!-- ── 3. AUTO-UPDATE TOAST ── -->
    <div id="pwa-update-toast" class="pwa-update-toast">
        <div class="pwa-toast-content">
            <i class="fa fa-refresh pwa-spin-icon"></i>
            <span>Nouvelle version disponible !</span>
        </div>
        <button class="pwa-btn-toast-update pwa-ripple" onclick="updatePwaApp()">Mettre à jour</button>
    </div>
</div>

<!-- PWA Installer CSS Styles -->
<style>
    /* ─── Styles CSS Premium PWA ─── */
    .pwa-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0);
        z-index: 999998;
        display: none;
        transition: background 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .pwa-overlay.active {
        display: block;
        background: rgba(10, 22, 40, 0.6);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    /* Bottom Sheet */
    .pwa-bottom-sheet {
        position: fixed;
        left: 0;
        right: 0;
        bottom: -100%;
        background: #0D1B2A;
        border-top: 3px solid #C9A84C;
        border-radius: 24px 24px 0 0;
        padding: 24px;
        z-index: 999999;
        transition: bottom 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 -8px 32px rgba(0,0,0,0.3);
        color: #FFFFFF;
        max-width: 540px;
        margin: 0 auto;
    }
    .pwa-bottom-sheet.active {
        bottom: 0;
    }
    .pwa-sheet-handle {
        width: 48px;
        height: 5px;
        background: rgba(255,255,255,0.2);
        border-radius: 3px;
        margin: -8px auto 20px auto;
    }
    .pwa-app-info {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }
    .pwa-sheet-logo {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        border: 2px solid #C9A84C;
        object-fit: cover;
    }
    .pwa-sheet-title {
        font-size: 20px;
        font-weight: 800;
        color: #FFFFFF;
        letter-spacing: -0.5px;
    }
    .pwa-sheet-subtitle {
        font-size: 13px;
        color: rgba(255,255,255,0.7);
        margin-top: 4px;
        line-height: 1.4;
    }

    /* Buttons & Actions */
    .pwa-platform-section {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .pwa-btn {
        width: 100%;
        padding: 14px 20px;
        border-radius: 14px;
        font-size: 15px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: transform 0.2s, box-shadow 0.2s, background-color 0.2s;
    }
    .pwa-btn:active {
        transform: scale(0.97);
    }
    .pwa-btn-primary {
        background: linear-gradient(135deg, #C9A84C, #E2C06E);
        color: #0D1B2A;
        box-shadow: 0 4px 15px rgba(201, 168, 76, 0.3);
    }
    .pwa-btn-secondary {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #FFFFFF;
    }
    .pwa-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.12);
    }
    .pwa-btn-flat {
        background: transparent;
        color: rgba(255, 255, 255, 0.6);
        padding: 10px;
        font-size: 14px;
    }
    .pwa-btn-flat:hover {
        color: #FFFFFF;
    }
    .pwa-text-note {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.45);
        text-align: center;
        margin-top: 4px;
    }

    /* iOS specifics */
    .pwa-ios-instructions {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .pwa-ios-step {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .pwa-badge-number {
        width: 24px;
        height: 24px;
        background: #C9A84C;
        color: #0D1B2A;
        font-weight: 800;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }

    /* Discrete Banner */
    .pwa-discrete-banner {
        position: fixed;
        bottom: -100px;
        left: 16px;
        right: 16px;
        background: #0D1B2A;
        border: 1.5px solid #C9A84C;
        border-radius: 18px;
        padding: 14px 16px;
        z-index: 999990;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        transition: bottom 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        max-width: 480px;
        margin: 0 auto;
        color: #FFFFFF;
    }
    .pwa-discrete-banner.active {
        bottom: 16px;
    }
    .pwa-banner-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    .pwa-banner-icon-wrap {
        position: relative;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
    }
    .pwa-banner-logo {
        width: 100%;
        height: 100%;
        border-radius: 8px;
        border: 1px solid #C9A84C;
        z-index: 2;
        position: relative;
    }
    .pwa-banner-icon-pulse {
        position: absolute;
        inset: -4px;
        border-radius: 10px;
        background: rgba(201, 168, 76, 0.2);
        animation: pulse-banner-logo 2s infinite ease-in-out;
        z-index: 1;
    }
    .pwa-banner-text {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .pwa-banner-text strong {
        font-size: 13.5px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pwa-banner-text span {
        font-size: 11px;
        color: rgba(255,255,255,0.6);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 1px;
    }
    .pwa-btn-banner-action {
        background: #C9A84C;
        color: #0D1B2A;
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
    }
    .pwa-btn-banner-close {
        background: transparent;
        border: none;
        color: rgba(255,255,255,0.4);
        font-size: 16px;
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pwa-btn-banner-close:hover {
        color: #FFFFFF;
    }
    .pwa-banner-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    /* Auto-Update Toast */
    .pwa-update-toast {
        position: fixed;
        top: -100px;
        left: 50%;
        transform: translateX(-50%);
        background: #2E7D32; /* Success green */
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 14px;
        padding: 12px 18px;
        z-index: 1000000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.35);
        color: #FFFFFF;
        width: calc(100% - 32px);
        max-width: 400px;
        transition: top 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .pwa-update-toast.active {
        top: 16px;
    }
    .pwa-toast-content {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13.5px;
        font-weight: 600;
    }
    .pwa-spin-icon {
        animation: spin-toast 2s infinite linear;
        color: #FFFFFF;
    }
    .pwa-btn-toast-update {
        background: #FFFFFF;
        color: #2E7D32;
        border: none;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    /* Ripple Effect & Animations */
    .pwa-ripple {
        position: relative;
        overflow: hidden;
    }
    @keyframes pulse-banner-logo {
        0% { transform: scale(0.95); opacity: 0.6; }
        50% { transform: scale(1.15); opacity: 1; }
        100% { transform: scale(1.3); opacity: 0; }
    }
    @keyframes spin-toast {
        100% { transform: rotate(360deg); }
    }

    /* Responsive Adjustments for Mobile Bottom Nav overlay */
    @media (max-width: 768px) {
        .pwa-discrete-banner.active {
            bottom: calc(75px + env(safe-area-inset-bottom, 0px));
        }
    }
</style>

<!-- PWA Installer JavaScript Logic -->
<script>
    (function() {
        var deferredPrompt;
        var pwaInstalledChecked = false;
        var platform = detectPlatform();

        // 1. Detect device & OS details
        function detectPlatform() {
            var ua = navigator.userAgent || navigator.vendor || window.opera;
            if (/android/i.test(ua)) return 'android';
            if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) return 'ios';
            return 'desktop';
        }

        // 2. Check if already running in standalone/installed mode
        function isStandalone() {
            return (window.matchMedia('(display-mode: standalone)').matches || 
                    window.navigator.standalone === true || 
                    document.referrer.includes('source=pwa'));
        }

        // 3. Document ready handler
        document.addEventListener("DOMContentLoaded", function() {
            initPwaInstaller();
        });

        function initPwaInstaller() {
            // If already installed/standalone, do not show any install banners or sheets
            if (isStandalone()) {
                window.isPWAInstalled = true;
                hidePwaInstallElements();
                return;
            }

            // Show appropriate section inside Bottom Sheet
            document.getElementById('pwa-installer-root').style.display = 'block';
            if (platform === 'android') {
                document.getElementById('pwa-android-section').style.display = 'block';
            } else if (platform === 'ios') {
                document.getElementById('pwa-ios-section').style.display = 'block';
            } else {
                document.getElementById('pwa-desktop-section').style.display = 'block';
            }

            // Register beforeinstallprompt
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredPrompt = e;
                console.log('beforeinstallprompt event triggered.');
                
                // Show installation buttons / banners
                showPwaTriggerElements();
            });

            // Listen for appinstalled
            window.addEventListener('appinstalled', function(e) {
                console.log('PWA app installed successfully.');
                window.isPWAInstalled = true;
                hidePwaInstallElements();
                closePwaBottomSheet();
            });

            // Start 30 seconds timer for discrete banner
            setTimeout(function() {
                // Show banner if not standalone and installable
                if (!isStandalone() && !window.isPWAInstalled) {
                    var banner = document.getElementById('pwa-discrete-banner');
                    if (banner && !localStorage.getItem('pwa_banner_dismissed')) {
                        banner.classList.add('active');
                    }
                }
            }, 30000);

            // Listen for service worker updates
            initServiceWorkerUpdater();
        }

        // Trigger PWA Install
        window.triggerPwaInstall = async function() {
            if (!deferredPrompt) {
                alert("Installation automatique indisponible. Vous pouvez installer via les paramètres du navigateur ou utiliser la version web.");
                return;
            }
            deferredPrompt.prompt();
            var choice = await deferredPrompt.userChoice;
            if (choice.outcome === 'accepted') {
                console.log('User accepted the PWA install prompt');
                window.isPWAInstalled = true;
                hidePwaInstallElements();
            } else {
                console.log('User dismissed the PWA install prompt');
            }
            deferredPrompt = null;
            closePwaBottomSheet();
        };

        // Open Bottom Sheet
        window.openPwaBottomSheet = function(triggerSource) {
            console.log('Opening PWA Bottom Sheet via: ' + triggerSource);
            var sheet = document.getElementById('pwa-bottom-sheet');
            var overlay = document.getElementById('pwa-bottom-sheet-overlay');
            if (sheet && overlay) {
                sheet.classList.add('active');
                overlay.classList.add('active');
                
                // Hide the discrete banner while modal is open
                closePwaDiscreteBanner();
            }
        };

        // Close Bottom Sheet
        window.closePwaBottomSheet = function() {
            var sheet = document.getElementById('pwa-bottom-sheet');
            var overlay = document.getElementById('pwa-bottom-sheet-overlay');
            if (sheet && overlay) {
                sheet.classList.remove('active');
                overlay.classList.remove('active');
            }
        };

        // Close Discrete Banner
        window.closePwaDiscreteBanner = function() {
            var banner = document.getElementById('pwa-discrete-banner');
            if (banner) {
                banner.classList.remove('active');
                localStorage.setItem('pwa_banner_dismissed', 'true');
            }
        };

        // Hide PWA install triggers globally
        function hidePwaInstallElements() {
            var banner = document.getElementById('pwa-discrete-banner');
            if (banner) banner.style.display = 'none';
            var buttons = document.querySelectorAll('.pwa-install-btn');
            buttons.forEach(function(btn) {
                btn.style.display = 'none';
            });
        }

        // Show PWA install triggers when installable
        function showPwaTriggerElements() {
            var buttons = document.querySelectorAll('.pwa-install-btn');
            buttons.forEach(function(btn) {
                btn.style.display = 'inline-flex';
            });
        }

        // Track installs or redirects
        window.trackInstall = function(type) {
            console.log('Tracking install/redirect type: ' + type);
            // Optionally make an API log call
        };

        // ── Service Worker Updater ──
        var newWorker;
        function initServiceWorkerUpdater() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistration().then(function(reg) {
                    if (!reg) return;

                    // Detect waiting worker on page load
                    if (reg.waiting) {
                        newWorker = reg.waiting;
                        showUpdateToast();
                    }

                    // Listen for updates
                    reg.addEventListener('updatefound', function() {
                        var installingWorker = reg.installing;
                        if (!installingWorker) return;

                        installingWorker.addEventListener('statechange', function() {
                            if (installingWorker.state === 'installed') {
                                if (navigator.serviceWorker.controller) {
                                    newWorker = installingWorker;
                                    showUpdateToast();
                                }
                            }
                        });
                    });
                });

                // Reload page after skipWaiting activates
                var refreshing;
                navigator.serviceWorker.addEventListener('controllerchange', function() {
                    if (refreshing) return;
                    window.location.reload();
                    refreshing = true;
                });
            }
        }

        function showUpdateToast() {
            var toast = document.getElementById('pwa-update-toast');
            if (toast) {
                toast.classList.add('active');
            }
        }

        window.updatePwaApp = function() {
            if (newWorker) {
                newWorker.postMessage({ type: 'SKIP_WAITING' });
            }
        };

        // Backwards compatibility with buttons calling window.installPWA
        window.installPWA = function(fallbackUrl) {
            openPwaBottomSheet('button');
        };
    })();
</script>
<?php /**PATH C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views/common/pwa_installer.blade.php ENDPATH**/ ?>