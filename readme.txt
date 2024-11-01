=== TrainingPeaks Connect ===
Contributors: tpcguy213
Tags: trainingpeaks, trainingpeaks connect, training peaks, training peaks connect, workouts, fitness, multiple, garmin
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 1.1.0

This plug-in allows you to publish trainingpeaks.com workouts for one or more accounts on your blog or cms.
== Description ==

Allows WordPress authors to connect to and display recent activities from the TrainingPeaks website using a shortcode.
  
Note: This plug-in does not currently differentiate between private and public workouts, so both will appear on your blog where the shortcode is used.  However the TrainingPeaks.com profile page will still take into consideration which workouts are public/private.

Related Links:
TrainingPeaks: http://home.trainingpeaks.com/
My Site: http://eightandahalfdevelopments.wordpress.com/
== Installation ==

1. Delete any existing `training-peaks-connect` folder from the `/wp-content/plugins/` directory
2. Upload `training-peaks-connect` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the TrainingPeaks Connect Accounts Management page under “tools” to create a new User Account. 
5. On any page or post, type the shortcode along with the name of the created User Account: -trainingpeaks-connect#AccountName-  OR -training-peaks-connect#AccountName-

== Frequently Asked Questions ==

= How can I display a list of recent TrainingPeaks activities in a post or page? =
Create an account by going to the TrainingPeaks Connect management page by clicking ‘TrainingPeaks Connect’ under Tools.  Then include the chosen account name along with a TrainingPeaks Connect shortcode (  -training-peaks-connect#AccountName-  OR -trainingpeaks-connect#AccountName-  ) on any page or post.  Example: ‘-trainingpeaks-connect#JohnDoe-‘.
= Why is nothing showing up on my page/post? =
If you see either nothing at all, or just a title with no data, your logon credentials for TrainingPeaks may be incorrect.  You can turn on ‘Public Errors’ on the management screen to see a more details about the error on posts and pages.  Make sure that you have referenced the Account Name correctly (This field is NOT case-sensitive).  It is best practice to avoid using hyphens (‘-‘) and spaces in your account name.
= I turned on public comments, but nothing changed.  Why? =
TrainingPeaks Connect will only display comments left by users themselves on each workout under “Athlete Comments”.  

== Screenshots ==

1. Screenshot of the TrainingPeaks Connect plugin in action.
2. Screenshot of the TrainingPeaks Connect plugin in action with Public Comments turned on.
3. How to use the TrainingPeaks Connect shortcode within a post or page.
4. The TrainingPeaks Connect accounts management page.


== Changelog ==

= 1.1.0 =
* Changed all printed instances of 'Training Peaks' to 'TrainingPeaks'
* Modified documentation and text on pages accordingly
* Increased input size of Profile URL field on management page to 250 characters
* Added a new shortcode tag -trainingpeaks-connect#AccountName-, in addition to the existing one -training-peaks-connect#AccountName-

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
* Changed all printed instances of 'Training Peaks' to 'TrainingPeaks'
* Modified documentation and text on pages accordingly
* Increased input size of Profile URL field on management page to 250 characters
* Added a new shortcode tag -trainingpeaks-connect#AccountName-, in addition to the existing one -training-peaks-connect#AccountName-

== Licence ==

This plug-in is free for anyone.  It is GPL licensed, so it is free for use on both personal and commercial sites. 

