=== WPS Mass Embedder ===


== Description ==

Mass import thousands of Adult Videos from the best Porn Tubes.


== Features ==

= Mass import videos =
This is the main feature of this plugin. Mass import videos and their data (title, description, iframe, duration, thumbnails, tags and actors) right into WordPress posts. You can search and import videos from 50+ Tubes by selecting their categories or entering some keywords.

= Auto-pilot =
Import some new video day after day the automatic and lazy way. Thanks to WordPress or Server cron job, the plugin will be able to retrieve new videos from saved feeds, importing them without your intervention. You can set the frequency between each auto import to 1 hour - 6 hours - 12 hours - 24 hours.

= Monetization =
Thanks to the partner embed player, save your bandwidth and send traffic to the original tube to earn commissions. You can also display banners from adult advertising networks. All our themes are advertising ready.

= SEO friendly =
The neat admin UI allows you to rewrite any videos content you want easily. All data are imported in a clean way. Any well formed theme will be able to display data ready for search engines. All our themes do that the best way.

= Neat admin UI =
You will spend most of your time there. So we thought that it's better for you to work with a killer admin user interface.Designed with love with bootstrap and written in full JS with Vue.js, the admin UI is responsive and actions are made in real time.

= 50+ Tubes Partners integrated =
Import porn videos from 50+ of the biggest Tubes as xHamster, Xvideos, Pornhub, Redtube, Youporn, DrTuber, ePorner, Txxx, XNXX and many more. Some filters help you to choose the best tubes that will fit your needs. Sort them by popularity or alphabeticaly. Filter them by language, mobile ready, orientation (straight, gay, shemale), https ready or thumbnail rotation ready.

= Theme compatibility =
This plugin is 100% compatible with all WPS Themes. All our themes are ready to display videos in a smart and very easy way. They also are mobile and ads ready. It is also compatible with any WordPress theme by automaticaly configuring custom post types, custom post meta and custom taxonomies.

= Many options available =
Set up the plugin the way you want thanks to smart integrated options. Configure options for search results, default import post status (publish or draft), auto-pilot and so on.

= Videos data =
Just toggle some options to import only videos data you want (title, thumbnails, description, tags and actors).

= Display modes =
Choose between two videos display modes in retrieved videos list. Video Cards have bigger thumbnails while video lists allow you to change all videos data on the fly.

= Accurate videos selection =
Check every single retrieved video you want to import. This feature will allow you to bypass unwanted videos the fastest way. There is a bulk check button to check/uncheck all videos in one click.

= Edit on the fly =
Click on any video in the search result list to get all video informations. Watch the video preview, thumbnails gallery. Watch and edit the video title, description, tags and actors before importing videos.

= No duplicate videos =
All videos you already get in your database are bypassed when retrieving new videos. That's why you'll never have to enter any page number by hand and one by one to retrieve videos, unlike many competitor.

= Saved feeds =
Whenever you import videos from a new search, a Feed is saved. A Feed is a saved state, keeping your current search linked to the selected WordPress category. Your feeds are listed just bellow, providing informations and actions for each one. You can filter saved feeds, change each feed default post status when importing and toggle auto-pilot.


== Changelog ==

= 1.4.7 = Released on 2020-11-05
* Fixed: RedTube is now working again.

= 1.4.6 = Released on 2020-07-09
* Added: Multi thumbs on Nuvid
* Fixed: Nuvid import
* Fixed: Deactivation of auto-import for saved feeds whose wordpress category has been deleted
* Fixed Pornhub user agent that could prevent to retrieve videos

= 1.4.5 = Released on 2020-07-02
* Added: Options to toggle iframe sandbox on mobile
* Fixed: TXXX Thumbnails imported before v1.4.4 (background process when you are in the main plugin page)

= 1.4.4 = Released on 2020-05-26
* Fixed: Redtube trailers are now well retrieved when they are avalable (natively compatible with all wp-script themes)
* Fixed: Third party iframes was sandboxed by error, blocking embed scripts inside of them
* Fixed: Infinite loading on import in some cases caused by meta no referrer tag
* Fixed: xHamster duplicated videos
* Fixed: Videos with no title are now well imported with the default title 'Untitled'

= 1.4.3 = Released on 2020-01-20
* Fixed: RedTube is now working again.

= 1.4.2 = Released on 2019-12-17
* Added: New tube from TubeCorporate: ShemaleZ
* Updated: All TubeCorporate tubes configuration. Now you have to set your campaign source ID instead of your promo ID. You can find your campaign source ID on Tubecorporate.com > Video > Campaigns. Click on Edit button and you will see the video campaign Source ID (eg. 1665168004).

= 1.4.1 = Released on 2019-11-21
* Fixed: Xvideos thumbnails and trailers issues for new imports

