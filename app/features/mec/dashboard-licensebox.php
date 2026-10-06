<?php

/** no direct access **/
defined('MECEXEC') or die();

/**
 * IA-01: the license activation box, extracted from dashboard.php so the
 * two Zone B states can share one source of truth - unlicensed installs
 * render it expanded directly below the header, licensed ones demote it
 * into the collapsed "Manage license" <details>.
 *
 * C-04: administrator-only. Non-admins used to get a dead
 * "You cannot access this section." box - the box simply does not render
 * for them now, so the partial returns before any output.
 *
 * Runs in the template scope and expects the state engine variables from
 * dashboard.php: $mec_options, $mec_partner, $mec_partner_name,
 * $product_license, $mec_license_token, $mec_is_offline_licensed,
 * $mec_license_status, $mec_network_licence.
 */
if (!current_user_can('administrator')) return;
?>
<div class="w-box mec-activation mec-intro-card" id="mec-activation-box">
    <h2 class="w-box-head">
        <?php esc_html_e('License Activation', 'modern-events-calendar-lite'); ?>
    </h2>
        <div class="w-box-content">
            <?php
            // Bundled entitlement (X theme and anything like it). The
            // purchase-code form stays available below: a partner licence is
            // time-limited and tied to the theme, so a customer may well want
            // their own licence instead.
            ?>

            <p><?php echo esc_html__('Enter your purchase code to unlock all Pro features.', 'modern-events-calendar-lite'); ?></p>
            <div class="box-mec-activation">
                <?php
                $license_status = '';
                $class_name = 'mec_activate';
                $button_value = esc_html__('Activate', 'modern-events-calendar-lite');

                // mec_license_status only records what the legacy activation
                // endpoint said, which now governs auto-updates alone. Whether
                // Pro actually runs is the signed token ($mec_license_token
                // from the state engine), and a site can hold a perfectly
                // valid purchase code while having no token - the claim was
                // refused, or the server was unreachable. That is precisely
                // the state in which Pro is quietly ramping down, so it must
                // not show green.
                if ($mec_license_token) {
                    // The signed token is valid - Pro is running. This covers
                    // both the online claim and the offline-token path.
                    $license_status = 'PurchaseSuccess';
                    $revoke = true;
                    $class_name = 'mec_revoke';
                    $button_value = esc_html__('Revoke License', 'modern-events-calendar-lite');
                } elseif (!empty($mec_options['purchase_code']) && $mec_license_status == 'active') {
                    $license_status = 'PurchaseError';
                    $revoke = true;
                    $class_name = 'mec_revoke';
                    $button_value = esc_html__('Revoke License', 'modern-events-calendar-lite');
                } elseif (!empty($mec_options['purchase_code']) && $mec_license_status == 'faild') {
                    $license_status = 'PurchaseError';
                    $revoke = false;
                }
                ?>
                <?php if ($mec_is_offline_licensed): ?>
                    <div class="mec-notice mec-notice--success">
                        <div class="mec-notice-accent"></div>
                        <div class="mec-notice-body">
                            <h3 class="mec-notice-title"><?php esc_html_e('Offline activation', 'modern-events-calendar-lite'); ?></h3>
                            <p class="mec-notice-text">
                                <?php
                                if ($mec_token_exp !== null) {
                                    $exp_format = get_option('date_format') ?: 'Y-m-d';
                                    printf(
                                        /* translators: %s: expiry date */
                                        esc_html__('License activated. This offline token is valid until %s. Activate your purchase code before then to keep Pro running.', 'modern-events-calendar-lite'),
                                        '<strong>' . esc_html(date_i18n($exp_format, $mec_token_exp)) . '</strong>'
                                    );
                                } else {
                                    esc_html_e('License activated. Thank you.', 'modern-events-calendar-lite');
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php
                // Partner (bundle) licence: same notice component and the
                // same slot as the offline notice - right after the intro
                // sentence, before the form (owner review, revision 6).
                if (is_array($mec_partner)):
                ?>
                    <div class="mec-notice mec-notice--success">
                        <div class="mec-notice-accent"></div>
                        <div class="mec-notice-body">
                            <h3 class="mec-notice-title"><?php esc_html_e('Bundle license', 'modern-events-calendar-lite'); ?></h3>
                            <p class="mec-notice-text">
                                <?php
                                printf(
                                    /* translators: 1: partner name, 2: expiry date */
                                    esc_html__('This site is licensed through %1$s, so no purchase code is needed. The license stays active while that theme is in use%2$s.', 'modern-events-calendar-lite'),
                                    '<strong>' . esc_html($mec_partner_name) . '</strong>',
                                    $mec_partner['expires'] ? ', and renews automatically before ' . esc_html(date_i18n(get_option('date_format') ?: 'Y-m-d', $mec_partner['expires'])) : ''
                                );
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                        <form id="MECActivation" action="#" method="post">
                            <?php
                            // I-05 + owner revision: masked by default (a purchase
                            // code is a credential - it must not sit in plain sight
                            // during screen shares), with an eye toggle to reveal
                            // and copy it. autocomplete="new-password" is the
                            // standard hint that keeps password managers from
                            // offering to save or silently overwriting the field;
                            // plain autocomplete="off" never stopped them.
                            ?>
                            <div class="LicenseField">
                                <span class="mec-code-field">
                                    <input type="password" id="MECPurchaseCodeInput" autocomplete="new-password" spellcheck="false" placeholder="<?php echo $mec_is_offline_licensed ? esc_attr__('Activated via offline token. Enter your purchase code to switch', 'modern-events-calendar-lite') : esc_attr__('Enter your purchase code', 'modern-events-calendar-lite'); ?>" name="MECPurchaseCode" value="<?php echo esc_html($product_license); ?>">
                                    <button type="button" class="mec-code-toggle" aria-label="<?php echo esc_attr__('Show purchase code', 'modern-events-calendar-lite'); ?>" aria-pressed="false" title="<?php echo esc_attr__('Show purchase code', 'modern-events-calendar-lite'); ?>"><i class="mec-sl-eye" aria-hidden="true"></i></button>
                                </span>
                                <input type="submit" class="<?php echo esc_html($class_name); ?>" value="<?php echo esc_html($button_value); ?>">
                                <?php
                                // A-03: the green/red cross changes silently for screen
                                // readers unless the container is a live region.
                                ?>
                                <div class="MECPurchaseStatus <?php echo esc_html($license_status); ?>" role="status" aria-live="polite"></div>
                            </div>
                            <p class="mec-license-help"><a href="https://webnus.net/dox/modern-events-calendar/auto-update/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Where do I find my purchase code?', 'modern-events-calendar-lite'); ?></a></p>
                    <div class="MECLicenseMessage mec-message-hidden" role="alert">
                        <?php
                        // The last activation attempt never reached the license
                        // server (blocked or unreachable endpoint). The default
                        // message below would tell the customer to check a
                        // purchase code that was never actually checked.
                        if (get_transient('mec_activation_network_error'))
                        {
                            echo esc_html__('The license server could not be reached, so your purchase code could not be checked. Please try again in a few minutes. If it keeps failing, contact support.', 'modern-events-calendar-lite');
                        }
                        else
                        {
                            echo esc_html__('Activation failed. Check your purchase code - it must match your license type.', 'modern-events-calendar-lite') . '<a style="text-decoration: underline; padding-left: 7px;" href="https://webnus.net/dox/modern-events-calendar/auto-update/" target="_blank" rel="noopener noreferrer">'  . esc_html__('Troubleshooting', 'modern-events-calendar-lite') . '</a>';
                        }
                        ?>
                    </div>
                </form>
                <script>
                    (function () {
                        // I-05 toggle: reveal/hide the purchase code. The button
                        // carries its state on aria-pressed, so screen readers
                        // announce it and the :focus-visible ring stays visible.
                        var input = document.getElementById('MECPurchaseCodeInput');
                        if (!input) return;

                        var btn = input.parentElement && input.parentElement.querySelector('.mec-code-toggle');
                        if (!btn) return;

                        var labelShow = <?php echo wp_json_encode(esc_html__('Show purchase code', 'modern-events-calendar-lite')); ?>;
                        var labelHide = <?php echo wp_json_encode(esc_html__('Hide purchase code', 'modern-events-calendar-lite')); ?>;

                        btn.addEventListener('click', function () {
                            var show = input.type === 'password';
                            input.type = show ? 'text' : 'password';
                            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                            btn.setAttribute('aria-label', show ? labelHide : labelShow);
                            btn.title = show ? labelHide : labelShow;
                        });
                    })();
                </script>
            </div>

            <?php
            // Offline activation. For the customer who bought through a
            // reseller, lost their purchase code, or runs on a host with no
            // outbound HTTPS. Support mints a token for their domain and they
            // paste it here.
            //
            // Hidden once the site is licensed: an activated site (online or
            // offline) has no use for another token, and showing the box next
            // to a green "activated" state is confusing.
            if (!$mec_license_token):
            ?>
            <div class="box-mec-offline-activation">
                <p><?php esc_html_e('If this server has no outbound internet access, or you bought MEC through a reseller, ask support for an offline activation token and paste it below.', 'modern-events-calendar-lite'); ?></p>
                <?php
                // The exact host the token is bound to. Shown because the
                // customer's idea of their domain and home_url() disagree
                // often enough - a subdomain, a path, a stray www - and a
                // token minted for the wrong one is refused, costing a
                // second support round trip on a site that is already
                // ramping. Quoting it here makes the ticket unambiguous.
                ?>
                <p class="mec-offline-site-id">
                    <?php printf(
                                    esc_html__('Include this site address when you contact support: %s', 'modern-events-calendar-lite'),
                        '<code>' . esc_html(MEC_license::instance()->host()) . '</code>'
                    ); ?>
                    <button type="button" class="mec-offline-copy-host" data-host="<?php echo esc_attr(MEC_license::instance()->host()); ?>"><?php esc_html_e('Copy', 'modern-events-calendar-lite'); ?></button>
                    <button type="button" class="mec-offline-support-btn" data-support-url="https://webnus.net/support"><?php esc_html_e('Contact support', 'modern-events-calendar-lite'); ?></button>
                </p>
                <form id="MECOfflineActivation" action="#" method="post">
                    <div class="LicenseField">
                        <input type="text" name="MECOfflineToken" autocomplete="off" spellcheck="false" placeholder="<?php echo esc_attr__('Paste your offline activation token here', 'modern-events-calendar-lite'); ?>">
                        <input type="submit" value="<?php echo esc_attr__('Activate offline', 'modern-events-calendar-lite'); ?>">
                    </div>
                    <div class="MECOfflineMessage" role="status" aria-live="polite"></div>
                </form>
            </div>

            <script>
                    (function () {
                        var copyBtn = document.querySelector('.mec-offline-copy-host');

                        // One-click copy of the host so the support ticket is unambiguous.
                        // The state class is "mec-copy-done", NOT "mec-copied": a legacy
                        // ".mec-copied" rule in backend.css hides any element with it.
                        if (copyBtn) {
                            var copiedTimer = null;
                            var label = copyBtn.textContent;

                            copyBtn.addEventListener('click', function () {
                                var host = copyBtn.getAttribute('data-host') || '';

                                var done = function () {
                                    copyBtn.textContent = <?php echo wp_json_encode(esc_html__('Copied!', 'modern-events-calendar-lite')); ?>;
                                    copyBtn.classList.add('mec-copy-done');

                                    window.clearTimeout(copiedTimer);
                                    copiedTimer = window.setTimeout(function () {
                                        copyBtn.textContent = label;
                                        copyBtn.classList.remove('mec-copy-done');
                                    }, 2000);
                                };

                                if (navigator.clipboard && navigator.clipboard.writeText) {
                                    navigator.clipboard.writeText(host).then(done, done);
                                } else {
                                    var ta = document.createElement('textarea');
                                    ta.value = host;
                                    document.body.appendChild(ta);
                                    ta.select();
                                    try { document.execCommand('copy'); } catch (e) {}
                                    document.body.removeChild(ta);
                                    done();
                                }
                            });
                        }

                        var supportBtn = document.querySelector('.mec-offline-support-btn');
                        if (supportBtn) {
                            supportBtn.addEventListener('click', function () {
                                window.open(supportBtn.getAttribute('data-support-url'), '_blank', 'noopener,noreferrer');
                            });
                        }

                        var form = document.getElementById('MECOfflineActivation');
                        if (!form) return;

                        var out = form.querySelector('.MECOfflineMessage');
                        var submit = form.querySelector('input[type=submit]');

                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            var body = new FormData();
                            body.append('action', 'mec_lic_token');
                            body.append('nonce', mec_admin_localize.ajax_nonce);
                            body.append('token', form.querySelector('input[name=MECOfflineToken]').value);

                            submit.disabled = true;
                            out.textContent = '';

                            window.fetch(mec_admin_localize.ajax_url, {
                                method: 'POST',
                                credentials: 'same-origin',
                                body: body
                            }).then(function (response) {
                                return response.json();
                            }).then(function (result) {
                                var success = !!(result && result.success);
                                var message = (result && result.data && result.data.message) ? result.data.message : '';

                                if (success) {
                                    // Reload so the server-rendered success notice
                                    // replaces the box.
                                    window.location.reload();
                                    return;
                                }

                                out.textContent = message;
                                out.className = 'MECOfflineMessage mec-offline-error';
                            })['catch'](function () {
                                out.textContent = <?php echo wp_json_encode(esc_html__('Something went wrong. Please reload the page and try again.', 'modern-events-calendar-lite')); ?>;
                                out.className = 'MECOfflineMessage mec-offline-error';
                            }).then(function () {
                                submit.disabled = false;
                            });
                        });
                    })();
            </script>
                    <?php endif; // !$mec_license_token ?>

            <?php include plugin_dir_path(__FILE__) . 'dashboard-addonstoggle.php'; ?>
        </div>
</div>
