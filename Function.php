<?php
// ======================================
// Register Webhook Settings Page
// ======================================
function rup_hbs_register_settings_page() {
    add_options_page(
        'Webhook Settings',   // Page title
        'Webhook Settings',   // Menu title under "Settings"
        'manage_options',     // Capability required
        'rup_webhook_settings', // Menu slug
        'rup_hbs_settings_page' // Callback function that renders the page
    );
}
add_action('admin_menu', 'rup_hbs_register_settings_page');


function rup_hbs_register_settings() {
    register_setting('rup_webhook_settings_group', 'rup_webhook_stored_settings');
}
add_action('admin_init', 'rup_hbs_register_settings');



// ======================================
// Render Admin Settings Page
// ======================================
function rup_hbs_settings_page() {
    $stored_settings = get_option('rup_webhook_stored_settings', []);

    ?>
    <div class="wrap">
        <h1>Webhook Global Settings</h1>

        <form method="post" action="options.php">
            <?php settings_fields('rup_webhook_settings_group'); ?>
            <?php do_settings_sections('rup_webhook_settings_group'); ?>

            <h2>Stored Webhook Parameters</h2>
            <table class="widefat fixed" id="webhook-settings-table">
                <thead>
                    <tr>
                        <th>Key Name</th>
                        <th>Value</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stored_settings)) : ?>
                        <?php foreach ($stored_settings as $key => $pair) : ?>
                            <tr>
                                <td><input type="text" name="rup_webhook_stored_settings[<?php echo esc_attr($key); ?>][key]" value="<?php echo esc_attr($pair['key']); ?>" /></td>
                                <td><input type="text" name="rup_webhook_stored_settings[<?php echo esc_attr($key); ?>][value]" value="<?php echo esc_attr($pair['value']); ?>" /></td>
                                <td><button type="button" class="button remove-row">Remove</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <button type="button" class="button button-primary" id="add-row">+ Add New</button>
            <hr>

            <h2>Import/Export Settings</h2>
            <textarea id="export-settings" rows="5" cols="100"><?php echo esc_textarea(json_encode($stored_settings, JSON_PRETTY_PRINT)); ?></textarea>
            <p><button type="button" class="button button-secondary" id="copy-export">Copy JSON</button></p>
            <p><input type="file" id="import-file" accept=".json" /><button type="button" class="button button-primary" id="import-settings">Import JSON</button></p>

            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
    console.log("Webhook settings script loaded!"); // Debugging

    // Add New Row
    document.getElementById("add-row").addEventListener("click", function() {
        addRow("", "");
    });

    // Remove Row
    document.addEventListener("click", function(event) {
        if (event.target.classList.contains("remove-row")) {
            event.target.closest("tr").remove();
            console.log("Row removed."); // Debugging
        }
    });

    // Copy JSON
    document.getElementById("copy-export").addEventListener("click", function() {
        let exportTextarea = document.getElementById("export-settings");
        exportTextarea.select();
        document.execCommand("copy");
        console.log("JSON copied to clipboard."); // Debugging
    });

    // Import JSON
    document.getElementById("import-settings").addEventListener("click", function() {
        let fileInput = document.getElementById("import-file");
        if (fileInput.files.length > 0) {
            let file = fileInput.files[0];
            let reader = new FileReader();
            reader.onload = function(event) {
                try {
                    let jsonData = JSON.parse(event.target.result);
                    document.getElementById("export-settings").value = JSON.stringify(jsonData, null, 4);
                    console.log("Imported JSON:", jsonData); // Debugging
                    
                    // Clear the existing table rows
                    let tbody = document.querySelector("#webhook-settings-table tbody");
                    tbody.innerHTML = "";
                    
                    // Populate table with imported data
                    Object.keys(jsonData).forEach(key => {
                        addRow(jsonData[key].key, jsonData[key].value);
                    });
                } catch (error) {
                    alert("Invalid JSON file!");
                    console.error("JSON Parse Error:", error);
                }
            };
            reader.readAsText(file);
        }
    });

    // Function to add a new row to the table
    function addRow(key, value) {
        let tbody = document.querySelector("#webhook-settings-table tbody");
        let uniqueKey = key || 'key_' + Math.random().toString(36).substr(2, 9); // Generate a unique key if empty
        
        let newRow = document.createElement("tr");
        newRow.innerHTML = `
            <td><input type="text" name="rup_webhook_stored_settings[${uniqueKey}][key]" value="${key}" placeholder="Enter Key Name" /></td>
            <td><input type="text" name="rup_webhook_stored_settings[${uniqueKey}][value]" value="${value}" placeholder="Enter Value" /></td>
            <td><button type="button" class="button remove-row">Remove</button></td>
        `;
        tbody.appendChild(newRow);
    }
});


    </script>

    <?php
}




