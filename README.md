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

 

Parameters
----------

 

### Required Parameters

| Parameter    | Description                                                                |
|--------------|----------------------------------------------------------------------------|
| `webhook`    | The URL of the webhook that will receive the request.                      |
| `text`       | The button text before being clicked.                                      |
| `after_text` | The text that replaces the button text after a successful webhook trigger. |

 

 

Additional Shortcode(s):
------------------------

#### `rup_auto_webhook webhook` Shortcode

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

#### `rup_webhook_click` Shortcode

`rup_webhook_click` triggers a webhook when an element (e.g., a button or link)
is clicked. Unlike `rup_auto_webhook`, this function requires an `element_id` to
bind the click event to a specific HTML element.

If `noemail="true"` is set, it sends a blank email. Otherwise, it sends the
logged-in user's email or an `isstored:` value if specified.

 

**Required Parameters**
-----------------------

| Parameter    | Description                                                    |
|--------------|----------------------------------------------------------------|
| `webhook`    | The URL of the webhook that will receive the request.          |
| `element_id` | The ID of the HTML element that triggers the webhook on click. |

 

**Optional Parameters Disabled on this shortcode are as follows:**
------------------------------------------------------------------

| Parameter  | Description                                                                  |
|------------|------------------------------------------------------------------------------|
| `redirect` | Disabled as it could be a link or button with its own link and may conflict. |

 

Shortcode Format
----------------

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
[rup_webhook_click element_id="my-link" webhook="isstored:webhookurl" class="webhook-button" param1="Value1" param2="123" XXX3="Testing" param4="true" param5="false" param6="SpecialChars!@#$%^&*" param7="AnotherParam" param8="2025-02-15" param9="ExtraData" param10="LongStringExample" param11="ShortText" param12="45.67" param13="SomeID-9876" param14="CustomValue" param15="FinalTest" capture-url="both" capture-browser="true" method="POST" header1="Authorization: isstored:flowmattic" header2="Custom-Header: TestValue" delay="1000" Testing="isstored:secret_value"  email="isstored:storedemail"]
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 

### Optional Parameters

| Parameter           | Description                                                                                                                                                                                                                                                                                             |
|---------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `email`             | Overrides the logged-in user's email. If not provided, the logged-in user's email is used. (Default is logged in user) *if using on a from without a logged in user you will get a warning so either conditionally display or set to noemail accepts email =”x\@x.com” rather than logged in user too.* |
| `class`             | Allows custom CSS styling by specifying a class name.                                                                                                                                                                                                                                                   |
| `rup-webhook-debug` | Set to `true` to enable debugging logs in `wp-content/debug.log`.                                                                                                                                                                                                                                       |
| `noemail`           | Set to `true` to allow webhook triggering **without requiring an email**. it still sends email but it will be “blank” at the receiving end for those circumstances where no email is needed, this allows you to set a blank email=””                                                                    |
| `paramX`            | Additional parameters (`param1`, `param2`, etc.) sent to the webhook as JSON.                                                                                                                                                                                                                           |
| `capture-url`       | Controls how URL parameters are sent: `individual` (each parameter as its own key-value pair), `full` (entire URL as `pageURL`), or `both` (sends both `individual` parameters and `pageURL`). **Note: Requires at least one empty parameter to be set i.e blank=””**                                   |
| `capture-browser`   | Set to `true` to include browser details such as user agent, screen size, platform, and language in the webhook payload.  Note: Requires at least one empty parameter to be set i.e blank=””                                                                                                            |
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

**Note: I’ve checked POST/ PUT are received and function in FlowMattic, I have
been able to prove that Delete and get work but have been able to check the
received information and that the command is received, I don’t have a service to
check these on.**

 

Admin Settings Page
-------------------

-   Navigate to `Settings > Webhook Settings` to manage stored values.

-   Admins can define system-wide webhook URLs, headers, and default parameters.

-   Users (Editors, Authors) can store their own values if admins allow it.

-   Bulk **import/export** of stored settings in JSON format is available.

Security
--------

I've hidden the isstored tokens from the front end code this doesn't mean they
can't be found by sniffing the headers or the Javascript using browser tools, as
with all tokens that are send using the browser there is a chance they will be
exposed Please keep this in mind
