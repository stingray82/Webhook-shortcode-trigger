# Webhook Button Shortcode Documentation

## Overview

This shortcode allows you to trigger a webhook with customizable parameters from a button press. The button is fully stylable via CSS, supports custom text, and can pass an unlimited number of parameters to the webhook.

## Shortcode Format

```html
[rup_webhook_button text="Button Text" after_text="After Click Text" webhook="YOUR_WEBHOOK_URL" class="custom-css-class" email="override@example.com" param1="value1" param2="value2" rup-webhook-debug="true" noemail="true"]

```

## Parameters

### Required Parameters

Parameter

Description

`webhook`

The URL of the webhook that will receive the request.

`text`

The button text before being clicked.

`after_text`

The text that replaces the button text after a successful webhook trigger.

### Optional Parameters

Parameter

Description

`email`

Overrides the logged-in user's email. If not provided, the logged-in user's email is used.

`class`

Allows custom CSS styling by specifying a class name.

`rup-webhook-debug`

Set to `true` to enable debugging logs in `wp-content/debug.log`.

`noemail`

Set to `true` to allow webhook triggering **without requiring an email**. The `email` field will be omitted from the JSON payload if this option is enabled.

`paramX`

Additional parameters (`param1`, `param2`, etc.) sent to the webhook as JSON.

## Example Usage

### Basic Button

```html
[rup_webhook_button text="Send Data" after_text="Sent!" webhook="https://example.com/webhook"]

```

### Custom Styling

```html
[rup_webhook_button text="Notify" after_text="Done!" webhook="https://example.com/webhook" class="custom-button"]

```

Apply CSS in your theme:

```css
.custom-button {
    background-color: #ff6600;
    color: white;
    font-size: 16px;
    padding: 12px 24px;
    border-radius: 8px;
}

```

### Debugging Enabled

```html
[rup_webhook_button text="Debug Test" after_text="Completed" webhook="https://example.com/webhook" rup-webhook-debug="true"]

```

### Sending Additional Parameters

```html
[rup_webhook_button text="Create" after_text="Created!" webhook="https://example.com/webhook" param1="User123" param2="Active" param3="Premium"]

```

### No Email Mode (for Webhooks That Don't Require an Email)

```html
[rup_webhook_button text="Trigger Webhook" after_text="Sent!" webhook="https://example.com/webhook" noemail="true"]

```

-   Works even if the **user is not logged in**.
-   **The webhook JSON will NOT contain an `email` field**.

## Webhook Data Format

When triggered, the webhook receives a `POST` request with the following JSON payload:

### If `noemail="true"` is NOT used:

```json
{
    "email": "user@example.com",
    "param1": "User123",
    "param2": "Active",
    "param3": "Premium"
}

```

### If `noemail="true"` is Used:

```json
{
    "param1": "User123",
    "param2": "Active",
    "param3": "Premium"
}

```

## Debugging & Troubleshooting

-   Enable debugging by adding `rup-webhook-debug="true"` to the shortcode.
-   Debugging logs are stored in `wp-content/debug.log`.
-   Ensure the webhook URL is correct and accepts JSON payloads.

## Styling the Button

By default, the button uses the following styles:

```css
.rup-webhook-button {
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    background-color: #0073aa;
    color: white;
    border: none;
    border-radius: 5px;
}

.rup-webhook-button:disabled {
    background-color: #aaa;
    cursor: not-allowed;
}

```

You can override these styles by adding your own CSS class and applying custom styles.

## Notes

-   The button can be placed anywhere on your WordPress site using a shortcode.
-   It works for both logged-in users and guest users (if an email is provided or `noemail="true"` is set).
-   The request is sent via AJAX to avoid page reloads.

----------

🚀 **Now you're all set to use and customize the webhook button!** 🚀