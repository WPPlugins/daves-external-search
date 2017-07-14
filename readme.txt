=== Plugin Name ===
Contributors: csixty4
Donate link: http://catguardians.org/
Tags: search, Flickr, YouTube, Twitter, social
Requires at least: 2.8
Tested up to: 3.1
Stable tag: 1.1

Widget that displays on your WordPress search results page with similar items from your Flickr, YouTube, and Twitter feeds.

== Description ==

Widget that displays on your WordPress search results page with similar items from your Flickr, YouTube, and Twitter feeds.

For example, someone searching for "cat" on your site might see Flickr photos you've tagged with "cat", or
that funny YouTube video you took of your kitty the other day.

Currently searches:
* Flickr
* Twitter
* YouTube

...or any combination of the above!

View the announcement for this plugin at http://www.slideshare.net/fvcp/wpexternalsearch

== Installation ==

1. Upload `daves-external-search` directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the `Dave's External Search` widget to your sidebar and configure it
1. If you want to search Flickr pictures, go to the External Search screen under "Settings" and enter your Flickr API key (get one at http://www.flickr.com/services/api/keys/apply/)

== Frequently Asked Questions ==

= What is the minimum PHP version needed for this plugin? =

Dave's External Search requires PHP 5.0 or higher.

== Screenshots ==

1. Searching for "cat", displaying my Flickr and Twitter content

== Changelog ==

= 1.1 =
* WordPress 3.1 compatibility

= 1.0 =
* Fixed a bug looking up NSIDs when Flickr usernames contain spaces
* Note about leaving usernames blank

= 0.4 =
* Check for writeable cache dir
* Misc. text changes

= 0.3 =
* Only try to load a service's results if we have a username
* Use a thumbnail image instead of a tiny embedded player
* Admin screen for Flickr API key
* Configurable max # of results (defaults to 10)

= 0.2 =
* First official release. Added screenshot & readme.txt file
* Made usernames configurable
* Error handling in admin (invalid Flickr usernames)

= 0.1 =
* Initial "proof of concept" code. Everything hard-coded to my usernames.