= 1.4.0 = Released on 2019-08-23
* Fixed: Auto-import run on plugin page

= 1.3.9 = Released on 2019-08-23
* Fixed: Auto-import

= 1.3.8 = Released on 2019-08-22
* Fixed: Thumbs that were not imported with XML feeds on PHP 5.3
* Fixed: Force tooltips to hide

= 1.3.7 = Released on 2019-08-20
* Fixed: Isse with some iframes that prevented the iframe to be displayed

= 1.3.6 = Released on 2019-08-19
* Fixed: Call to undefined function str_get_html() error

= 1.3.5 = Released on 2019-08-19
* Added: Blocking iframe redirects for all integrated partners
* Added: New options to choose exactly the partners whose iframes you want to block
* Added: languages/amve_lang.pot file to translate the plugin
* Updated: Xtube has been temporary removed
* Updated: Pornxs has been temporary removed
* Fixed: Visual bug of the fixed search bar when scrolling down
* Fixed: PHP 5.3 error on install
* Fixed: Spankwire videos search
* Fixed: Pornomovies videos search
* Fixed: Pornrabbit videos search

= 1.3.4 = Released on 2019-07-29
* fixed: xVideos iframes error in the preview modal box after a search. xVideos iframes are well imported but viewable on the frontend only.

= 1.3.3 = Released on 2019-05-23
* Updated: Videos are now retrieved faster thanks to algorithm enhancement
* Fixed: Duplicated videos retrieved when you already have a lot of videos from a partner
* Fixed: Xhamster gay / shemale searches are now more relevant
* Fixed: FlyFlv now works with HTTPS

= 1.3.2 = Released on 2019-01-29
* Fixed: Tags and Actors that where retrieved only in the first video
* Fixed: Memory leaks caused by Tags and Actors detection with some tubes (ie. xHamster)

= 1.3.1 = Released on 2019-01-14
* Updated: Simple HTML DOM parser updated to v1.7
* Updated: VuePaginate.js updated to v3.6
* Fixed: Errors when searching for videos with keywords that contain capital letters
* Fixed: Merged thumbnails imported in auto-import mode (+ background fix for already imported thumbnails)

= 1.3.0 = Released on 2019-01-09
* Fixed: Memory leak issue when searching videos from the biggest Tubes (xHamster, Xvideos, RedTube, Youporn...). Sometimes no video was found. In any case, this will dramatically reduce the load of your server
* Fixed: Saved Feeds loading issue when a WordPress Category used by a saved feed has been removed

= 1.2.9 = Released on 2018-09-14
* Fixed: Pornhub video search

= 1.2.8 = Released on 2018-09-10
* Added: Redtube trailers (natively compatible with all wp-script themes)
* Added: xHamster trailers (natively compatible with all wp-script themes)
* Added: Xvideos trailers (natively compatible with all wp-script themes)
* Added: Youporn trailers, when available (natively compatible with all wp-script themes)
* Updated: search results display (thumbs and trailers details added)

= 1.2.7 = Released on 2018-09-03
* Added: You can now press enter when creating a WordPress category on the fly
* Added: Actors are now auto detected in titles
* Added: You can now use Proxy with authentication
* Added: New options to set up Proxy user and password for proxy authentication
* Fixed: Custom categories selection dropdown when importing is now working
* Fixed: Default partner selection issue after searching videos from a saved feed
* Fixed: "mb_detect_encoding" PHP function prerequired that prevented the plugin to work when not installed
* Fixed: vPorn searches work again
* Fixed: xHamster thumbnails rotation (removed)
* Fixed: Minor bugs

= 1.2.6 = Released on 2018-06-04
* Added: You can now search as many Pornhub videos as you want in any category or with any keywords!
* Updated: Pornhub search results have been enhenced
* Updated: Theme compatibilty options have been enhenced (Custom post type options are now select boxes instead of text fields)
* Fixed: Minor bugs

= 1.2.5 = Released on 2018-06-01
* Added: You can now remove unwanted video from searches (for ever!)
* Added: Details on the response of each video (success, already imported, unwanted, invalid) after a search
* Updated: Minor graphic improvements
* Fixed: Youporn/Youporn Gay search issue when selecting a two words category
* Fixed: Saved Feeds loading issue when a WordPress Category used by a saved feed has been removed
* Fixed: Infinite loading that can occures after migrating from old generation of plugin
* Fixed: Tooltips visual bugs
* Fixed: Minor bugs

= 1.2.4 = Released on 2018-05-24
* Fixed: Duplicated videos on some search results
* Added: Saving selected tab in video detail (Video data or Thumbnails) when switching from a video to an other
* Fixed: Minor bugs

= 1.2.3 = Released on 2018-05-17
* Fixed: Youporn and Youporn Gay thumbnails when importing videos
* Fixed: Minor bugs

