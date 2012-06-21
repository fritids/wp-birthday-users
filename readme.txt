=== Plugin Name ===
Contributors: omarke85
Tags: birthday, calendar, user, add-on, plugin
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=HPZRXMPY99LPS&item_name=Wordpress%20plugin&item_number=wp%20birthday%20users&currency_code=EUR
Requires at least: x.x.x
Tested up to: 3.4
Stable tag: 0.1.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Let users fill in there birthday.

== Description ==

This plugin will add additional fields in the profil of a user, they can fill in there birthday, choose if they want to share it and if they want to show there birthday.
When saved, the system will generate a ICAL-file that can included in many calendar programs/plugins.

As admin you can see an overview of all birthdays, with upcoming, passed and with some info about, how many registered birthdays, the oldest, youngest, average age.

> #### Upgrade from 0.1.x to 0.1.4
> If you upgrade to 0.1.4 please run the "rebuild birthdys"-script. This because of changes in storing files on the system.
> When using 0.1.6 and you changes an options the rebuild will be done for you.
> ### Caution: in version 0.1.3
> For some reason there is 2 breakspaces in the wp-birthday-users.php at the end. Delete these to let the plugin work. (I try to fix it later)

== Installation ==

1. Download <a href="http://wordpress.org/extend/plugins/wp-birthday-users/">wp-birthday-users</a> to a directory on your web server. 
2. Upload everything to the wp-content/plugins/ directory of your wordpress installation.
3. Log in as administrator and activate the plugin.
4. That's it.
5. Go to your profil and fill in your birthday. Save it.
6. This will create a birthday.ics-file in your upload-directory of wordpress.

If you upgrade:
Run rebuild-script found on the birthdays overview page.

== Frequently Asked Questions ==

= Who made this? =

Thanks to Omar Reygaert.

= What versions of wordpress does this plugin support? =

At the moment I have no idea, it's created on the last version (3.3.2) but I think this will still work on older versions.

= What versions of PHP does this support? =

This plugin should work wtih PHP 4.4 through last versions of php is always the best.

= I upgrade from a version before 0.1.3 and I see birthdays 2 times in the ical? =

Since 0.1.3 there is a change in the way the plugin saves the ical-file. If you encounter problems of duplicate events, run the "rebuild birthdays" on the overview page on "Birthday users". This will cleanupthe old usage and recreate it in the new way.

= I have version 0.1.3 but it gives an error when I activate the plugin, what to do? =

For some reason there went 2 breakspaces in the core plugin file. You can edit the file wp-birthday-users.php at the end by remove the breakspaces. Or you could upgrade to the last version.
I'm sorry for this.

== Changelog ==

= 0.1.6 =
* Fixed empty names in ical-file
* Fixed birthday view (wrong count)
* Added option to use the user display name or self-defined

= 0.1.5 =
* Fixed bug
* Added translation in the ical-file
* Added option to choose which name will be used
* Added option to choose who the birthday page can see
* Added translation German

= 0.1.4 =
* Fixed error with breakspace at the end of the php-file
* Added extra functionality

= 0.1.3 =
* Fixed bugfix: when no filled in birthdays
* Fixed empty names in overview
* Added rebuild-function
* Changed names of stored-files

= 0.1.2 =
* Fixed style error with Chrome
* Fixed the function to collapse upcoming/passed birthday info

= 0.1.1 =
* Added translation Dutch
* Added translation French

= 0.1 =
* Initial release.

== Upgrade Notice ==

= 0.1.6 =
Fix for empty names + additional functionality. Upgrade recommended

= 0.1.5 =
Adds additional functionality. Upgrade recommended

= 0.1.4 =
Should fix the error in version 0.1.3 Upgrade immediately.

= 0.1.3 =
This version fixes some errors in the system.  Upgrade immediately.
After upgrading, run the rebuild script (you can find this on the birthdays overview-page)

