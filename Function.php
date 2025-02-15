<?php
function rup_hbs_webhook_button_shortcode($atts) {
    $allow_no_email = isset($atts['noemail']) && $atts['noemail'] === 'true';

    if (!$allow_no_email && !is_user_logged_in() && !isset($atts['email'])) {
        return '<p>You must be logged in, provide an email, or use noemail="true".</p>';
    }

    $user = wp_get_current_user();
    $email = isset($atts['email']) ? esc_js($atts['email']) : ($allow_no_email ? '' : esc_js($user->user_email));
    $button_text = isset($atts['text']) ? esc_attr($atts['text']) : 'Send Data';
    $after_text = isset($atts['after_text']) ? esc_attr($atts['after_text']) : 'Sent!';
    $webhook_url = isset($atts['webhook']) ? esc_url($atts['webhook']) : '';
    $custom_class = isset($atts['class']) ? esc_attr($atts['class']) : '';
    $debug_enabled = isset($atts['rup-webhook-debug']) && $atts['rup-webhook-debug'] === 'true';
    $capture_browser = isset($atts['capture-browser']) && $atts['capture-browser'] === 'true';
    $capture_url = isset($atts['capture-url']) ? esc_attr($atts['capture-url']) : 'none';
    $method = isset($atts['method']) ? strtoupper($atts['method']) : 'POST';
    $delay = isset($atts['delay']) ? intval($atts['delay']) : 0;
    $redirect_url = isset($atts['redirect']) ? esc_url($atts['redirect']) : '';

    if (empty($webhook_url)) {
        return '<p style="color:red;">Error: Webhook URL is missing in the shortcode.</p>';
    }

    $extra_params = [];
    $headers = [];

    foreach ($atts as $key => $value) {
        if (strpos($key, 'header') === 0) {
            $header_parts = explode(': ', $value, 2);
            if (count($header_parts) === 2) {
                $headers[trim($header_parts[0])] = trim($header_parts[1]);
            }
        } elseif (!in_array($key, ['text', 'after_text', 'webhook', 'email', 'class', 'rup-webhook-debug', 'capture-browser', 'capture-url', 'noemail', 'method', 'delay', 'redirect'])) {
            $extra_params[$key] = esc_attr($value);
        }
    }

    $extra_params_json = htmlspecialchars(json_encode($extra_params), ENT_QUOTES, 'UTF-8');
    $headers_json = htmlspecialchars(json_encode($headers), ENT_QUOTES, 'UTF-8');
    $unique_id = uniqid('rup-hbs-btn_');

    ob_start();
    ?>
    <button id="<?php echo $unique_id; ?>" class="rup-webhook-button <?php echo $custom_class; ?>"
        data-email="<?php echo $email; ?>"
        data-webhook="<?php echo $webhook_url; ?>"
        data-after-text="<?php echo esc_attr($after_text); ?>"
        data-extra-params="<?php echo $extra_params_json; ?>"
        data-debug="<?php echo $debug_enabled ? 'true' : 'false'; ?>"
        data-capture-browser="<?php echo $capture_browser ? 'true' : 'false'; ?>"
        data-capture-url="<?php echo $capture_url; ?>"
        data-method="<?php echo esc_attr($method); ?>"
        data-delay="<?php echo $delay; ?>"
        data-redirect="<?php echo esc_attr($redirect_url); ?>"
        data-headers="<?php echo $headers_json; ?>">
        <?php echo $button_text; ?>
    </button>
    <p id="response_<?php echo $unique_id; ?>" class="rup-hbs-webhook-response" style="display:none; color:red;"></p>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        let button = document.getElementById('<?php echo $unique_id; ?>');
        let responseMsg = document.getElementById('response_<?php echo $unique_id; ?>');
        let debugEnabled = button.getAttribute('data-debug') === 'true';
        let captureBrowser = button.getAttribute('data-capture-browser') === 'true';
        let captureURL = button.getAttribute('data-capture-url');
        let method = button.getAttribute('data-method');
        let delay = parseInt(button.getAttribute('data-delay')) || 0;
        let redirectURL = button.getAttribute('data-redirect');
        let headers = JSON.parse(button.getAttribute('data-headers')) || {};

        if (button) {
            button.addEventListener('click', function () {
                let email = button.getAttribute('data-email');
                let webhookURL = button.getAttribute('data-webhook');
                let afterText = button.getAttribute('data-after-text');
                let extraParams = JSON.parse(button.getAttribute('data-extra-params')) || {};

                if (captureBrowser) {
                    extraParams.userAgent = navigator.userAgent;
                    extraParams.language = navigator.language;
                    extraParams.screenWidth = window.screen.width;
                    extraParams.screenHeight = window.screen.height;
                    extraParams.viewportWidth = window.innerWidth;
                    extraParams.viewportHeight = window.innerHeight;
                    extraParams.platform = navigator.platform;
                }

                if (captureURL === "individual" || captureURL === "both") {
                    let urlParams = new URLSearchParams(window.location.search);
                    urlParams.forEach((value, key) => {
                        extraParams[`url_${key}`] = value;
                    });
                }
                if (captureURL === "full" || captureURL === "both") {
                    extraParams.pageURL = window.location.href;
                }

                setTimeout(() => {
                    if (method === 'GET' || method === 'DELETE') {
                        let queryParams = new URLSearchParams(extraParams).toString();
                        let requestURL = webhookURL + (queryParams ? '?' + queryParams : '');

                        fetch(requestURL, {
                            method: method,
                            headers: headers,
                        })
                        .then(response => response.text())
                        .then(data => {
                            if (debugEnabled) {
                                console.log(`Webhook Response (${method}):`, data);
                            }
                            button.innerText = afterText;
                            if (redirectURL) {
                                window.location.href = redirectURL;
                            }
                        })
                        .catch(error => {
                            console.error(`Webhook Error (${method}):`, error);
                        });

                    } else {
                        let formData = new FormData();
                        formData.append('action', 'rup_hbs_trigger_webhook');
                        formData.append('email', email);
                        formData.append('webhook', webhookURL);
                        formData.append('extra_params', JSON.stringify(extraParams));
                        formData.append('method', method);
                        formData.append('headers', JSON.stringify(headers));
                        formData.append('debug', debugEnabled ? 'true' : 'false');

                        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                            method: 'POST',
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (debugEnabled) {
                                console.log("AJAX Response:", data);
                            }
                            if (data.success) {
                                button.innerText = afterText;
                                responseMsg.style.display = 'none';
                                if (redirectURL) {
                                    window.location.href = redirectURL;
                                }
                            } else {
                                button.innerText = 'Try Again';
                                button.disabled = false;
                                responseMsg.innerText = 'Error: ' + (data.message || 'Unknown error.');
                                responseMsg.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            if (debugEnabled) {
                                console.error("AJAX Error:", error);
                            }
                            button.innerText = 'Try Again';
                            button.disabled = false;
                            responseMsg.innerText = 'Error: Could not send request.';
                            responseMsg.style.display = 'block';
                        });
                    }
                }, delay);
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}

function rup_hbs_trigger_webhook_callback() {
    $post_data = $_POST;

    if (!isset($post_data['email']) || !isset($post_data['webhook'])) {
        wp_send_json_error(['message' => 'Missing parameters!']);
    }

    $email = sanitize_email($post_data['email']);
    $webhook_url = esc_url_raw($post_data['webhook']);
    $extra_params = json_decode(stripslashes($post_data['extra_params']), true) ?: [];
    $method = strtoupper($post_data['method']);
    $headers = json_decode(stripslashes($post_data['headers']), true) ?: [];

    $body_payload = array_merge($extra_params, ['email' => $email]);
    $response = wp_remote_request($webhook_url, [
        'method' => $method,
        'body' => json_encode($body_payload),
        'headers' => array_merge(['Content-Type' => 'application/json'], $headers),
    ]);

    wp_send_json_success(['message' => 'Webhook sent successfully!']);
}

add_shortcode('rup_webhook_button', 'rup_hbs_webhook_button_shortcode');
add_action('wp_ajax_rup_hbs_trigger_webhook', 'rup_hbs_trigger_webhook_callback');
add_action('wp_ajax_nopriv_rup_hbs_trigger_webhook', 'rup_hbs_trigger_webhook_callback');
