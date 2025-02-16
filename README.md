Webhook Button Shortcode Documentation
======================================

Overview
--------

 

This shortcode allows you to trigger a webhook with customisable parameters from
a button press. The button is fully stylable via CSS, supports custom text, and
can pass an unlimited number of parameters to the webhook.

 

Shortcode Format
----------------

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ html
[rup_webhook_button text="Button Text" after_text="After Click Text" webhook="YOUR_WEBHOOK_URL" class="custom-css-class" email="override@example.com" param1="value1" param2="value2" rup-webhook-debug="true" noemail="true" capture-url="both" capture-browser="true" method="POST" header1="Authorization: isstored:somebearertoken" header2="Custom-Header: TestValue" delay="5000" redirect="https://example.com/success"]
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

dd

Parameters
----------

### Required Parameters

| Parameter    | Description                                                                |
|--------------|----------------------------------------------------------------------------|
| `webhook`    | The URL of the webhook that will receive the request.                      |
| `text`       | The button text before being clicked.                                      |
| `after_text` | The text that replaces the button text after a successful webhook trigger. |

 

Additional Shortcode
--------------------

rup_auto_webhook webhook, works on page load due to the lack of on screen
feedback, this only fails if there is an issue with web hook which is its
minimum basic requirements, it will send a blank email to the hook if the user
isn’t logged in and the word press users email if they are logged in  
  
Other than this functionality is the same, except there is no need for class,
text and after_text as there is no screen output.

 

### Required Parameters

| Parameter | Description                                           |
|-----------|-------------------------------------------------------|
| `webhook` | The URL of the webhook that will receive the request. |

 

Shortcode Format
----------------

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
[rup_webhook_button text="Button Text" after_text="After Click Text" webhook="YOUR_WEBHOOK_URL" class="custom-css-class" email="override@example.com" param1="value1" param2="value2" rup-webhook-debug="true" noemail="true" capture-url="both" capture-browser="true" method="POST" header1="Authorization: isstored:somebearertoken" header2="Custom-Header: TestValue" delay="5000" redirect="https://example.com/success"]
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 

### Optional Parameters

| Parameter           | Description                                                                                                                                                                                                                                                                                             |
|---------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `email`             | Overrides the logged-in user's email. If not provided, the logged-in user's email is used. (Default is logged in user) *if using on a from without a logged in user you will get a warning so either conditionally display or set to noemail accepts email =”x\@x.com” rather than logged in user too.* |
| `class`             | Allows custom CSS styling by specifying a class name.                                                                                                                                                                                                                                                   |
| `rup-webhook-debug` | Set to `true` to enable debugging logs in `wp-content/debug.log`.                                                                                                                                                                                                                                       |
| `noemail`           | Set to `true` to allow webhook triggering **without requiring an email**. it still sends email but it will be “blank” at the receving end for those circumstances where no email is needed                                                                                                              |
| `paramX`            | Additional parameters (`param1`, `param2`, etc.) sent to the webhook as JSON.                                                                                                                                                                                                                           |
| `capture-url`       | Controls how URL parameters are sent: `individual` (each parameter as its own key-value pair), `full` (entire URL as `pageURL`), or `both` (sends both `individual` parameters and `pageURL`).                                                                                                          |
| `capture-browser`   | Set to `true` to include browser details such as user agent, screen size, platform, and language in the webhook payload.                                                                                                                                                                                |
| `method`            | Defines the HTTP method for the webhook request (`POST`, `GET`, `PUT`, or `DELETE`). Defaults to `POST`.                                                                                                                                                                                                |
| `headerX`           | Custom headers for the webhook request (e.g., `header1="Authorization: Bearer XYZ123"`).                                                                                                                                                                                                                |
| `delay`             | Sets a delay (in milliseconds) before sending the webhook request. Default is `0` (no delay).                                                                                                                                                                                                           |
| `redirect`          | URL to redirect the user to after a successful webhook trigger.                                                                                                                                                                                                                                         |
| `isstored:KEY_NAME` | Fetches stored webhook parameters from settings instead of hardcoding values.\`                                                                                                                                                                                                                         |

 

Stored Values System
--------------------

Admins can define global stored values under `Settings > Webhook Settings`.
Editors and authors can set personal stored values, but they cannot override
admin-defined settings.

### Example Usage with Stored Values

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ html
[rup_webhook_button text="Stored Webhook" after_text="Done!" webhook="isstored:globalWebhook"]
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ html
[rup_webhook_button text="Secure Request" after_text="Sent!" webhook="isstored:userWebhook" header1="Authorization: isstored:somebearertoken]
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

  
These work with emails, parameters and headers

 

 

Debugging & Troubleshooting
---------------------------

-   Enable debugging by adding `rup-webhook-debug="true"` to the shortcode.

-   Debugging logs are stored in `wp-content/debug.log`.

-   This will output console information too for the autowebhook, as well as
    very detailed logs in the debug.log

-   Ensure the webhook URL is correct and accepts JSON payloads.

-   Ensure the server allows **custom HTTP methods (GET, PUT, DELETE, etc.)**
    and **custom headers**.

-   This will work with bearer authorisation on FlowMattic

  
Note: I’ve checked POST/ PUT are received and function in FlowMattic, I have
been able to prove that Delete and get work but have been able to check the
received information and that the command is received, I don’t have a service to
check these on.  


Admin Settings Page
-------------------

-   Navigate to `Settings > Webhook Settings` to manage stored values.

-   Admins can define system-wide webhook URLs, headers, and default parameters.

-   Users (Editors, Authors) can store their own values if admins allow it.

-   Bulk **import/export** of stored settings in JSON format is available.