// ======================================
// Resolve Stored Values isstored:
// ======================================
function resolve_stored_value($value, $stored_settings, $debug_enabled) {
    if ($debug_enabled) {
        error_log("Resolve_stored_value() CALLED for `$value`");
        error_log("Flattened Stored Settings: " . print_r($stored_settings, true));
    }

    //Only process values that actually contain 'isstored:'
    if (is_string($value) && strpos($value, 'isstored:') === 0) {
        $stored_key = trim(str_replace('isstored:', '', $value));

        if ($debug_enabled) {
            error_log("Looking for stored key: `$stored_key`");
        }

        // Search for stored key
        foreach ($stored_settings as $key => $setting) {
            if (isset($setting['key']) && $setting['key'] === $stored_key) {
                if ($debug_enabled) {
                    error_log("Matched stored key `$stored_key`, resolved value → `" . $setting['value'] . "`");
                }

                // Ensure we return the stored value, NOT the key itself
                if (!empty($setting['value'])) {
                    if ($debug_enabled) {
                        error_log("Returning resolved value: `" . $setting['value'] . "`");
                    }
                    return $setting['value'];
                } else {
                    if ($debug_enabled) {
                        error_log("WARNING: Matched key `$stored_key`, but value is empty!");
                    }
                    return ''; // Avoid returning the key itself
                }
            }
        }

        //No match found for `isstored:` key
        if ($debug_enabled) {
            error_log("No match found for `$stored_key`. Returning empty string.");
        }
        return '';
    }

    // If value does NOT start with `isstored:`, return it unchanged
    if ($debug_enabled) {
        error_log("Returning original (non-stored) value: `$value`");
    }
    return $value;
}



// ======================================
// Webhook Buttons Shortcode
// ======================================

