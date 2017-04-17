Travis integration: [![Build Status](https://travis-ci.org/catalyst/moodle-local_csp.svg?branch=master)](https://travis-ci.org/catalyst/moodle-local_csp)
# moodle-local_csp

* [Why would you want this?](#why-would-you-want-this)
* [What is this?](#what-is-this)
* [How does it work?](#how-does-it-work)
* [Installation](#installation)
* [References](#references)

Why would you want this?
------------------------
Security, security, security.

This plugin helps you to detect and eliminate security errors in your Moodle such as:
 - Mixed content (https/http) after you switched to HTTPS.
 - Same origin (or specified origin) policy for scripts and media data. 

What is this?
-------------
This plugin enables [Custom Security Policy headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP) across the Moodle website. 
Examples: 
 - Report/enforce SSL origin for links, images etc.
 - Report/enforce same-origin for links, images etc.

How does it work?
-----------------
Site admin configures CSP headers: Content-Security-Policy or Content-Security-Policy-Report-Only in the plugin settings.
Header Content-Security-Policy-Report-Only is for recording CSP violations in Moodle and reviewing them later from the plugin's report page.
Enabling of Content-Security-Policy blocks browser from showing site resources that violate defined rules.

Installation
------------
Checkout or download the plugin source code into folder `local\csp` of your Moodle installation.
```sh
git clone git@github.com:catalyst/moodle-local_csp.git local\csp
```
or
```sh
wget https://github.com/catalyst/moodle-local_csp/archive/master.zip
mkdir -p local/csp
unzip master.zip -d local/csp
```
Then go to your Moodle admin interface and complete installation and configuration.
Example policy 'default-src https:;' will be reporting or enforcing the links to be HTTPS-only. Please note, the whole moodle website should be accessible via HTTPS for this to work.
For more examples of other CSP directives please read [here](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP).

References
----------
Relevant issue in Moodle tracker: (https://tracker.moodle.org/browse/MDL-46269)

A complementary plugin which works by searching the moodle DB for bad links:

https://github.com/moodlerooms/moodle-tool_httpsreplace


This plugin was developed by Catalyst IT Australia:

https://www.catalyst-au.net/

<img alt="Catalyst IT" src="https://cdn.rawgit.com/CatalystIT-AU/moodle-auth_saml2/master/pix/catalyst-logo.svg" width="400">
