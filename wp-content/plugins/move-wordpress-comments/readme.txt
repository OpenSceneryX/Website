=== Move WordPress Comments ===
Contributors: nkuttler
Author URI: http://www.nicolaskuttler.de/en/
Plugin URI: http://www.nkuttler.de/nkmovecomments/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=11041772
Tags: admin, plugin, comment, comments, move comment, move comments, widget, widgets
Requires at least: 2.7
Tested up to: 2.9
Stable tag: 0.2.1.1

This plugin allows you to easily move comments between posts and pages, and it allows you to change comment threading.

== Description ==

This plugin adds a small form to every comment on your blog.
The form is only added for admins and allows you to move comments to a different page and to fix comment threading.

This plugin is not designed for mass-moving of comments, rather for moving single comments from time to time. There are other plugins better suited for moving many comments at once.

The forms will be visible on every comment on your post/page, but not in the admin section. I found this more convenient than the existing solutions. There's also a widget included that allows you to turn the forms on and off in case you don't need them all the time.

= My plugins =

[Better tag cloud](http://www.nkuttler.de/wordpress/nktagcloud/): I was pretty unhappy with the default WordPress tag cloud widget. This one is more powerful and offers a list HTML markup that is consistent with most other widgets.

[Theme switch](http://www.nkuttler.de/wordpress/nkthemeswitch/): I like to tweak my main theme that I use on a variety of blogs. If you have ever done this you know how annoying it can be to break things for visitors of your blog. This plugin allows you to use a different theme than the one used for your visitors when you are logged in.

[Zero Conf Mail](http://www.nkuttler.de/wordpress/zero-conf-mail/): Simple mail contact form, the way I like it. No ajax, no bloat. No configuration necessary, but possible.

[Move WordPress comments](http://www.nkuttler.de/wordpress/nkmovecomments/): This plugin adds a small form to every comment on your blog. The form is only added for admins and allows you to [move comments](http://www.nkuttler.de/nkmovecomments/) to a different post/page and to fix comment threading.

[Delete Pending Comments](http://www.nkuttler.de/wordpress/delete-pending-comments): This is a plugin that lets you delete all pending comments at once. Useful for spam victims.

[Snow and more](http://www.nkuttler.de/wordpress/nksnow/): This one lets you see snowflakes, leaves, raindrops, balloons or custom images fall down or float upwards on your blog.

[Fireworks](http://www.nkuttler.de/wordpress/nkfireworks/): The name says it all, see fireworks on your blog!

[Rhyming widget](http://www.rhymebox.de/blog/rhymebox-widget/): I wrote a little online [rhyming dictionary](http://www.rhymebox.com/). This is a widget to search it directly from one of your sidebars.

== Installation ==
Unzip, upload to your plugin directory, enable the plugin. That's all. 

== Screenshots ==
1. The plugin's move comments form.

== Frequently Asked Questions ==
Q: How do I use your plugin?<br />
A: Log on as administrator and enabled the plugin. Identify and bring up the comments you want to move either on the post page. Change the Post Id (the first field) to the post number of the new post and click Move (or move the post to a new parent by changing the Parent number) in the same manner.

Q: What is the new post Id?<br />
A: It is easy if there are already posts on that page, but
if not then you have to find the Id some other way. There are several ways to do this, one of them is bring up the page or post in edit mode and check out the URL. It is the last number of the URL.

Q: I like this plugin, but the move comments form is too big for my taste.<br/>
A: Add something like <tt>.move-wordpress-comments * { font-size: xx-small; }</tt> to your template's <tt>style.css</tt>.

Q: But... everybody can move commments on my blog!?<br />
A: No, the move form is only visible to logged in admins.

== Changelog ==
= 0.2.1.1 ( 2010/03/17 ) =
 * FAQ updates, thanks to [Meini](http://www.utechworld.com/).
= 0.2.1 ( 2010/02/01 ) =
 * Widget improvements as suggested by [Tobias Bäthge](http://tobias.baethge.com/), thanks!
 * Complete I18N.
= 0.2.0 =
 * Improve security, usability and add some validation.
 * I18N
 * Thanks to [Milan](http://blog.milandinic.com/) and [Tobias Bäthge](http://tobias.baethge.com/) for all the suggestions.
 * Add [German translation](http://www.nicolaskuttler.de).
 * Change readme format, improve documentation.
= 0.1.1 =
 * Enable the forms by default, thanks to [Dayna](http://amourchaleur.com/) for directing my attention to this issue.
= 0.1.0 =
 * Added a widget to turn the comment moving forms on and off.
 * Fix some badly nested blocks.
= 0.0.3 =
 * Added a FAQ entry.
 * Fix messy first commit.
= 0.0.2 =
 * First WordPress.org release.
 * Two small bugfixes.
 * Doc updates.
= 0.0.1 =
 * First release.