function rup_hbs_webhook_button_shortcode($atts) {
    $allow_no_email = isset($atts['noemail']) && $atts['noemail'] === 'true';

    // Get the logged-in user's email if available
    $user_email = is_user_logged_in() ? wp_get_current_user()->user_email : '';

    // Handle email logic:
    if (!isset($atts['email'])) {
        $atts['email'] = $user_email; // Default to logged-in user's email
    } elseif ($atts['email'] === '') {
        // Explicitly set blank email, allow it
    } elseif ($atts['email'] === 'isstored:emailhidden') {
        // Keep isstored:emailhidden for processing
    } else {
        // Use the provided email (e.g., hello@email.com)
    }

    // If email is required but missing, return an error message
    if (!$allow_no_email && empty($atts['email'])) {
        return '<p>You must be logged in, provide an email, or use noemail="true".</p>';
    }

    // Define protected fields
    $protected_fields = ['webhook', 'email', 'testing']; 

    // Ensure protected values remain placeholders (`isstored:`)
    foreach ($protected_fields as $field) {
        if (isset($atts[$field]) && strpos($atts[$field], 'isstored:') !== 0) {
            $atts[$field] = 'isstored:' . $atts[$field];
        }
    }

    // Safe UI attributes
    $button_text = esc_attr($atts['text'] ?? 'Send Data');
    $after_text = esc_attr($atts['after_text'] ?? 'Sent!');
    $custom_class = esc_attr($atts['class'] ?? '');
    $debug_enabled = isset($atts['rup-webhook-debug']) && $atts['rup-webhook-debug'] === 'true';
    $capture_browser = isset($atts['capture-browser']) && $atts['capture-browser'] === 'true';
    $capture_url = esc_attr($atts['capture-url'] ?? 'none');
    $method = strtoupper($atts['method'] ?? 'POST');
    $delay = intval($atts['delay'] ?? 0);
    $redirect_url = esc_url($atts['redirect'] ?? '');

    // Generate unique ID
    $unique_id = uniqid('rup-hbs-btn_');

    // Store extra parameters and headers
    $extra_params = [];
    $headers = [];

    foreach ($atts as $key => $value) {
    if (strpos($key, 'header') === 0) {
        $header_parts = explode(': ', $value, 2);
        if (count($header_parts) === 2) {
            $key_name = trim($header_parts[0]);
            $header_value = trim($header_parts[1]);

            // Keep hardcoded headers unchanged and only resolve stored values
            if (strpos($header_value, 'isstored:') === 0) {
                $headers[$key_name] = $header_value; // Use stored reference as-is
            } else {
                $headers[$key_name] = $header_value; // Keep hardcoded headers unchanged
            }
        }
    } elseif (!in_array($key, ['text', 'after_text', 'webhook', 'email', 'class', 'rup-webhook-debug', 'capture-browser', 'capture-url', 'noemail', 'method', 'delay', 'redirect'])) {
        $extra_params[$key] = (in_array($key, $protected_fields) && strpos($value, 'isstored:') !== 0) ? 'isstored:' . $value : $value;
    }
}


    // Encode JSON safely for use in data attributes
    $extra_params_json = htmlspecialchars(json_encode($extra_params), ENT_QUOTES, 'UTF-8');
    $headers_json = htmlspecialchars(json_encode($headers), ENT_QUOTES, 'UTF-8');

    ob_start();
    ?>
    <button id="<?php echo esc_attr($unique_id); ?>" class="rup-webhook-button <?php echo esc_attr($custom_class); ?>"
        data-email="<?php echo esc_attr($atts['email']); ?>"
        data-webhook="<?php echo esc_attr($atts['webhook'] ?? ''); ?>"
        data-after-text="<?php echo esc_attr($after_text); ?>"
        data-extra-params="<?php echo esc_attr($extra_params_json); ?>"
        data-debug="<?php echo $debug_enabled ? 'true' : 'false'; ?>"
        data-capture-browser="<?php echo $capture_browser ? 'true' : 'false'; ?>"
        data-capture-url="<?php echo esc_attr($capture_url); ?>"
        data-method="<?php echo esc_attr($method); ?>"
        data-delay="<?php echo esc_attr($delay); ?>"
        data-redirect="<?php echo esc_attr($redirect_url); ?>"
        data-headers="<?php echo esc_attr($headers_json); ?>">
        <?php echo esc_html($button_text); ?>
    </button>
    <p id="response_<?php echo esc_attr($unique_id); ?>" class="rup-hbs-webhook-response" style="display:none; color:red;"></p>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        let button = document.getElementById('<?php echo esc_js($unique_id); ?>');
        if (!button) return;

        let responseMsg = document.getElementById('response_<?php echo esc_js($unique_id); ?>');

        button.addEventListener('click', function () {
            let email = button.getAttribute('data-email');
            let webhookURL = button.getAttribute('data-webhook');
            let afterText = button.getAttribute('data-after-text');
            let extraParamsRaw = button.getAttribute('data-extra-params');
            let headersRaw = button.getAttribute('data-headers');
            let debugEnabled = button.getAttribute('data-debug') === 'true';
            let method = button.getAttribute('data-method');
            let delay = parseInt(button.getAttribute('data-delay')) || 0;
            let redirectURL = button.getAttribute('data-redirect');
            let captureBrowser = button.getAttribute('data-capture-browser') === 'true';
            let captureURL = button.getAttribute('data-capture-url');

            let extraParams = {};
            let headers = {};

            try {
                extraParams = JSON.parse(extraParamsRaw);
            } catch (e) {
                console.error("Error parsing extra params JSON:", e);
            }

            try {
                headers = JSON.parse(headersRaw);
            } catch (e) {
                console.error("Error parsing headers JSON:", e);
            }

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
                    extraParams["url_" + key] = value;
                });
            }
            if (captureURL === "full" || captureURL === "both") {
                extraParams.pageURL = window.location.href;
            }

            setTimeout(() => {
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
                    if (data.success) {
                        button.innerText = afterText;
                        responseMsg.style.display = 'none';
                        if (redirectURL) window.location.href = redirectURL;
                    } else {
                        responseMsg.innerText = 'Error: ' + (data.message || 'Unknown error.');
                        responseMsg.style.display = 'block';
                    }
                })
                .catch(error => {
                    responseMsg.innerText = 'Error: Could not send request.';
                    responseMsg.style.display = 'block';
                });
            }, delay);
        });
    });
    </script>
    <?php
    return ob_get_clean();
}


