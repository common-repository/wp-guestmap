=== WP GuestMap ===
Contributors: Jiang Kuan
Donate link: 
Tags: Widget, GuestMap, Google, Google Map, GeoIP, Weather, Stats, Sidebar, Statistics
Requires at least: 2.0.0
Tested up to: 2.3.1
Stable tag: 1.8

The plugin is Google Map widget builder, currently four major widgets are supported: Guest Locator, Online Tracker, Stats Map and Weather Map. Now you can view your visitor's statistics via feed.


== Description ==

[WP GuestMap in your own language](http://blog.codexpress.cn/wordpress/wp-guestmap-i18n/ "WP GuestMap Internationalization")

If you have some problems with it, please leave a comments [HERE](http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/ "WP GuestMap Bug report")

The plugin is Google Map widget builder, currently three major widgets are supported:

* Guest Locator -- locate and display the current visitor on Google Map
* Online Tracker -- very similart to Guest Locator, except that it also show other online users
* Stats Map -- collect visitors' geolocation and make clouds on Google Map
* Weather Map -- show weather forecast of specific location on Google Map

It generates HTML codes, so that you can easily copy and paste it to sidebar widget or posts/pages. (To use it as a widget, go to **Presentation** ->**Widgets**, add a **Text Widgets** and paste the code there.)

**Guest Locator**

The simplest widget. Just put a welcome message (plain text or HTML), and the widget will locates your visitors and welcome them. Macros like %country%, %country_code%, %city%, %latitude% and %longitude% are available.

**Online Tracker**

Almost the same as **Guest Locator**, except that this widget also shows other online users, and refresh every minute. You have two extra tag %online_user_count% and  %online_other_user_count% besides those in **Guest Locator**.

[DEMO](http://blog.codexpress.cn/ "Online Tracker Demo")

**Stats Map**

This widget must be enabled to take effect. It loads little by little and finally displays all your visitors' location.

[DEMO](http://blog.codexpress.cn/guestmap/ "Stats Map Demo")

*Output Pagesize* is maximum output count each step(you needn't change it generally). *Date of Birth* is very useful option, only visitor visiting after the birthday will be shown on Stats Map. *Authentic Key* is a private key, with which you can subscribe statistics by RSS feed. A public Daily GeoRSS is also available.

[Public GeoRSS on Google Maps](http://maps.google.com/maps?f=q&ie=UTF8&z=2&q=http%3A%2F%2Fblog.codexpress.cn%2Fwp-content%2Fplugins%2Fwp-guestmap%2Ffeed.php%3Fvisual%3Denabled "WP GuestMap Daily GeoRSS")

**Weather Map**

This widget shows a simple weather report on Google Maps. You need to get your Google AJAX Feed API Key(From Google) and the location id (from Yahoo! Weather).


Upgrading & Other Infomation: please visit [http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/](http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/ "WP GuestMap Upgrade").

== Installation ==


1. Unzip the package and upload the folder **wp-guestmap** to **/wp-content/plugins/**.
1. Activate the plugin through the **Plugins** menu in WordPress.
1. Go to **WP GuestMap** on **Options** page, configurate your Google Maps API key and other settings in **Map Settings** section. *Save the settings* (the key must be valid, otherwise you cannot do further operations).
1. Adjust map options in **Map Builder** section (you can do that with the form or manipulate the map on the right directly)
1. Generate codes and paste them to where you need. To use it as a widget, go to **Presentation** ->**Widgets**, add a **Text Widgets** and paste the code there.

**For more information, please visit [http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/](http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/ "WP GuestMap Installation")**



== Screenshots ==

1. Guest Locator
2. Online Tracker
3. Stats Map
4. Weather Map
5. Visual feed for Stats Map on Google Maps

== Frequently Asked Questions ==

1. **The plugin doesn't work, what can I do?**
First, make sure you are using the [latest version of WP-GuestMap](http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/ "WP GuestMap Latest"). If it fails, please leave a comment [HERE](http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/ "WP GuestMap Bug Report") with the URL of the widget, your WordPress version, and other plugins you've installed.

1. **Why I cannot get the code in the option page?**
First, the plugin requires URL address containing the key, not just a key. So you should first enter something like __"http://maps.google.com/maps?file=api&v=2&key=your_keys"__, and save your settings. If you can get a small map in **Map Builder** Section, I thinks it begins to work. You can see it [HERE](http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/ "WP GuestMap Option Panel").
If you still fails to get that small map after the first step, please check whether you have enabled javascript. If you have, check whether there are javascript errors when you loading the option page.
The plugin itself will not cause a javascript error, but it is possible that some other plugin which inserts a wrong javascript in the option panel will affect this plugin. You may try to deactivate all the plugins except WP GuestMap, and after getting your code successfully, you can activate all of them again. If it still fails, please leave a comment with your blog URL, browser version and WP version.

1. **Why Stats Map does not load any icon?**
Open this link **{your-blog-url}/wp-content/plugins/wp-guestmap/output.php** in your browser. If it cannot be loaded as an XML document (browsers will report the error), it is certain there is something wrong. If you are using a freehost that automatically appends some extra code to the page, I'm sorry this widget is not for you, because AJAX based loader cannot parse those code; otherwise try the latest version.


== Changelog ==
(+ - new feature, * - bug fix or optimization, # - important notice.)

    * 2007-11-22 Version 1.7 Beta 2
      * Fix the compatibility problem with MySQL prior to 4.1.2
      * Fix the Stats Feed containing special German characters
	  
    * 2007-11-08 Version 1.7 Beta 1
      + Add a "X" button to let visitors choose whether to display WP GuestMap widgets
      # If visitors think WP GuestMap widgets slows down the site, they can disable the widgets by themselves and speed up loading
	  
    * 2007-11-01 Version 1.6.1
      * Fix several bugs in option panel
	  
    * 2007-10-30 Version 1.6
      + Add internationalization support
      + Add passive mode to Online Tracker
      * Fix a potential conflict between Stats Map and WP-Cache 
      * Correct some typos
	  
    * 2007-10-28 Version 1.5
      * Correct some potential Javascript errors in Stats Map and Online Tracker
	  
    * 2007-10-25 Version 1.5 Beta 4
      * Try to fix XML entity bugs in Stats Map and Online Tracker

    * 2007-10-24 Version 1.5 Beta 2
      + Add two options to Online Tracker (timeout and refresh rate)
      * Try to fix some bugs in Stats Map
      # I mean I try, but I don't know whether it is fixed or not.

    * 2007-10-23 Version 1.5 Beta 1
      + Add Online Tracker widget
      * Change the default text alignment in Guest Locator and Online Tracker (from center to justify)
      # Online Tracker is recommended to replace Guest Locator.
	  
	  
    * 2007-10-21 Version 1.4.1
      + Cache the widget page to speed up loading
      # It has limited effect, I think.
	  
	  
    * 2007-10-19 Version 1.4
      + Add an option to determine whether to use # <object> or <iframe>
      # If your theme is not XHTML Strict, use <iframe> since it loads faster than <object>.
	  
	  
    * 2007-10-17 Version 1.4 Beta 2
      + Add GeoRSS Feed that can let you view your stats on Google Maps
      # Try it HERE.
	  
    * 2007-10-14 Version 1.4 Beta 1
      + Add a RSS Feed to view your stats
      # <object> is replaced by <iframe> again temporarily in this beta version.
      # The RSS URL is {your-blog-url}/wp-content/plugins/wp-guestmap/feed.php?auth={authentic-key}. You can set the authentic key in Stats Map. The time is the server time, NOT GMT time OR your local time.
	  
    * 2007-10-10 Version 1.3
      * Fix the compatibility with XHTML strict.
      # <iframe> is replaced by <object> because XHTML Strict doesn't support <iframe>  at all. You may need to regenerate your code and paste it to where it should be again.
	  
    * 2007-10-02 Version 1.3 Beta 1
      * Reset all the old data in Stats Map.
      + Add an option "Date of Birth" for Stats Map.
      + Add an option to delete old data.
      # "Date of Birth" means Stats Map begin to collect the data from that day. Somewhat similar to birthday.
	  
    * 2007-09-30 Version 1.2-fix-2
      * Fix v1.2 again.
      # Find another mistake in v1.2.
	  
    * 2007-09-25 Version 1.2-fix
      * Fix GMarker shifting in Stats Map in v1.2.
      # To recover the wrong data in Stats Map, browse "fix-1.2.php". If no error occurs, delete "fix-1.2.php".
	  
    * 2007-09-24 Version 1.2
      * Enhance the performance of Stats Map.
      + Add an page size option for Stats Map which is greatly involved the performance.
      # The default value of page size is 25; in early versions, the fixed value was 100, which will knock down old machines. I've no idea what is the best value, so I let you determine that. Personally, I believe 20~30 is fine, which works fine on Firefox with an Pentium4 1.8GHz CPU(Willamette).
	  
    * 2007-09-22 Version 1.1
      * Fix a serious bug of Stats Map when collecting data.
      * Optimize Stats Map when resizing.
      * Enhanced security of Stats Map.
      # I made a foolish mistake since version 1.0a5, which will make Stats Map fail to gathering data.
	  
    * 2007-09-20 Version 1.0
      * Make the widgets xhtml valid.
      # Some users might download a wrong file. Fixed two hours later.
	  
    * 2007-09-17 Version 1.0 Beta 3
      * Bug fix for Weather Map: prevent it from loading cached (outdated) data.
	  
    * 2007-09-16 Version 1.0 Beta 2
      * Change the looking of Weather Map
	  
    * 2007-09-16 Version 1.0 Beta 1
      * Fix a little flaw in national flag package
      + Add some screenshots ( useless to users )
	  
    * 2007-09-15 Version 1.0 Alpha 5
      * Optimize Stats Map on the server side
      * Use markers of different colors to indicate visit density
	  
    * 2007-09-13 Version 1.0 Alpha 4
      * Almost reconstruct the whole thing. Some map options are removed from database.
      * TLabel's transparency bug solved (it is my or Microsoft's fault ¡ª IE6 doesn't support transparent PNG images)
      * Optimize Stats Map's XML loading process
      + Add a option to set whether to collect visitors' statistics
      + Add Weather Map, data from Yahoo! Weather, weather icons from MSN weather
	  
    * 2007-08-24 Version 1.0 Alpha 3
      * A few bug fixes on options page
      + Add basic statistical functions, now you could make a stats frame to map all your visitors. See it HERE.
	  
    * 2007-08-19 Version 1.0 Alpha 2
      * Speed up loading
      + Add a custom Info Window to display messages.
      # Info Window uses Tlabel by Tom Mangan
	  
    * 2007-08-18 Version 1.0 Alpha 1
      * First release.
      # National flags from frenchfragfactory.net, (c) Zarkof Cyberleagues
