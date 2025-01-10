(function($) {
    'use strict';

    // Store original functions to restore them later
    const originalPushState = history.pushState;
    const originalReplaceState = history.replaceState;

    // Cookie management
    const GCCookieConsent = {
        cookieConsent: null,
        
        init: function() {
            console.log('Initializing GCCookieConsent');
            this.cookieConsent = this.getCookie('gcc_cookie_consent');
            console.log('Current cookie value:', this.cookieConsent);
            console.log('jQuery loaded:', typeof jQuery !== 'undefined'); // Check if jQuery is available
            console.log('Banner element exists:', $('#gcc-cookie-banner').length); // Check if banner exists in DOM
            
            // Always setup event listeners
            this.setupEventListeners();
            this.handleScriptBlocking();
            
            // Show banner if no consent
            if (!this.cookieConsent) {
                console.log('No consent found, showing banner');
                this.showBanner();
            } else {
                console.log('Consent found:', this.cookieConsent);
            }
        },

        // Get cookie value
        getCookie: function(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return JSON.parse(parts.pop().split(';').shift());
            return null;
        },

        // Set cookie with consent preferences
        setConsentCookie: function(preferences) {
            const value = JSON.stringify(preferences);
            const date = new Date();
            date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000)); // 1 year
            document.cookie = `gcc_cookie_consent=${value}; expires=${date.toUTCString()}; path=/; SameSite=Strict`;
            this.cookieConsent = preferences;
        },

        // Remove all non-necessary cookies
        removeDisabledCookies: function() {
            const cookies = document.cookie.split(';');
            const consent = this.cookieConsent || {};
            
            cookies.forEach(cookie => {
                const cookieName = cookie.split('=')[0].trim();
                const cookieCategory = this.determineCookieCategory(cookieName);
                
                if (cookieCategory !== 'necessary' && !consent[cookieCategory]) {
                    this.deleteCookie(cookieName);
                }
            });
        },

        // Delete specific cookie
        deleteCookie: function(name) {
            document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
            document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=${window.location.hostname};`;
        },

        // Determine cookie category based on name
        determineCookieCategory: function(name) {
            name = name.toLowerCase();
            if (name.includes('ga') || name.includes('analytics')) return 'analytics';
            if (name.includes('ad') || name.includes('fbp')) return 'marketing';
            if (name.includes('wordpress') || name.includes('wp-') || name === 'phpsessid') return 'necessary';
            return 'others';
        },

        // Handle script blocking
        handleScriptBlocking: function() {
            const consent = this.cookieConsent || {};
            
            // Block scripts based on their type
            document.querySelectorAll('script[type="text/plain"]').forEach(script => {
                const category = script.getAttribute('data-cookiecategory');
                if (consent[category]) {
                    this.enableScript(script);
                }
            });

            // Modify history API to prevent analytics tracking
            if (!consent.analytics) {
                this.blockHistoryAPI();
            }
        },

        // Enable specific script
        enableScript: function(script) {
            const newScript = document.createElement('script');
            Array.from(script.attributes).forEach(attr => {
                if (attr.name !== 'type') {
                    newScript.setAttribute(attr.name, attr.value);
                }
            });
            newScript.type = 'text/javascript';
            newScript.innerHTML = script.innerHTML;
            script.parentNode.replaceChild(newScript, script);
        },

        // Block History API for analytics
        blockHistoryAPI: function() {
            history.pushState = function() {
                return originalPushState.apply(history, arguments);
            };
            history.replaceState = function() {
                return originalReplaceState.apply(history, arguments);
            };
        },

        // Show cookie banner if no consent is stored
        showBanner: function() {
            const $banner = $('#gcc-cookie-banner');
            if ($banner.length) {
                console.log('Banner element found, showing');
                $banner.fadeIn(300);
            } else {
                console.error('Banner element not found!');
            }
        },

        // Setup event listeners for banner buttons
        setupEventListeners: function() {
            $('#gcc-accept-all').on('click', () => {
                const preferences = {
                    necessary: true,
                    analytics: true,
                    marketing: true,
                    others: true
                };
                this.setConsentCookie(preferences);
                this.handleScriptBlocking();
                $('#gcc-cookie-banner').hide();
                location.reload();
            });

            $('#gcc-reject-all').on('click', () => {
                const preferences = {
                    necessary: true,
                    analytics: false,
                    marketing: false,
                    others: false
                };
                this.setConsentCookie(preferences);
                this.removeDisabledCookies();
                $('#gcc-cookie-banner').hide();
                location.reload();
            });

            $('#gcc-save-preferences').on('click', () => {
                const preferences = {
                    necessary: true,
                    analytics: $('#gcc-analytics').is(':checked'),
                    marketing: $('#gcc-marketing').is(':checked'),
                    others: $('#gcc-others').is(':checked')
                };
                this.setConsentCookie(preferences);
                this.handleScriptBlocking();
                this.removeDisabledCookies();
                $('#gcc-cookie-banner').hide();
                location.reload();
            });

            $('.consentsettings').on('click', () => {
                $('#gcc-cookie-banner').show();
            });

            // Add toggle functionality for cookie details
            $('.gcc-toggle-details').on('click', function(e) {
                e.preventDefault();
                const $this = $(this);
                const $details = $this.next('.gcc-cookie-details');
                const isExpanded = $this.attr('aria-expanded') === 'true';
                
                // Toggle aria-expanded
                $this.attr('aria-expanded', !isExpanded);
                
                // Toggle visibility with animation
                $details.slideToggle(200);
                
                // Update button text and icon
                const buttonText = isExpanded ? 
                    gccData.translations.showCookies : 
                    gccData.translations.hideCookies;
                
                $this.html(`${buttonText} <span class="gcc-toggle-icon">▼</span>`);
                
                // Add class for icon rotation
                $this.find('.gcc-toggle-icon').css('transform', 
                    isExpanded ? 'rotate(0deg)' : 'rotate(180deg)'
                );
            });
        }
    };

    // Initialize when DOM is fully loaded
    $(function() {
        console.log('DOM ready, initializing consent manager');
        GCCookieConsent.init();
    });

})(jQuery); 