// ======================================
// AJAX - Secure Webhook Trigger
// ======================================

function rup_hbs_trigger_webhook_callback() {
    $post_data = $_POST;
    $debug_enabled = !empty($post_data['debug']) && $post_data['debug'] === 'true'; 
    if (!isset($post_data['email']) || !isset($post_data['webhook'])) {
        if ($debug_enabled) {
            error_log("Missing parameters! Received data: " . print_r($post_data, true));
        }   
        wp_send_json_error(['message' => 'Missing parameters!']);
    }

    $stored_settings = get_option('rup_webhook_stored_settings', []);
    $debug_enabled = !empty($post_data['debug']) && $post_data['debug'] === 'true';

    if ($debug_enabled) {
        error_log("Debug Mode Enabled");
        error_log("Incoming POST Data: " . print_r($post_data, true));
    }

    // Resolve webhook URL
    $webhook_url = resolve_stored_value($post_data['webhook'], $stored_settings, $debug_enabled);
    
    // Resolve email correctly this workflow requires a lot of effort due to the options
    $email = $post_data['email'] ?? ''; // Get raw email from the form

    //Step 1: Get the raw email from the request
    $email = $post_data['email'] ?? ''; 

    if ($debug_enabled) {
        error_log("Initial Email Value: $email");
    }

    //Step 2: Check if it's a manually entered email (not stored)
    if (!empty($email) && strpos($email, 'isstored:') === false) {
        if ($debug_enabled) {
            error_log("Hardcoded Email Detected, LOCKED IN: $email");
        }
        // KEEP THIS EMAIL AS-IS!
    } 
    // Step 3: If email is stored (isstored:XYZ), resolve it properly
    elseif (strpos($email, 'isstored:') === 0) {
        $stored_email_key = str_replace('isstored:', '', $email);

        if ($debug_enabled) {
            error_log("Looking for stored key: `$stored_email_key`");
        }

        // Fetch stored value
        $resolved_email = resolve_stored_value($email, $stored_settings, $debug_enabled);

        //Log the exact returned structure
        if ($debug_enabled) {
            error_log("Raw output from resolve_stored_value(): " . print_r($resolved_email, true));
        }

        // Validate that we are NOT assigning the key itself
        if (!empty($resolved_email) && $resolved_email !== $stored_email_key && filter_var($resolved_email, FILTER_VALIDATE_EMAIL)) {
            $email = $resolved_email;
            if ($debug_enabled) {
                error_log("Successfully assigned resolved email: `$email`");
            }
        } else {
            if ($debug_enabled) {
                error_log("Resolved value `$resolved_email` is NOT a valid email. Keeping email unset.");
            }
        }
    }

    // Step 4: Ensure fallback logic happens ONLY if the email is still empty
    if (empty($email) && is_user_logged_in()) {
        $email = wp_get_current_user()->user_email;
        if ($debug_enabled) {
            error_log("No email provided, falling back to logged-in user: $email");
        }
    }

    // Step 5: If STILL empty, ensure it's not null
    if ($email === null || $email === '' || strpos($email, 'isstored:') === 0) {
        if ($debug_enabled) {
            error_log("Final Resolved Email is invalid or prefixed. Stripping `isstored:`.");
        }
        $email = preg_replace('/^isstored:/', '', $email);
    }

    //Final Debugging Log
    if ($debug_enabled) {
        error_log("FINAL Email Before Sending Webhook: " . ($email ?: 'MISSING'));
    }

    if (empty($webhook_url) || strpos($webhook_url, 'isstored:') === 0) {
        wp_send_json_error(['message' => 'Invalid Webhook URL!']);
    }

    // Process extra parameters
    $extra_params = $post_data['extra_params'] ?? [];
    if (is_string($extra_params)) {
        $extra_params = json_decode(stripslashes($extra_params), true) ?: [];
    }
    foreach ($extra_params as $key => $value) {
        $extra_params[$key] = resolve_stored_value($value, $stored_settings, $debug_enabled);
    }

    // Securely resolve headers
    $headers = $post_data['headers'] ?? [];
    if (is_string($headers)) {
        $headers = json_decode(stripslashes($headers), true) ?: [];
    }
    $new_headers = [];

    foreach ($headers as $key => $value) {
        $original_key = $key;
        $original_value = $value;

        // Only resolve if the value starts with 'isstored:'
        if (strpos($value, 'isstored:') === 0) {
            $resolved_value = resolve_stored_value($value, $stored_settings, $debug_enabled);
        } else {
            $resolved_value = $value; // Keep the original value unchanged
        }

        //Authorization header formatting if needed
        if (stripos($key, 'Authorization') !== false) {
            if ($debug_enabled) {
                error_log("Original Authorization Value: " . $original_value);
                error_log("Resolved Authorization Value: " . $resolved_value);
            }

            if (strpos($resolved_value, 'Bearer ') !== 0) {
                $resolved_value = 'Bearer ' . $resolved_value;
            }
        }

        // Debugging to confirm headers are set
        if ($debug_enabled) {
            error_log("Final Header Set: $key => $resolved_value");
        }

        $new_headers[$key] = $resolved_value;
    }

    $headers = $new_headers;

    // Debug log headers before sending
    if ($debug_enabled) {
        error_log("Final Headers Before Sending: " . print_r($headers, true));
    }

    // Prepare webhook payload
    $body_payload = array_merge($extra_params, ['email' => $email]);

    // Send webhook request securely
    $response = wp_remote_request($webhook_url, [
        'method'    => strtoupper($post_data['method']),
        'body'      => json_encode($body_payload),
        'headers'   => array_merge(['Content-Type' => 'application/json'], $headers),
    ]);

    // Debug response
    if ($debug_enabled) {
        error_log("Webhook Response: " . print_r($response, true));
    }

    // Handle errors
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Webhook request failed.']);
    }

    wp_send_json_success(['message' => 'Webhook sent successfully!']);
}



