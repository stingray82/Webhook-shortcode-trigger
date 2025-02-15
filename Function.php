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

    if (empty($webhook_url)) {
        return '<p style="color:red;">Error: Webhook URL is missing in the shortcode.</p>';
    }

    $extra_params = [];
    foreach ($atts as $key => $value) {
        if (!in_array($key, ['text', 'after_text', 'webhook', 'email', 'class', 'rup-webhook-debug', 'capture-browser', 'capture-url', 'noemail'])) {
            $extra_params[$key] = esc_attr($value);
        }
    }

    $extra_params_json = htmlspecialchars(json_encode($extra_params), ENT_QUOTES, 'UTF-8');
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
        data-capture-url="<?php echo $capture_url; ?>">
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

        if (button) {
            button.addEventListener('click', function () {
                let email = button.getAttribute('data-email');
                let webhookURL = button.getAttribute('data-webhook');
                let afterText = button.getAttribute('data-after-text');
                let extraParams = button.getAttribute('data-extra-params');

                if (!extraParams) {
                    extraParams = '{}';
                }

                extraParams = JSON.parse(extraParams);

                // Capture browser parameters if enabled
                if (captureBrowser) {
                    extraParams.userAgent = navigator.userAgent;
                    extraParams.language = navigator.language;
                    extraParams.screenWidth = window.screen.width;
                    extraParams.screenHeight = window.screen.height;
                    extraParams.viewportWidth = window.innerWidth;
                    extraParams.viewportHeight = window.innerHeight;
                    extraParams.platform = navigator.platform;
                }

                // Capture URL parameters if enabled
                if (captureURL === "individual" || captureURL === "both") {
                    let urlParams = new URLSearchParams(window.location.search);
                    urlParams.forEach((value, key) => {
                        extraParams[`url_${key}`] = value;
                    });
                }
                if (captureURL === "full" || captureURL === "both") {
                    extraParams.pageURL = window.location.href;
                }

                if (debugEnabled) {
                    console.log("Extra Params:", extraParams);
                }

                let ajaxURL = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';

                if (!webhookURL) {
                    responseMsg.innerText = 'Error: Webhook URL not set.';
                    responseMsg.style.display = 'block';
                    return;
                }

                button.innerText = 'Sending...';
                button.disabled = true;

                let formData = new FormData();
                formData.append('action', 'rup_hbs_trigger_webhook');
                formData.append('email', email);
                formData.append('webhook', webhookURL);
                formData.append('extra_params', JSON.stringify(extraParams));
                formData.append('debug', debugEnabled ? 'true' : 'false');

                fetch(ajaxURL, {
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
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}

function rup_hbs_trigger_webhook_callback() {
    $post_data = $_POST;
    $debug_enabled = isset($post_data['debug']) && $post_data['debug'] === 'true';

    if ($debug_enabled) {
        error_log("Received POST Data: " . print_r($post_data, true));
    }

    if (!isset($post_data['email']) || !isset($post_data['webhook'])) {
        wp_send_json_error([
            'message' => 'Missing parameters!',
            'debug_received_data' => $debug_enabled ? json_encode($post_data) : null
        ]);
    }

    $email = sanitize_email($post_data['email']);
    $webhook_url = esc_url_raw($post_data['webhook']);
    $extra_params = isset($post_data['extra_params']) ? stripslashes($post_data['extra_params']) : '{}';
    $extra_params = json_decode($extra_params, true);

    if (!is_array($extra_params)) {
        $extra_params = [];
    }

    if ($debug_enabled) {
        error_log("Parsed Extra Params: " . print_r($extra_params, true));
    }

    if (empty($webhook_url)) {
        wp_send_json_error(['message' => 'Webhook URL is empty.']);
    }

    $body_payload = $extra_params;
    if (!empty($email)) {
        $body_payload['email'] = $email;
    }
    $body_data = json_encode($body_payload);

    if ($debug_enabled) {
        error_log("Webhook Data Sent: " . $body_data);
    }

    $response = wp_remote_post($webhook_url, [
        'body' => $body_data,
        'headers' => ['Content-Type' => 'application/json'],
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error([
            'message' => 'Webhook request failed.',
            'error_details' => $debug_enabled ? $response->get_error_message() : null
        ]);
    }

    $response_body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);

    if ($response_code !== 200) {
        wp_send_json_error(['message' => 'Webhook responded with error.', 'http_status' => $response_code]);
    }

    wp_send_json_success(['message' => 'Webhook sent successfully!', 'response_code' => $response_code]);
}

add_shortcode('rup_webhook_button', 'rup_hbs_webhook_button_shortcode');
add_action('wp_ajax_rup_hbs_trigger_webhook', 'rup_hbs_trigger_webhook_callback');
add_action('wp_ajax_nopriv_rup_hbs_trigger_webhook', 'rup_hbs_trigger_webhook_callback');