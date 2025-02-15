# Webhook Button Shortcode Documentation

## Overview
This shortcode allows you to trigger a webhook with customizable parameters from a button press. The button is fully stylable via CSS, supports custom text, and can pass an unlimited number of parameters to the webhook.

## Shortcode Format
```html
[rup_webhook_button text="Button Text" after_text="After Click Text" webhook="YOUR_WEBHOOK_URL" class="custom-css-class" email="override@example.com" param1="value1" param2="value2" rup-webhook-debug="true" noemail="true" capture-url="both" capture-browser="true"]
```

## Parameters
### Required Parameters
| Parameter  | Description |
|------------|-------------|
| `webhook`  | The URL of the webhook that will receive the request. |
| `text`     | The button text before being clicked. |
| `after_text` | The text that replaces the button text after a successful webhook trigger. |

### Optional Parameters
| Parameter  | Description |
|------------|-------------|
| `email`   | Overrides the logged-in user's email. If not provided, the logged-in user's email is used. |
| `class`   | Allows custom CSS styling by specifying a class name. |
| `rup-webhook-debug` | Set to `true` to enable debugging logs in `wp-content/debug.log`. |
| `noemail` | Set to `true` to allow webhook triggering **without requiring an email**. The `email` field will be omitted from the JSON payload if this option is enabled. |
| `paramX`  | Additional parameters (`param1`, `param2`, etc.) sent to the webhook as JSON. |
| `capture-url` | Controls how URL parameters are sent: `individual` (each parameter as its own key-value pair), `full` (entire URL as `pageURL`), or `both` (sends both `individual` parameters and `pageURL`). |
| `capture-browser` | Set to `true` to include browser details such as user agent, screen size, platform, and language in the webhook payload. |

## Example Usage
### Basic Button
```html
[rup_webhook_button text="Send Data" after_text="Sent!" webhook="https://example.com/webhook"]
```

### Capturing Browser Information
```html
[rup_webhook_button text="Capture Browser Info" after_text="Sent!" webhook="https://example.com/webhook" capture-browser="true"]
```
**Example Webhook Data:**
```json
{
    "email": "user@example.com",
    "userAgent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)...",
    "language": "en-GB",
    "screenWidth": 1920,
    "screenHeight": 1080,
    "viewportWidth": 1024,
    "viewportHeight": 768,
    "platform": "Win32"
}
```

### Capturing URL Parameters
#### Capture URL as Individual Key-Value Pairs
```html
[rup_webhook_button text="Capture Individual" after_text="Sent!" webhook="https://example.com/webhook" capture-url="individual"]
```
**Example Webhook Data:**
```json
{
    "email": "user@example.com",
    "url_ref": "google",
    "url_campaign": "summer"
}
```

#### Capture Full URL
```html
[rup_webhook_button text="Capture Full URL" after_text="Sent!" webhook="https://example.com/webhook" capture-url="full"]
```
**Example Webhook Data:**
```json
{
    "email": "user@example.com",
    "pageURL": "https://example.com?ref=google&campaign=summer"
}
```

#### Capture Both Individual Parameters and Full URL
```html
[rup_webhook_button text="Capture Everything" after_text="Sent!" webhook="https://example.com/webhook" capture-url="both"]
```
**Example Webhook Data:**
```json
{
    "email": "user@example.com",
    "pageURL": "https://example.com?ref=google&campaign=summer",
    "url_ref": "google",
    "url_campaign": "summer"
}
```

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
- Enable debugging by adding `rup-webhook-debug="true"` to the shortcode.
- Debugging logs are stored in `wp-content/debug.log`.
- Ensure the webhook URL is correct and accepts JSON payloads.

## Notes
- The button can be placed anywhere on your WordPress site using a shortcode.
- It works for both logged-in users and guest users (if an email is provided or `noemail="true"` is set).
- The request is sent via AJAX to avoid page reloads.

---
🚀 **Now you're all set to use and customize the webhook button!** 🚀
