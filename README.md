# Webhook Button Shortcode Documentation

## Overview
This shortcode allows you to trigger a webhook with customizable parameters from a button press. The button is fully stylable via CSS, supports custom text, and can pass an unlimited number of parameters to the webhook.

## Shortcode Format
```html
[rup_webhook_button text="Button Text" after_text="After Click Text" webhook="YOUR_WEBHOOK_URL" class="custom-css-class" email="override@example.com" param1="value1" param2="value2" rup-webhook-debug="true" noemail="true" capture-url="both" capture-browser="true" method="POST" header1="Authorization: Bearer XYZ123" header2="Custom-Header: TestValue" delay="5000" redirect="https://example.com/success"]
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
| `method` | Defines the HTTP method for the webhook request (`POST`, `GET`, `PUT`, or `DELETE`). Defaults to `POST`. |
| `headerX` | Custom headers for the webhook request (e.g., `header1="Authorization: Bearer XYZ123"`). |
| `delay` | Sets a delay (in milliseconds) before sending the webhook request. Default is `0` (no delay). |
| `redirect` | URL to redirect the user to after a successful webhook trigger. |

## Example Usage
### Basic Button
```html
[rup_webhook_button text="Send Data" after_text="Sent!" webhook="https://example.com/webhook"]
```

### Sending a GET Request Instead of POST
```html
[rup_webhook_button text="Fetch Data" after_text="Fetched!" webhook="https://example.com/api/data" method="GET"]
```

### Using Custom Headers
```html
[rup_webhook_button text="Secure Request" after_text="Sent!" webhook="https://example.com/api" header1="Authorization: Bearer XYZ123" header2="Custom-Header: Example"]
```

### Debugging & Troubleshooting
- Enable debugging by adding `rup-webhook-debug="true"` to the shortcode.
- Debugging logs are stored in `wp-content/debug.log`.
- Ensure the webhook URL is correct and accepts JSON payloads.
- Ensure the server allows **custom HTTP methods (GET, PUT, DELETE, etc.)** and **custom headers**.

---
🚀 **Now includes fully working method and headers support!** 🚀