add_action('wp_ajax_rup_hbs_trigger_webhook', 'rup_hbs_trigger_webhook_callback');
add_action('wp_ajax_nopriv_rup_hbs_trigger_webhook', 'rup_hbs_trigger_webhook_callback');
add_shortcode('rup_webhook_button', 'rup_hbs_webhook_button_shortcode');



// ======================================
// Auto Page Load Trugger - Shortcode
// ======================================

function rup_hbs_auto_webhook_shortcode($atts) {
    $debug_enabled = isset($atts['rup-webhook-debug']) && $atts['rup-webhook-debug'] === 'true';

    // Load stored settings
    $stored_settings = get_option('rup_webhook_stored_settings', []);

    // Resolve the webhook URL
    $webhook_url = resolve_stored_value($atts['webhook'] ?? '', $stored_settings, $debug_enabled);

    if ($debug_enabled) {
        error_log("Auto Webhook Shortcode - Raw Webhook Value: " . print_r($atts['webhook'], true));
        error_log("Auto Webhook Shortcode - Resolved Webhook URL: " . print_r($webhook_url, true));
    }

    if (empty($webhook_url)) {
        return '<p style="color: red;">Webhook URL is invalid or missing!</p>';
    }

    // Process extra parameters and headers correctly
    $extra_params = [];
    $headers = [];

    foreach ($atts as $key => $value) {
        if (strpos($key, 'header') === 0) {
            // Extract headers properly
            $header_value = resolve_stored_value($value, $stored_settings, $debug_enabled);
            $header_parts = explode(':', $header_value, 2);
            if (count($header_parts) === 2) {
                $headers[trim($header_parts[0])] = trim($header_parts[1]);
            } else {
                error_log("Invalid header format for $key: $value");
            }
        } elseif (!in_array($key, ['webhook', 'email', 'class', 'rup-webhook-debug', 'capture-browser', 'capture-url', 'method', 'delay', 'redirect', 'noemail'])) {
            $extra_params[$key] = resolve_stored_value($value, $stored_settings, $debug_enabled);
        }
    }

    //Ensure email is properly passed to match webhook logic
    $no_email = isset($atts['noemail']) && strtolower($atts['noemail']) === 'true';
    
    if ($no_email) {
        $email = 'isstored:'; // **Use `isstored:` to properly unset the email**
    } else {
        $email = resolve_stored_value($atts['email'] ?? '', $stored_settings, $debug_enabled);
    }

    ob_start();
    ?>
    <script> 
    document.addEventListener('DOMContentLoaded', function () {
        let debugEnabled = <?php echo json_encode($debug_enabled); ?>;
        let extraParams = <?php echo json_encode($extra_params, JSON_UNESCAPED_SLASHES); ?>;
        let headers = <?php echo json_encode($headers, JSON_UNESCAPED_SLASHES); ?>;
        let captureBrowser = <?php echo json_encode(isset($atts['capture-browser']) && $atts['capture-browser'] === 'true'); ?>;
        let captureURL = <?php echo json_encode($atts['capture-url'] ?? 'none'); ?>;
        let method = <?php echo json_encode(strtoupper($atts['method'] ?? 'POST')); ?>;
        let delay = <?php echo json_encode(isset($atts['delay']) ? intval($atts['delay']) : 0); ?>;
        let redirectURL = <?php echo json_encode($atts['redirect'] ?? ''); ?>;
    
        // Ensure noEmail is checked correctly
        let noEmail = <?php echo json_encode(strtolower($atts['noemail'] ?? 'false')); ?>;
        let email = (noEmail === 'true') ? 'isstored:' : <?php echo json_encode($email, JSON_UNESCAPED_SLASHES); ?>;
        
        let webhookURL = <?php echo json_encode($webhook_url, JSON_UNESCAPED_SLASHES); ?>;

        // Ensure delay is always a valid number
        delay = (typeof delay !== 'undefined' && !isNaN(delay)) ? parseInt(delay) : 0;

        setTimeout(function() {
            // Capture browser details
            if (captureBrowser) {
                extraParams['browser_userAgent'] = navigator.userAgent || '';
                extraParams['browser_language'] = navigator.language || '';
                extraParams['browser_screenWidth'] = window.screen.width || '';
                extraParams['browser_screenHeight'] = window.screen.height || '';
                extraParams['browser_viewportWidth'] = window.innerWidth || '';
                extraParams['browser_viewportHeight'] = window.innerHeight || '';
                extraParams['browser_platform'] = navigator.platform || '';
            }

            // Capture URL parameters correctly
            if (captureURL === "individual" || captureURL === "both") {
                let urlParams = new URLSearchParams(window.location.search);
                urlParams.forEach((value, key) => {
                    extraParams[`url_param_${key}`] = value || '';
                });
            }
            if (captureURL === "full" || captureURL === "both") {
                extraParams['full_pageURL'] = window.location.href || '';
            }

            let formData = new FormData();
            formData.append('action', 'rup_hbs_trigger_webhook');
            formData.append('webhook', webhookURL);
            formData.append('email', email); //Now sends `isstored:` to properly unset email
            formData.append('method', method);
            formData.append('debug', debugEnabled ? 'true' : 'false');

            Object.keys(extraParams).forEach(key => {
                formData.append(`extra_params[${key}]`, extraParams[key]);
            });

            // Convert headers to a JSON string before sending
            let headersJson = JSON.stringify(headers);
            formData.append('headers', headersJson);

            if (debugEnabled) {
                console.log("Webhook Data Payload:", Object.fromEntries(formData.entries()));
            }

            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (debugEnabled) {
                    console.log("Webhook Response:", data);
                }
                if (!data.success && debugEnabled) {
                    console.error("Webhook Failed:", data.data?.message || "Unknown error");
                }

                // Redirect after webhook success
                if (data.success && redirectURL) {
                    window.location.href = redirectURL;
                }
            })
            .catch(error => {
                if (debugEnabled) {
                    console.error("Fetch Error:", error);
                }
            });
        }, delay);
    });
    </script>

    <?php
    return ob_get_clean();
} 

