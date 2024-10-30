=== Current Password? ===
Contributors: wpcurrentpassword
Tags: profile, user, password, security, protection
Requires at least: 4.9.0
Tested up to: 5.3.2
Stable tag: 2.1.1
Requires PHP: 5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a Current Password or Admin Password field to the Profile, Add New User and User Edit forms.

== Description ==

**[TL;DR]** Adds a Current Password or Admin Password field to the Profile, Add New User and User Edit forms. Available in 7 languages.

Require a user's or admin's current password as part of user password changing process on the dashboard.

Forgetting about an account and leaving it **logged in on devices that one might have no control over later** (think of publicly accessbile computers) is a **common mistake** among users. The WordPress community is probably aware of that too, that is why a "Log Out Everywhere Else" button was introduced in [version 4.1](https://wordpress.org/support/wordpress-version/version-4-1/#users), which provides the possibility of logging out of all (or except one – your current) active sessions. This button was added to the dashboard's Profile and User Edit pages, **but it is only visible if JavaScript is enabled** in your browser. WordPress also sends an e-mail to the user's registered e-mail address after password change, but that is only a notification that records the password change action, not a confirmation request to approve the new password.

Therefore, **WordPress does not have any built-in security to prevent an attacker changing the password** of a logged in account before the owner might have the chance to log in and click the "Log Out Everywhere Else" button on another machine (and sadly, many users don't even remember or care). The situation is even worse when an admin account is left logged in, since malicious accounts might be created with Administrator role, or existing user accounts might be compromised.

This plugin adds the functionality that should be in the WordPress core by default: users **must enter their own current password** when changing their password, and admins **must enter their admin password** when creating a new user or changing a user's password. This prevents the creation of malicious accounts and the takeover existing user accounts by those who gained access to the dashboard **without knowing password** of the account.

* Current Password and Admin Password fields are added seamlessly where necessary (see screenshots).
* Works without JavaScript (but with JavaScript it requires jQuery, which is included in WordPress by default).
* Hook into the `wpcp/profile_password_change` action to catch the user ID, current user password and the new user password on profile password change: `add_action('wpcp/profile_password_change', $user_id, $current_password, $new_password, 10, 3);`
* Use the `wpcp/user_password_change` action to catch the user ID (NULL if user is created), admin password and the new user password on user password change: `add_action('wpcp/user_password_change', $user_id, $admin_password, $new_password, 10, 3);`
* Available in 7 languages: Chinese (zh_CN), English (default), French (fr_FR), Hebrew (he_IL), Hungarian (hu_HU), Russian (ru_RU), Spanish (es_ES).

== Installation ==

Since the plugin's aim is to provide an extra layer of security to your WordPress site, we suggest you to **install it as a [Must Use plugin](https://wordpress.org/support/article/must-use-plugins/)**:

1. Download the plugin.
2. Unzip and upload the `current-password` plugin directory to `/wp-content/mu-plugins/`. If you don't have a `mu-plugins` directory, create it.
3. Move `current-password.php` plugin file from `/wp-content/mu-plugins/current-password/` to `/wp-content/mu-plugins/`.
4. Must Use plugins are activated by default, no manual activation is needed – Current Password and Admin Password fields will appear automatically.

(Installing the plugin through the WordPress plugins screen directly also works but is **not recommended**.)

== Screenshots ==

1. The Current Password field added above the New Password field on the dashboard's Profile form.
2. The Admin Password field added to the Add New User form.
3. The Admin Password field added above the New Password field on the dashboard's User Edit form.

== Changelog ==

= 2.1.1 =
* Added `wpcp/profile_password_change` and `wpcp/user_password_change` actions.
* Minor bug fixes.
* Updated readme.txt.

= 2.1.0 =
* Added translations for Spanish and Hebrew languages.
* Removed unnecessary JavaScript code that was added by mistake in 2.0.0.
* Updated readme.txt.

= 2.0.0 =
* Added Admin Password field and functionality to the Add New User form and User Edit form.
* Added translations for Chinese, French and Russian languages.
* Updated translations for Hungarian language.
* Updated readme.txt.

= 1.1.0 =
* Added description for current password input.
* Added support for internationalization.
* Added translations for Hungarian language.
* Updated assets (icon, banner, screenshot).
* Updated plugin description.

= 1.0.0 =
* Initial version.