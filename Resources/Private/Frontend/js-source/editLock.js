(function (window) {

    'use strict';

    const document = window.document;

    const proposalForm = document.querySelector('form[name="proposal"]');

    if (!proposalForm) {
        return;
    }

    /*
     * Diagnostic logging, silent unless it is asked for: the form carries
     * data-oap-editlock-debug, or the url the parameter oap-editlock-debug. Coordinating
     * several tabs is hard to follow from the outside, so the protocol stays in the file and
     * an installation switches it on when it needs it - per request through the url, or for
     * good through the attribute.
     */
    const debug = proposalForm.dataset.oapEditlockDebug !== undefined
        || new URLSearchParams(window.location.search).has('oap-editlock-debug');

    const log = function () {
        if (!debug) {
            return;
        }

        // eslint-disable-next-line no-console
        console.log.apply(console, arguments);
    };

    log('[editLock] script loaded', proposalForm);

    const tokenInput = proposalForm.querySelector('input[name$="[editLockToken]"], input[name="editLockToken"]');
    const proposalUid = proposalForm.dataset.oapProposal;

    log('[editLock] lookup', {
        tokenInput: tokenInput,
        proposalUid: proposalUid
    });
    if (!tokenInput || !proposalUid) {
        return;
    }

    const token = tokenInput.value;

    if (!token) {
        return;
    }

    const sessionBanner = document.getElementById('editlock-banner-session');
    const lockBanner = document.getElementById('editlock-banner-lock');
    const duplicateBanner = document.getElementById('editlock-banner-duplicate-tab');

    const HEARTBEAT_MS = 5 * 60 * 1000;
    const HIDDEN_RELEASE_DELAY_MS = 30 * 1000;
    const DEBUG_TICK_MS = 15 * 1000;
    const HELLO_WAIT_MS = 250;

    let heartbeatTimer = null;
    let hiddenReleaseTimer = null;
    let debugTickCount = 0;
    let heartbeatCount = 0;

    let released = false;
    let submitting = false;

    log('[editLock] init', {
        proposalUid: proposalUid,
        token: token,
        heartbeatMs: HEARTBEAT_MS
    });

    const showBanner = function (banner) {
        if (banner) {
            banner.hidden = false;
        }
    };

    const hideBanner = function (banner) {
        if (banner) {
            banner.hidden = true;
        }
    };

    let saveBlocked = false;
    let sessionDisabledButtons = [];

    /*
     * While the session is gone, disable all save/menu submit buttons (the page-menu
     * buttons save on page change too) and block form submits. Only buttons that were
     * enabled get tracked, so an intentionally disabled button (e.g. submit-proposal)
     * is not wrongly enabled on recovery.
     */
    const blockSaving = function () {
        if (saveBlocked) {
            return;
        }
        saveBlocked = true;
        sessionDisabledButtons = [];
        proposalForm.querySelectorAll('button[type="submit"]').forEach(function (button) {
            if (!button.disabled) {
                button.disabled = true;
                sessionDisabledButtons.push(button);
            }
        });
    };

    const unblockSaving = function () {
        if (!saveBlocked) {
            return;
        }
        saveBlocked = false;
        sessionDisabledButtons.forEach(function (button) {
            button.disabled = false;
        });
        sessionDisabledButtons = [];
    };

    const stopHeartbeat = function () {
        if (heartbeatTimer !== null) {
            clearInterval(heartbeatTimer);
            heartbeatTimer = null;
        }
    };

    const heartbeat = function () {
        heartbeatCount += 1;
        const cycle = heartbeatCount;
        const url = '?oap-ajax-task=editlock-renew'
            + '&proposal=' + encodeURIComponent(proposalUid)
            + '&editLockToken=' + encodeURIComponent(token);

        log('[editLock] heartbeat fire', {
            cycle: cycle,
            url: url
        });
        fetch(url, {
            credentials: 'same-origin',
            cache: 'no-store'
        })
            .then(function (response) {
                return response.ok ? response.json() : null;
            })
            .then(function (data) {
                log('[editLock] heartbeat response', {
                    cycle: cycle,
                    data: data
                });
                if (!data) {
                    return;
                }
                if (!data.sessionValid) {
                    /*
                     * Keep polling so the banner disappears automatically once the
                     * user has logged in again (in another tab — the session cookie
                     * is shared, so this tab is authenticated again too).
                     */
                    showBanner(sessionBanner);
                    blockSaving();

                    return;
                }
                // Session recognized again -> remove the expiry banner and re-enable saving.
                hideBanner(sessionBanner);
                unblockSaving();
                if (!data.lockValid) {
                    stopHeartbeat();
                    showBanner(lockBanner);
                }
            })['catch'](function (err) {
                log('[editLock] heartbeat error', {
                    cycle: cycle,
                    error: err
                });

            });
    };

    const debugTick = function () {
        debugTickCount += 1;
        log('[editLock] debug tick', {
            count: debugTickCount,
            heartbeats: heartbeatCount,
            heartbeatActive: heartbeatTimer !== null,
            released: released
        });
    };

    const release = function () {
        if (released || submitting) {
            return;
        }
        released = true;

        const data = new FormData();

        data.append('proposal', proposalUid);
        data.append('editLockToken', token);
        if (navigator.sendBeacon) {
            navigator.sendBeacon('?oap-ajax-task=editlock-release', data);
        }
    };

    const startPrimary = function () {
        log('[editLock] role: primary');

        /*
         * On form submit, pagehide also fires. We must not let the beacon delete the
         * lock then, or the release would overtake the fresh tryAcquire of the
         * following editAction redirect and the lock would vanish from the database.
         * The server (updateAction) manages release/renew within the same request cycle.
         * Fallback reset: if navigation does not occur within 5s (e.g. HTML5 validation
         * keeps the user on the page), the beacon may fire again.
         */
        proposalForm.addEventListener('submit', function () {
            submitting = true;
            setTimeout(function () {
                submitting = false;
            }, 5000);
        });

        proposalForm.addEventListener('submit', function (e) {
            if (saveBlocked) {
                e.preventDefault();
            }
        });

        heartbeatTimer = setInterval(heartbeat, HEARTBEAT_MS);
        if (debug) {
            setInterval(debugTick, DEBUG_TICK_MS);
        }

        window.addEventListener('pagehide', release);

        /*
         * visibilitychange → hidden also fires on brief tab-switches; defer so we only
         * release when the tab actually stays hidden (≈ a real close on iOS Safari).
         */
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') {
                hiddenReleaseTimer = setTimeout(release, HIDDEN_RELEASE_DELAY_MS);
            } else {
                if (hiddenReleaseTimer !== null) {
                    clearTimeout(hiddenReleaseTimer);
                    hiddenReleaseTimer = null;
                }
                if (!released && heartbeatTimer !== null) {
                    heartbeat();
                }
            }
        });
    };

    const becomeDuplicate = function () {
        log('[editLock] role: duplicate (proposal already open in another tab)');

        showBanner(duplicateBanner);

        /*
         * Disable every submit button and block form submits (e.g. Enter inside a
         * text field) via preventDefault. This prevents the duplicate tab from
         * overwriting data the primary tab is editing and from issuing a stray
         * lock release.
         */
        proposalForm.querySelectorAll('button[type="submit"]').forEach(function (button) {
            button.disabled = true;
        });
        proposalForm.addEventListener('submit', function (e) {
            e.preventDefault();
        });
    };

    /*
     * Tab-duplicate detection via BroadcastChannel: otherwise both tabs would share
     * the same token, wipe each other's lock row via sendBeacon and race into a
     * lost-update on save. The first tab instance stays primary; every later one
     * switches into the passive duplicate mode.
     */
    if (typeof BroadcastChannel !== 'function' || typeof crypto === 'undefined' || typeof crypto.randomUUID !== 'function') {
        log('[editLock] BroadcastChannel/crypto.randomUUID not available — starting primary directly');
        startPrimary();

        return;
    }

    const tabId = crypto.randomUUID();
    const channel = new BroadcastChannel('proposal-edit-' + proposalUid);
    let role = 'pending';

    log('[editLock] hello protocol start', {
        tabId: tabId
    });

    channel.addEventListener('message', function (e) {
        if (!e.data) {
            return;
        }
        if (e.data.type === 'hello' && e.data.tabId !== tabId) {
            if (role === 'primary') {
                channel.postMessage({
                    type: 'present',
                    tabId: tabId
                });
            } else if (role === 'pending' && e.data.tabId < tabId) {
                // Race: two tabs opening simultaneously — the smaller tabId wins primary.
                role = 'duplicate';
                becomeDuplicate();
            }
        } else if (e.data.type === 'present' && role === 'pending') {
            role = 'duplicate';
            becomeDuplicate();
        }
    });

    channel.postMessage({
        type: 'hello',
        tabId: tabId
    });

    setTimeout(function () {
        if (role === 'pending') {
            role = 'primary';
            startPrimary();
        }
    }, HELLO_WAIT_MS);

}(this));
