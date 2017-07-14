<?php 

/*
Plugin Name: Dave's External Search
Description: Search against Flickr, YouTube, and other sources
Author: Dave Ross
Version: 1.1
Author URI: http://davidmichaelross.com
*/

include_once(ABSPATH . WPINC . '/rss-functions.php');

if(!class_exists('DavesFileCache'))
	include('DavesFileCache.php');

if(5.0 > floatval(phpversion()))
	die("Dave's External Search requires PHP 5.0 or higher");

class DavesExternalSearch extends WP_Widget
{
  //const FLICKR_API_KEY = '8e847ed53de31295a781b1741c87f54d';
  //const FLICKR_API_SECRET = 'f94c8ea2bbbbd475';
  
	private $alertMessages;
	
	/////////
	// Setup
	/////////
	
	public static function register()
	{
		register_widget(__CLASS__);	
	}
	  
	// Constructor
	function DavesExternalSearch() {
		$this->alertMessages = array();
		
		$widget_ops = array ('classname' => 'daves_external_search', 'description' => __('Search external sites') );
		$control_ops = null;
		
		$this->WP_Widget('daves_external_search', __("Dave's External Search"), $widget_ops, $control_ops);
	}
	
	/////////////////////////////
	// WP_Widget implementation
	/////////////////////////////
	
	function widget($args, $instance)
	{
		if(array_key_exists('s', $_REQUEST))
		{
			extract($args, EXTR_SKIP);
			$options = get_option('daves-external-search');
			if(is_array($options)) {
		 		extract($options);
			}
	 		
	 		// Before Widget
			echo $before_widget;
			
			// Title
			$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
	
			// Flickr Results
			if(!empty($instance['flickr_nsid'])) {
				// Limit the number of results
				if(array_key_exists('flickr_count', $instance) && $instance['flickr_count']) {
					$flickrCount = $instance['flickr_count'];
				}
				else {
					// Default
					$flickrCount = 10;
				}
				
				$results = self::flickrCall($flickrAPIKey, 'flickr.photos.search', array(
					'user_id' => $instance['flickr_nsid'],
					'text' => $_GET['s'],
					'per_page' => $flickrCount
				));
				
				$items = $results['photos']['photo'];
				if($items)
				{
					echo "<h4>".__("Results from Flickr")."</h4>";
				}
				
				$photos = array_slice($items, 0, $flickrCount);
				
				foreach($photos as $index=>$photo)
				{
					$url = self::flickrPhotoUrl($photo);
					echo "<a href=\"http://www.flickr.com/photos/{$photo['owner']}/{$photo['id']}\"><img src=\"$url\" title=\"{$photo['title']}\" style=\"margin: 5px;\" /></a>";
				}
			}
						
			// YouTube Results
			if(!empty($instance['youtube_username'])) {
				$feedURL = self::youTubeURL($instance['youtube_username'], $_GET['s']);
				try {
					 $cache = DavesFileCache::forIdentifier($feedURL);
					 $feed = $cache->get();
					 print "<!-- YOUTUBE FROM CACHE!!! -->";	
				}
				catch(Exception $e) {
					$feed = fetch_rss($feedURL);
					$cache = new DavesFileCache($feedURL);
					$cache->store($feed, 300);
				}
				
				if($feed->items)
				{
					echo "<h4>".__("Results from YouTube")."</h4>";

					// Limit the number of results
					if(array_key_exists('youtube_count', $instance) && $instance['youtube_count']) {
						$youtubeCount = $instance['youtube_count'];
					}
					else {
						// Default
						$youtubeCount = 10;
					}
				
					$videos = array_slice($feed->items, 0, $youtubeCount);					
					foreach($videos as $item)
					{
						$id = substr($item['guid'], strrpos($item['guid'] , '/')+1);
						$title = $item['title'];
												
						echo self::youTubeEmbed($id, 150, 100, 'en', $title, $item['link']);
					}
				}
			}
									
			// Twitter Results
			if(!empty($instance['twitter_username'])) {
				$twitterSearchURL = self::twitterSearchURL($instance['twitter_username'], $_GET['s']);
				try {
					 $cache = DavesFileCache::forIdentifier($twitterSearchURL);
					 $feed = $cache->get();
					 print "<!-- TWITTER FROM CACHE!!! -->";	
				}
				catch(Exception $e) {
					$feed = fetch_rss($twitterSearchURL);
					$cache = new DavesFileCache($twitterSearchURL);
					$cache->store($feed, 300);
				}
				
				if($feed->items)
				{
					echo "<h4>".__("Results from Twitter")."</h4>";
					
					// Limit the number of results
					if(array_key_exists('twitter_count', $instance) && $instance['twitter_count']) {
						$tweetCount = $instance['twitter_count'];
					}
					else {
						// Default
						$tweetCount = 10;
					}					
					$tweets = array_slice($feed->items, 0, $tweetCount);
					foreach($tweets as $item)
					{
						$publishedTime = date('M d, Y h:i:sa', strtotime($item['published']));
						
						echo "{$item['title']} :: {$item['author_name']} at <a href=\"{$item['link']}\">$publishedTime</a><br /><br />";
					}
				}
			}

                        // Picasa Results
                        if(!empty($instance['picasa_username'])) {
                                $feedURL = self::picasaURL($instance['picasa_username'], $_GET['s']);
                                try {
                                         $cache = DavesFileCache::forIdentifier($feedURL);
                                         $feed = $cache->get();
                                         print "<!-- PICASA FROM CACHE!!! -->";
                                }
                                catch(Exception $e) {
                                        $feed = fetch_rss($feedURL);
                                        $cache = new DavesFileCache($feedURL);
                                        $cache->store($feed, 300);
                                }

                                if($feed->items)
                                {
                                        echo "<h4>".__("Results from Picasa")."</h4>";

                                        // Limit the number of results
                                        if(array_key_exists('picasa_count', $instance) && $instance['picasa_count']) {
                                                $picasaCount = $instance['picasa_count'];
                                        }
                                        else {
                                                // Default
                                                $picasaCount = 10;
                                        }

                                        $pictures = array_slice($feed->items, 0, $picasaCount);
                                        foreach($pictures as $item)
                                        {
						$matches = array();
						preg_match('/thumbnail url="([^"]*)"/', $item['atom_content'], $matches);
						echo "<a href=\"{$item['link']}\"><img src=\"{$matches[1]}\" title=\"{$item['summary']}\" style=\"margin: 5px;\" /></a>";

                                        }
                                }
                        }

			// After Widget
			echo $after_widget;
		}
	}
	