add_shortcode('rup_auto_webhook', 'rup_hbs_auto_webhook_shortcode');







// ======================================
// On click Element Trigger - Shortcode
// ======================================

function rup_hbs_webhook_click_shortcode($atts) {
    $debug_enabled = isset($atts['rup-webhook-debug']) && $atts['rup-webhook-debug'] === 'true';

    // Load stored settings
    $stored_settings = get_option('rup_webhook_stored_settings', []);

    // Resolve the webhook URL
    $webhook_url = resolve_stored_value($atts['webhook'] ?? '', $stored_settings, $debug_enabled);

    if ($debug_enabled) {
        error_log("Webhook Click Shortcode - Raw Webhook Value: " . print_r($atts['webhook'], true));
        error_log("Webhook Click Shortcode - Resolved Webhook URL: " . print_r($webhook_url, true));
    }

    if (empty($webhook_url)) {
        return '<p style="color: red;">Webhook URL is invalid or missing!</p>';
    }

    // Process extra parameters and headers correctly
    $extra_params = [];
    $headers = [];
    $email = '';

    foreach ($atts as $key => $value) {
        if (strpos($key, 'header') === 0) {
            // Extract and resolve headers
            $header_value = resolve_stored_value($value, $stored_settings, $debug_enabled);
            $header_parts = explode(':', $header_value, 2);
            if (count($header_parts) === 2) {
                $headers[trim($header_parts[0])] = trim($header_parts[1]);
            } else {
                error_log("Invalid header format for $key: $value");
            }
        } elseif ($key === 'noemail' && $value === 'true') {
            $email = ''; // Ensure blank email
        } elseif ($key === 'email') {
            $email = resolve_stored_value($value, $stored_settings, $debug_enabled);
        } elseif (!in_array($key, ['webhook', 'element_id', 'class', 'rup-webhook-debug', 'capture-browser', 'capture-url', 'method', 'delay'])) {
            $extra_params[$key] = resolve_stored_value($value, $stored_settings, $debug_enabled);
        }
    }

    // If email is still empty and user is logged in, use their email
    if ($email === '' && !isset($atts['noemail']) && is_user_logged_in()) {
        $email = wp_get_current_user()->user_email;
    }

    ob_start();
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        let element = document.getElementById("<?php echo esc_js($atts['element_id']); ?>");
        if (!element) {
            <?php if ($debug_enabled) : ?>
                console.error("Element ID '<?php echo esc_js($atts['element_id']); ?>' not found.");
            <?php endif; ?>
            return;
        }

        element.addEventListener("click", function(event) {
            event.preventDefault();
            let webhookURL = "<?php echo esc_url($webhook_url); ?>";
            let debugEnabled = <?php echo json_encode($debug_enabled); ?>;
            let captureBrowser = <?php echo json_encode(isset($atts['capture-browser']) && $atts['capture-browser'] === 'true'); ?>;
            let captureURL = <?php echo json_encode($atts['capture-url'] ?? 'none'); ?>;
            let method = <?php echo json_encode(strtoupper($atts['method'] ?? 'POST')); ?>;
            let delay = <?php echo intval($atts['delay'] ?? 0); ?>;
            let extraParams = <?php echo json_encode($extra_params, JSON_UNESCAPED_SLASHES); ?>;
            let headers = <?php echo json_encode($headers, JSON_UNESCAPED_SLASHES); ?>;
            let email = "<?php echo esc_js($email); ?>";

            if (captureBrowser) {
                extraParams['userAgent'] = navigator.userAgent;
                extraParams['language'] = navigator.language;
                extraParams['screenWidth'] = window.screen.width;
                extraParams['screenHeight'] = window.screen.height;
                extraParams['viewportWidth'] = window.innerWidth;
                extraParams['viewportHeight'] = window.innerHeight;
                extraParams['platform'] = navigator.platform;
            }

            if (captureURL === "individual" || captureURL === "both") {
                let urlParams = new URLSearchParams(window.location.search);
                urlParams.forEach((value, key) => {
                    extraParams["url_" + key] = value;
                });
            }
            if (captureURL === "full" || captureURL === "both") {
                extraParams['pageURL'] = window.location.href;
            }

            setTimeout(() => {
                let formData = new FormData();
                formData.append("action", "rup_hbs_trigger_webhook");
                formData.append("webhook", webhookURL);
                formData.append("method", method);
                formData.append("debug", debugEnabled ? "true" : "false");
                formData.append("email", email);
                
                Object.keys(extraParams).forEach(key => {
                    formData.append(`extra_params[${key}]`, extraParams[key]);
                });
                
                let headersJson = JSON.stringify(headers);
                formData.append("headers", headersJson);

                if (debugEnabled) {
                    console.log("Sending Webhook Request:", Object.fromEntries(formData.entries()));
                }

                fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
                    method: "POST",
                    body: formData,
                })
                .then(response => response.json().catch(() => ({ success: false, message: "Invalid JSON Response" })))
                .then(data => {
                    if (debugEnabled) console.log("Webhook Response:", data);
                    if (!data.success) {
                        console.error("Webhook Failed:", data.message || "Unknown error");
                    }
                })
                .catch(error => {
                    if (debugEnabled) console.error("Fetch Error:", error);
                });
            }, delay);
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('rup_webhook_click', 'rup_hbs_webhook_click_shortcode');





