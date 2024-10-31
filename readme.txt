=== Remind me to change my password ===
Contributors: whodunitagency, leprincenoir, marineevain
Donate link: https://www.paypal.com/donate?hosted_button_id=35RPQ8DX9TQDW
Tags: password, reset password, manage passwords
Requires at least: 5.0
Tested up to: 5.9
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enhance the security of your website by managing the passwords expiry date and the suspension of inactive accounts.

== Description ==

= Enhance the security of your website by managing the passwords expiry date and the suspension of inactive accounts =

*Remind me to change my password* is the perfect plugin if you have to handle certain security requirements on your websites. Some fields of activity happen to require a frequent update of passwords. If this is you, this plugin will be of valuable help!

**With the *Remind me to change my password* plugin, you can easily:**

* set up the passwords period of validity
* set up a delay before the password has to be updated, once the expiry date is reached.
* define the roles this rule applies to
* automatically suspend the accounts that exceeded the delay for password update
* delete or reactivate a suspended account.

**Some little extras:**
A *privileged* account: in order to avoid losing access to the website, an admin account is appointed as impossible to suspend. This way, there will always be someone able to reactivate the suspended accounts. This account will not be submitted to the set up rule.
A nice and simple visibility in your back-office: you can set up a highlight color. It will help you visualize, at a glance to your users list, if some of them have reached their expiry date and need to update their password shortly.

The plugin set up interface is placed under the “Users” menu of your website, as this is where you manage the users and their passwords. The plugin sets up both an access to the basic parameters management screen (validity period, delays, etc), and to a suspended accounts management screen.


== Installation ==

1. Go to 'Plugins > Add New' in your WordPress dashboard and search for "Remind me to change my password". Install and activate the plugin.
2. Go to 'Users > Password Reset Manager'


== Screenshots ==

1. Settings page
2. Blocked users page
3. Password expired
4. Blocked user



== Features and Options: ==

* Passwords validity period management screen (validity period, delay before account suspension, roles the rule applies to, non suspendable account…)
* Automatic email alert sent to the user when the password has reached its expiry date
* Display of a warning message with a call-to-action button to update the password (the CTA send an autmatic email to the user with a password renewal link)
* Automatic suspension of the accounts that have exceeded the set up validity period without updating their password.
* Suspended accounts management screen, that allows to reactivate or delete these accounts.

== Available languages ==
The plugin is currently available in French and English.

== About us ==

“Remind me to change my password” is one of the WordPress plugins made by [Whodunit Agency.](https://www.whodunit.agency/)
Whodunit is a full-remote French WordPress agency. Founded in 2009, we are deeply involved in open-source development. Whodunit is the biggest agency in France in terms of contribution to the WordPress ecosystem.
We are building tailor-made editorial experiences for our clients and also providing high-level maintenance services. This activity is strongly related to our commitment to WordPress core development.


== Help and support ==

= How does the user know he has to update his password? =
Once the password expiry date is reached, an automatic email is sent to ethe user to request that he updates his password. Plus, once the users want to log in with an expired password, a warning message requests that he updates it, with a call-to-action button to send (or re-send) the automatic email with the password renewal link. The user does not have the ability to log in before updating his password.

= When the account is suspended, is it deleted? =
No. When the account is suspended, the user no longer has access to the website, the account no longer appears in the website accounts list, however it does appear in the suspended accounts list (under Users > Suspended accounts), where the admin can chose to reactivate the account or delete it permanently. Both actions request manual action from the admin, which is why the deletion is not automatic.

= If the account has been suspended but the user tries to log in, what happens? =
If the account is suspended (the user did not update his password in time), when the user tries to log in, an error message indicates that the account is suspended and advise to reach out to the website admin in order to reactivate the account. Then, the website admin can go to the suspended accounts management screen and manually reactivate the account. The user will receive a new email to update his password and finalize the procedure.

= If I am an admin of the website, can my account be suspended? =
The password management rules can apply to the “Administrator” role, therefore an admin can have his account suspended if he doesn’t update his password in due time. However, when setting up the plugin, an admin account is appointed as impossible to suspend. This way, there is always someone who can access the website, whatever the context, and reactivate the suspended accounts when needed.

= Does the password management rule defined with the plugin apply to all of the users roles? =
Not necessarily. The settings (password management and account suspension) apply to the roles selected by the admin when setting up the plugin. You are the one selecting the one or several role that are submitted to the rule: all of them, a selection, only one, depending on your own needs.

= When updating the password, is it possible to re-use the same one? =
No. A security rule prevents to use the same password as before. The user will have to define a new password and this way we can keep a good security level for his account.

= How, as an admin, can I reactivate a suspended account? =
In the administration panel, under Users > Suspended account(s), the admin can reactivate a suspended account that to the “magic wand” icon.

== Changelog ==

1.0
Initial release.