	function update($new_instance, $old_instance)
	{
		extract(get_option('daves-external-search'));
		
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['flickr_username'] = strip_tags($new_instance['flickr_username']);
		$instance['flickr_count'] = intval($new_instance['flickr_count']);
		$instance['twitter_username'] = strip_tags($new_instance['twitter_username']);
		$instance['twitter_count'] = intval($new_instance['twitter_count']);
		$instance['youtube_username'] = strip_tags($new_instance['youtube_username']);
		$instance['youtube_count'] = intval($new_instance['youtube_count']);
                $instance['picasa_username'] = strip_tags($new_instance['picasa_username']);
                $instance['picasa_count'] = intval($new_instance['picasa_count']);
		
		if($old_instance['flickr_username'] != $new_instance['flickr_username'])
		{
			// Flickr username has changed. Fetch the new username's NSID
			$results = self::flickrCall($flickrAPIKey, 'flickr.people.findByUsername', array('username' => urlencode($new_instance['flickr_username'])));

			if('fail' == $results['stat'])
			{
				$this->alertMessages['flickr_username'] = $instance['flickr_username'].': '.$results['message'];
				return FALSE;
			}

			$instance['flickr_nsid'] = $results['user']['nsid'];
		}
		
		return $instance;
	}
	
	function form ($instance)
	{
		$options = get_option('daves-external-search');
		if(!$options) { $options = array(); }
		extract($options);
		
		$instance = wp_parse_args( (array) $instance, array('title' => '', 'text' => '', 'flickr_username' => '', 'flickr_count'=>'', 'twitter_username' => '', 'twitter_count'=>'', 'youtube_username' => '', 'youtube_count'=>'', 'picasa_username' => '', 'picasa_count' => ''));
		
		$title = strip_tags($instance['title']);
		$text = format_to_edit($instance['text']);
		$flickr_username = format_to_edit($instance['flickr_username']);
		$flickr_count = format_to_edit($instance['flickr_count']);
		if('' == $flickr_count) { $flickr_count = 10; }
		$twitter_username = format_to_edit($instance['twitter_username']);
		$twitter_count = format_to_edit($instance['twitter_count']);
		if('' == $twitter_count) { $twitter_count = 10; }
		$youtube_username = format_to_edit($instance['youtube_username']);
		$youtube_count = format_to_edit($instance['youtube_count']);
		if('' == $youtube_count) { $youtube_count = 10; }
                $picasa_username = format_to_edit($instance['picasa_username']);
                $picasa_count = format_to_edit($instance['picasa_count']);
                if('' == $picasa_count) { $picasa_count = 10; }

		
		?>
		
		<?php /* Title */ ?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
		</label>
		</p>

		<p><?php echo __("If you don't want a service searched, leave that username blank and set that maximum to 0."); ?></p>
		
		<?php /* Flickr Username */ ?>		
		<p>
		<label for="<?php echo $this->get_field_id('flickr_username'); ?>"><?php _e('Flickr Username:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('flickr_username'); ?>" name="<?php echo $this->get_field_name('flickr_username'); ?>" type="text" value="<?php echo attribute_escape($flickr_username); ?>" />
		</label>
		<?php if($this->alertMessages['flickr_username']) : ?>
		<?php echo $this->alertMessages['flickr_username']; ?>
		<?php endif; ?>
		</p>
		
		<?php if(empty($flickrAPIKey)) : ?>
		<p style="color: #ff0000;">A Flickr API key is required. Please go to the <a href="options-general.php?page=daves-external-search/external_search.php">External Search settings page</a> and enter your API key.</p>
		<?php endif; ?>

		<?php /* Flickr Count */ ?>
		<p>
		<label for="<?php echo $this->get_field_id('flickr_count'); ?>"><?php _e('Maximum Flickr Results to show:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('flickr_count'); ?>" name="<?php echo $this->get_field_name('flickr_count'); ?>" type="text" value="<?php echo attribute_escape($flickr_count); ?>" />
		</label>
		<?php if($this->alertMessages['flickr_count']) : ?>
		<?php echo $this->alertMessages['flickr_count']; ?>
		<?php endif; ?>
		</p>
		
		<?php /* Twitter Username */ ?>
		<p>
		<label for="<?php echo $this->get_field_id('twitter_username'); ?>"><?php _e('Twitter Username:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('twitter_username'); ?>" name="<?php echo $this->get_field_name('twitter_username'); ?>" type="text" value="<?php echo attribute_escape($twitter_username); ?>" />
		</label>
		</p>
		
		<?php /* Twitter Count */ ?>
		<p>
		<label for="<?php echo $this->get_field_id('twitter_count'); ?>"><?php _e('Maximum Twitter Results to show:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('twitter_count'); ?>" name="<?php echo $this->get_field_name('twitter_count'); ?>" type="text" value="<?php echo attribute_escape($twitter_count); ?>" />
		</label>
		<?php if($this->alertMessages['twitter_count']) : ?>
		<?php echo $this->alertMessages['twitter_count']; ?>
		<?php endif; ?>
		</p>
		
		<?php /* YouTube Username */ ?>
		<p>
		<label for="<?php echo $this->get_field_id('youtube_username'); ?>"><?php _e('YouTube Username:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('youtube_username'); ?>" name="<?php echo $this->get_field_name('youtube_username'); ?>" type="text" value="<?php echo attribute_escape($youtube_username); ?>" />
		</label>
		</p>
		
		<?php /* YouTube Count */ ?>
		<p>
		<label for="<?php echo $this->get_field_id('youtube_count'); ?>"><?php _e('Maximum YouTube Results to show:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('youtube_count'); ?>" name="<?php echo $this->get_field_name('youtube_count'); ?>" type="text" value="<?php echo attribute_escape($youtube_count); ?>" />
		</label>
		<?php if($this->alertMessages['youtube_count']) : ?>
		<?php echo $this->alertMessages['youtube_count']; ?>
		<?php endif; ?>
		</p>
		
                <?php /* Picasa Username */ ?>
                <p>
                <label for="<?php echo $this->get_field_id('picasa_username'); ?>"><?php _e('Picasa Username:'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('picasa_username'); ?>" name="<?php echo $this->get_field_name('picasa_username'); ?>" type="text" value="<?php echo attribute_escape($picasa_username); ?>" />
                </label>
                </p>

                <?php /* Picasa Count */ ?>
                <p>
                <label for="<?php echo $this->get_field_id('picasa_count'); ?>"><?php _e('Maximum Picasa Results to show:'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('picasa_count'); ?>" name="<?php echo $this->get_field_name('picasa_count'); ?>" type="text" value="<?php echo attribute_escape($picasa_count); ?>" />
                </label>
                <?php if($this->alertMessages['picasa_count']) : ?>
                <?php echo $this->alertMessages['picasa_count']; ?>
                <?php endif; ?>
                </p>

		<?php
	}
	
	////////////////////
	// Flickr Interface
	////////////////////
	function flickrCall($apiKey, $method, $params)
	{
		// Build URL
		$url = "http://api.flickr.com/services/rest/?method=$method&format=php_serial&api_key=$apiKey";
		
		// Append parameters
		foreach($params as $name=>$value)
		{
			$url .= "&{$name}={$value}";
		}

		// Submit & return response
		try {
			 $cache = DavesFileCache::forIdentifier($url);
			 $result = $cache->get();
			 print "<!-- FLICKR FROM CACHE!!! -->";	
		}
		catch(Exception $e) {
			$result = file_get_contents($url);
			$cache = new DavesFileCache($url);
			$cache->store($result, 300);
		}
		$result = unserialize($result);

		return $result;
	}
	
	function flickrPhotoUrl($photo)
	{
		extract($photo);
		return self::flickrURL($farm, $server, $id, $secret, 's');	
	}
	
	function flickrURL($farmID, $serverID, $id, $secret, $size = 's')
	{
		$url = "http://farm{$farmID}.static.flickr.com/{$serverID}/{$id}_{$secret}_$size.jpg";
		
		return $url;
	}

	////////////////////
	// Picasa Interface
	////////////////////

	function picasaURL($userName, $query) {
		$url = "http://picasaweb.google.com/data/feed/api/user/".urlencode($userName)."?kind=photo&q=".urlencode($query);

		return $url;
	}

	/////////////////////	
	// YouTube Interface
	/////////////////////
	function youTubeUrl($userName, $query)
	{
		// Build URL
		$query = urlencode($query);
		$url = "http://gdata.youtube.com/feeds/api/users/$userName/uploads?alt=rss&q=$query";
		return $url;
	}
	
	function youTubeEmbed($id, $width = 320, $height = 265, $language = 'en', $title, $link)
	{
		$embed = '<div class="external-search-yt" style="text-align: center;">';

		if(!empty($title)) {
			$embed .= "<div class=\"external-search-yt-title\">";
			if(!empty($link)) {
				$embed .= "<a href=\"$link\" target=\"_blank\"><img src=\"http://img.youtube.com/vi/{$id}/1.jpg\" /><div>$title</div></a>";
			}
			else {
				$embed .= $title;
			}
			$embed .= "</div>";
		}
		
		$embed .= "</div>";

		return $embed;
	}
	
	/////////////////////
	// Twitter Interface
	/////////////////////
	function twitterSearchURL($userName, $query)
	{
		$userName = urlencode($userName);
		$query = urlencode($query);
		
		return "http://search.twitter.com/search.atom?q=$query%20from%3A$userName";
	}

	/////////////////////	
	// Utility Functions
	/////////////////////
	
	///////////////
	// Admin Pages
	///////////////
	
	/**
	 * Include the Live Search options page in the admin menu
	 * @return void
	 */
	function admin_menu()
	{
		if(current_user_can('manage_options'))
		{
			add_options_page("Dave's External Search Options", __('External Search', 'mt_trans_domain'), 8, __FILE__, array('DavesExternalSearch', 'plugin_options'));
		}
	}
	
	/**
	 * Display & process the Live Search admin options
	 * @return void
	 */
	public function plugin_options()
	{
		$thisPluginsDirectory = dirname(__FILE__);
		
		if("Save Changes" == $_POST['daves-external-search_submit'] && current_user_can('manage_options'))
		{
			check_admin_referer('daves-external-search-config');
			
			// Read their posted value
	        $flickrAPIKey = $_POST['daves-external-search_flickr_api_key'];

	        // Save the posted value in the database
	        update_option('daves-external-search', array(
	        	'flickrAPIKey' => $flickrAPIKey,
	        ));	
	        
	        // Translate the "Options saved" message...just in case.
	        // You know...the code I was copying for this does it, thought it might be a good idea to leave it
	        $updateMessage = __('Options saved.', 'mt_trans_domain' );	        
	        echo "<div class=\"updated fade\"><p><strong>$updateMessage</strong></p></div>";
		}
		else
		{
			$options = get_option('daves-external-search');
			if(!$options) { $options = array(); }
			extract($options);
		}
	        
		include("$thisPluginsDirectory/daves-external-search-admin.tpl");
	}
	
	public function admin_notices()
	{
		$thisPluginsDirectory = dirname(__FILE__);
		$cacheDir = "{$thisPluginsDirectory}/cache/";

		if(!DavesFileCache::testCacheDir()) {
			$alertMessage = __("The <em>Dave's External Search</em> plugin cannot write to its cache directory. Please check that {$cacheDir} exists and has the correct permissions.");
			echo "<div class=\"updated\"><p><strong>$alertMessage</strong></p></div>";
		}
	}
}

// Register
add_action('widgets_init', array('DavesExternalSearch', 'register'));
add_action('admin_menu', array('DavesExternalSearch', 'admin_menu'));
add_action('admin_notices', array('DavesExternalSearch', 'admin_notices'));

?>