= 1.2.2 = Released on 2018-05-15
* Fixed: Issue that prevented enable/disable auto-import option to work properly

= 1.2.1 = Released on 2018-05-14
* Fixed: Issue that prevented partner categories to be loaded with Firefox on slow servers
* Fixed: xhamster video searches that didn't work after xhamster main site changes

= 1.2.0 = Released on 2018-04-27
* Added: Close button to reset search when there is a search error/info alert
* Fixed: Upornia and TubePornClassic thumbs displaying issue
* Fixed: File Max size on Simple Html Dom issue that could prevent some videos searches to work

= 1.1.9 = Released on 2018-04-09
* Updated: PornTube removed from the list (their new api data is not compatible anymore)
* Added: Saved Feeds counter
* Fixed: Befuck videos search issue
* Fixed: JavaScript onload conflict with other plugins that prevented WP-Script pages to load in the admin dashboard
* Fixed: Feeds index JavaScript issues
* Fixed: Minor bugs

= 1.1.8 = Released on 2018-03-20
* Added: Add a new WP category on the fly in the WP category drop-down list (auto filled with the current category or keyword search to save time)
* Added: Switch between two search results display mode: cards like before and list that allow you to edit all videos the fastest way
* Added: Zoom on each thumbnail in the thumbnails tab when editing a video in the search results
* Added: Press enter (or click on the Search videos button) after entering a keyword to start a search the fastest way
* Fixed: Namespace has been added to Bootstrap to prevent conflicts with other plugins
* Fixed: Youjizz duplicated videos in search results
* Fixed: Youjizz embed code is now https
* Fixed: Arabic characters are now well imported
* Fixed: Minor bugs

= 1.1.7 = Released on 2018-02-28
* Updated: PornTube removed from the list (embed player not available anymore)
* Fixed: API calls errors when SERVER_NAME is empty
* Fixed: Minor bugs

= 1.1.6 = 2018-02-21
* Fixed: Auto import maximum execution time exceeded when last page to import is reached

= 1.1.5 = 2018-02-16
* Added: ExtremeTube Tube Partner is now part of the game
* Updated: Tube8 has been removed because its API from Hubtraffic is currently dead
* Updated: Tubes searches have been optimized for manual searches and auto-import
* Fixed: Porn.com now works like a charm (user agent issue fixed)

= 1.1.4 = 2018-02-01
* Fixed: Auto import issue with some categories
* Fixed: Redtube videos search
* Fixed: Xvideos search issue with some categories

= 1.1.3 = 2018-01-26
* Fixed: Error 400 issue when auto-importing videos in some cases

= 1.1.2 = 2018-01-08
* Added: Global Auto Import option to activate or deactivate Auto Import features
* Updated: Better Cron error logs
* Updated: Cron Start and Stop notice logs

= 1.1.1 = 2017-12-28
* Updated: Cron import system
* Fixed: Preventing data import for data uncheked in options
* Fixed: Undefined category when seaching videos from saved feeds whilst data isn't loaded yet
* Fixed: Minor bugs

= 1.1.0 = 2017-12-05
* Added: Error message when detecting that the server IP has been banned from Pornhub / Youporn / Redtube
* Fixed: Undefined keyword when searching from saved feeds
* Fixed: Auto-import feeds with keywords

= 1.0.9 = 2017-12-01
* Added: Player in the content option for maximum themes (others than WP-Script themes) compatibility
* Added: Player before or after the content option
* Fixed: Issue with some PHP versions that prevented feeds to be created or updated

= 1.0.8 = 2017-11-24
* Fixed: Auto-import
* Fixed: Tags that were not imported in auto-import mode

= 1.0.7 = 2017-11-23
* Fixed: Duplicated feeds when searching from a saved feed with tags
* Fixed: Default tag taxonomy that prevented tags to be imported ("post_tag" instead of "tag" in the custom taxonomy option)

= 1.0.6 = 2017-11-22
* Fixed: Bug that prevented the plugin sub-menu and tab to be displayed

= 1.0.5 = 2017-11-21
* Added: option to download or not the main thumb of imported videos
* Updated : WP-Script Core required message enhenced when WP-Script Core is not installed
* Fixed: Fatal error when activating the plugin manually WP-Script Core is not installed
* Fixed: Minor bugs

= 1.0.4 = 2017-11-20
* Added: Server Cron options
* Fixed: Auto-import
* Fixed: Minor bugs

= 1.0.3 = 2017-11-14
* Fixed: Xvideos Partner
* Fixed: XNXX Partner

= 1.0.2 = 2017-11-13
* Added: PornVe Tube Partner

= 1.0.1 = 2017-11-10
* Fixed: Glyphicons 500 error
* Fixed: Minor bugs

= 1.0.0 = 2017-11-02
* Initial release
