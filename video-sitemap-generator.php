<?php
/*
Plugin Name: Video SEO, Youtube SEO Sitemap
Plugin URI: https://www.hdwplayer.com/video-sitemap-generator/
Description: This plugin will generate a XML Video Sitemap for your WordPress website. Open the <a href="tools.php?page=pvsg-video-sitemap-generate-page">settings page</a> to generate your video sitemap.
Author: Plestar Inc
Version: 1.0.0
License: GPLv2
*/

add_action ('admin_menu', 'pvsg_video_sitemap_generate_page');

function pvsg_video_sitemap_generate_page () {
    if (function_exists ('add_submenu_page'))
        add_submenu_page ('tools.php', __('WP Video Sitemap'), __('WP Video Sitemap'),
            'manage_options', 'pvsg-video-sitemap-generate-page', 'pvsg_video_sitemap_generate');
}

    /**
     * Checks if a file is writable and tries to make it if not.
     *
     * @since 3.05b
     * @access private
     * @author  VJTD3 <http://www.VJTD3.com>
     * @return bool true if writable
     */
    function pvsg_is_video_sitemap_writable($filename) {
        //can we write?
        if(!is_writable($filename)) {
            //no we can't.
            if(!@chmod($filename, 0666)) {
                $pathtofilename = dirname($filename);
                //Lets check if parent directory is writable.
                if(!is_writable($pathtofilename)) {
                    //it's not writeable too.
                    if(!@chmod($pathtoffilename, 0666)) {
                        //darn couldn't fix up parrent directory this hosting is foobar.
                        //Lets error because of the permissions problems.
                        return false;
                    }
                }
            }
        }
        //we can write, return 1/true/happy dance.
        return true;
    }

function pvsg_video_EscapeXMLEntities($xml) {
    return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $xml);
}

function pvsg_video_sitemap_generate () {
	if(!session_id()) {
		session_start();
	}
    if (sanitize_text_field($_POST ['submit']) && check_admin_referer( 'pvsg-nonce')) {    	
        $st = pvsg_video_sitemap_loop ();
        if (!$st) {			
			$_SESSION[ 'st_msg'] = '<br /><div class="error"><h2>Oops!</h2><p>Looks like none of your blog posts contain videos. Please publish a test post containing a YouTube, Dailymotion, Vimeo or HDW Player video and regnerate the video sitemap.</p><p>If the issue remains unresolved, please post the error message in this <a target="_blank" href="http://wordpress.org/tags/xml-sitemaps-for-videos?forum_id=10#postform">WordPress forum</a>.</p></div>';			
		} else{ $_SESSION[ 'st_msg'] = 'generated'; }
		echo '<script>window.location = "'.$_SERVER['REQUEST_URI'].'";</script>';
    }else if(sanitize_text_field($_POST ['update']) && sanitize_text_field($_POST['type']) == 'edit_xml' && check_admin_referer( 'pvsg-nonce')){
		$pvsg_video_sitemap_url = ABSPATH . '/sitemap-video.xml';
		$xml = stripslashes($_POST['xml_cotent']);
		$xml = esc_textarea($xml);		
	    if (pvsg_is_video_sitemap_writable(ABSPATH) || pvsg_is_video_sitemap_writable($pvsg_video_sitemap_url)) {
	    	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
	    	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
	    	$wp_filesystem = new WP_Filesystem_Direct('direct');
	        if ($wp_filesystem->put_contents ($pvsg_video_sitemap_url, $xml)) {
	           $_SESSION[ 'st_msg'] = 'updated';
	        }else{
	        	$_SESSION[ 'st_msg'] = 'Error to update Video sitemap XML.';
	        }
	    }else{
			$_SESSION[ 'st_msg'] = '<div class="wrap"><h2>Error writing the file</h2><p>The plugin was unable to save the xml to your WordPress root folder at <strong>' . ABSPATH . '</strong> probably because the folder doesn\'t have appropriate <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">write permissions</a>.</p></div>';			
    	}
    	echo ' <script> location.replace("'.$_SERVER['REQUEST_URI'].'"); </script>';
	 } else {  ?>		  
		<div class="wrap">
			<div class="con_note"><p>This plugin can only add upto five videos in video sitemap. Get the <a href="https://www.hdwplayer.com/en/wordpress-video-sitemap">premium version</a> to include more videos in your sitemap.</p></div>
			<h2>XML Sitemap for Videos</h2>
			<?php if(isset($_SESSION['st_msg'])){ ?>
				<div  class="con_div" style="background-color:#8e0000;color: #fff">		    
				  <?php if($_SESSION['st_msg'] == 'generated' || $_SESSION['st_msg'] == 'updated'){ ?>
					    <?php $sitemapurl = get_bloginfo('url') . "/sitemap-video.xml"; ?>
					    <p>The <a target="_blank" style="color: #faa" href="<?php echo $sitemapurl; ?>">XML Sitemap</a> was <?php echo $_SESSION['st_msg'] ?> successfully.</p>				    
			      <?php }else echo $_SESSION['st_msg']; ?>
			 	</div>		  
			<?php unset($_SESSION['st_msg']); } ?>
			<?php
			$pvsg_video_sitemap_url = ABSPATH . 'sitemap-video.xml';
			if(file_exists($pvsg_video_sitemap_url)){
				require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
				require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
				$xml_content = WP_Filesystem_Direct::get_contents($pvsg_video_sitemap_url);				
				$sitemapurl = get_bloginfo('url') . "/sitemap-video.xml";
			?>
		<div class="con_div">
			<p style="text-align: right;"><a target="_blank" class="btnn" href="http://www.google.com/webmasters/sitemaps/ping?sitemap=<?php echo $sitemapurl; ?>">Ping Your XML to Google</a></p>
			<h3>Update XML Sitemap for Videos</h3>
					<form id="options_form" method="post" action="">
					<?php wp_nonce_field('pvsg-nonce'); ?>
						<textarea name="xml_cotent" style="width: 100%; height: 400px;"><?php echo (stripslashes($xml_content)); ?></textarea>
						<input type="hidden" name="type" value="edit_xml" />
						<div class="submit">
				        	<input type="submit" name="update" id="sb_submit" value="Update Video Sitemap" />
				      	</div>
					</form>
					<h3 style="text-align: center">OR</h3>
			</div>
			<?php } $post_types = get_post_types('', 'names'); ?>
			<style>
				a.btnn{
			  		color: #8e0000;
				    background: #fff;
				    padding: 5px 15px;
				    text-decoration: none;
				    box-shadow: 1px 1px 1px #0000008f;
			  	}
				.con_div{
					width:800px; padding:10px 20px; background-color:#eee; font-size:.95em; font-family:Georgia;margin:20px;
				}
				.con_note{
					padding: 5px 10px;
				    background: #d7f5ff;
				    margin: 5px 0 20px 0;
				    border: solid 1px #bad5df;
				    border-radius: 4px;
				    width: 800px;
				}
				.con_note p{
				    margin: 0;
				}
			</style>
		  <div class="con_div">
		    <h3>Generate New XML Sitemap for Videos</h3>
		    <p>Sitemaps are a way to tell Google and other search engines about web pages, images and video content on your site that they may otherwise not discover. </p>
		    <form id="options_form" method="post" action="">
		    <?php wp_nonce_field('pvsg-nonce'); ?>
		      <div class="outer"><label for="post_type">Post Types</label> 
		      	<select name="pvsg_post_type[]" id="post_type" required multiple>		      	
			      	<option value="">-- Select Post Type --</option>
			      	<?php foreach ($post_types as $type){ ?>
			      	<option value="<?php echo $type ?>" <?php echo ($type == "post" || $type == "page")?"selected":"" ?>><?php echo $type ?></option>
			      	<?php } ?>
		      	</select>
	      	  </div>
		      <br>
		      <input type="checkbox" id="youtube" name="youtube" value="1" checked/>
		      <label for="youtube">Include YouTube videos?</label>
		      <br><br>
		      <input type="checkbox" id="dailymotion" name="dailymotion" value="1" />
		      <label for="dailymotion">Include Dailaymotion videos?</label>
		      <br><br>
		      <input type="checkbox" id="vimeo" name="vimeo" value="1" />
		      <label for="vimeo">Include Vimeo videos?</label>
		      <br><br>
		      <input type="checkbox" id="hdwplayer" name="hdwplayer" value="1" />
		      <label for="hdwplayer">Include HDW Player plugin videos? (You must installed HDW Player plugin in your website.)</label>
		       <br><br> <br><br>
		      <label for="">You must choose any one of the video type.</label>
		      <div class="submit">
		        <input type="submit" name="submit" id="sb_submit" value="Generate Video Sitemap" />
		      </div>
		    </form>
		    <p>Click the button above to generate a Video Sitemap for your website. Once you have created your Sitemap, you can submit it to Google using Webmaster Tools. </p>    
		  </div>
		</div>
		<style>
		.outer label {
		    display: inline-block;
		    width: 35%;
		    margin-bottom: 20px;
		}
		
		.outer input, .outer select {
		    width: 50%
		}
	</style>
	<?php }
}

function pvsg_video_sitemap_loop () {
    global $wpdb;

    $qa = array();
	
	if(intval($_POST['youtube'])){
		$qa[] = "post_content LIKE '%youtube.com%'";
	} 
	if(intval($_POST['dailymotion'])){
		$qa[] = "post_content LIKE '%dailymotion.com%'";
	}
	if(intval($_POST['vimeo'])){
		$qa[] = "post_content LIKE '%vimeo.com%'";
	}
	if(intval($_POST['hdwplayer'])){
		$qa[] = "post_content LIKE '%[hdwplayer%'";
	}
	
	if(empty($qa)) {
		echo '<br /><div class="error"><h2>Oops!</h2><p>You have to choose any one of the video type.</p></div>';
		exit();
	}
	
	$post_types = array_map('sanitize_text_field', $_POST['pvsg_post_type']);
	$post_types = implode("','", $post_types);
	$query = "SELECT id, post_title, post_content, post_date_gmt, post_excerpt
	FROM $wpdb->posts WHERE post_status = 'publish'
	AND post_type IN ('".$post_types."')
	AND (".implode(" OR ", $qa).")
	ORDER BY post_date DESC LIMIT 5";
	
    $posts = $wpdb->get_results ($query);

    if (empty ($posts)) {
        return false;
    } else {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";       
        $xml .= '<!-- Created by (http://wordpress.org/extend/plugins/video-sitemap-generator/) -->' . "\n";
        $xml .= '<!-- Generated-on="' . date("F j, Y, g:i a") .'" -->' . "\n";             
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/video-sitemap-generator/video-sitemap.xsl"?>' . "\n" ;        
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";
        
        $videos = array();
    
        foreach ($posts as $post) {
        	if(intval($_POST['youtube'])){
        		$xml .= pvsg_get_youtube($post,$videos);
        	}
        	if(intval($_POST['dailymotion'])){
        		$xml .= pvsg_get_dailymotion($post,$videos);
        	}
        	if(intval($_POST['vimeo'])){
        		$xml .= pvsg_get_vimeo($post,$videos);
        	}
        	if(intval($_POST['hdwplayer'])){
        		$xml .= pvsg_get_hdwplayer($post,$videos);
        	}
        }
        $xml .= "\n</urlset>";
    }
    $pvsg_video_sitemap_url = ABSPATH . '/sitemap-video.xml';
    if (pvsg_is_video_sitemap_writable(ABSPATH) || pvsg_is_video_sitemap_writable($pvsg_video_sitemap_url)) {
    	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
    	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
    	$wp_filesystem = new WP_Filesystem_Direct('direct');
    	if ($wp_filesystem->put_contents ($pvsg_video_sitemap_url, $xml)) {
            return true;
        }
    }
	echo '<br /><div class="wrap"><h2>Error writing the file</h2><p>The XML sitemap was generated successfully but the  plugin was unable to save the xml to your WordPress root folder at <strong>' . $_SERVER["DOCUMENT_ROOT"] . '</strong> probably because the folder doesn\'t have appropriate <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">write permissions</a>.</p><p>You can however manually copy-paste the following text into a file and save it as video-sitemap.xml in your WordPress root folder. </p><br /><textarea rows="30" cols="150" style="font-family:verdana; font-size:11px;color:#666;background-color:#f9f9f9;padding:5px;margin:5px">' . $xml . '</textarea></div>';    
    exit();
}

function pvsg_get_youtube($post,&$videos){
	$xml = '';
	if (strpos($post->post_content,"youtube.com") && preg_match_all ("/youtube.com\/(v\/|watch\?v=|embed\/)([a-zA-Z0-9\-_]*)/", $post->post_content, $matches, PREG_SET_ORDER)) {
		$c = 0;
		$excerpt = ($post->post_excerpt != "") ? $post->post_excerpt : $post->post_title ;
		$permalink = pvsg_video_EscapeXMLEntities(get_permalink($post->id));
	
		foreach ($matches as $match) {
	
			$id = $match [2];
			$fix =  $c++==0?'':' [Video '. $c .'] ';
			
			if(count($videos) >= 5) return $xml;
			
			if (in_array($id, $videos))
				continue;
	
			array_push($videos, $id);
	
			$xml .= "\n <url>\n";
			$xml .= " <loc>$permalink</loc>\n";
			$xml .= " <video:video>\n";
			$xml .= "  <video:player_loc allow_embed=\"yes\" autoplay=\"autoplay=1\">https://www.youtube.com/v/$id</video:player_loc>\n";
			$xml .= "  <video:thumbnail_loc>http://i.ytimg.com/vi/$id/hqdefault.jpg</video:thumbnail_loc>\n";
			$xml .= "  <video:title>" . htmlspecialchars($post->post_title) . $fix . "</video:title>\n";
			$xml .= "  <video:description>" . $fix . htmlspecialchars($excerpt) . "</video:description>\n";		
	
			$xml .= "  <video:publication_date>".date (DATE_W3C, strtotime ($post->post_date_gmt))."</video:publication_date>\n";
	
			$posttags = get_the_tags($post->id); if ($posttags) {
				$tagcount=0;
				foreach ($posttags as $tag) {
					if ($tagcount++ > 32) break;
					$xml .= "   <video:tag>$tag->name</video:tag>\n";
				}
			}
	
			$postcats = get_the_category($post->id); if ($postcats) {
				foreach ($postcats as $category) {
					$xml .= "   <video:category>$category->name</video:category>\n";
					break;
				}
			}
	
			$xml .= " </video:video>\n </url>";
		}
	}
	
	return $xml;
}


function pvsg_get_dailymotion($post,&$videos){
	$xml = '';
	if (strpos($post->post_content,"dailymotion.com") && preg_match_all ("/dailymotion.com\/(video\/|embed\/video\/)([a-zA-Z0-9\-_]*)/", $post->post_content, $matches, PREG_SET_ORDER)) {
		$c = 0;
		$excerpt = ($post->post_excerpt != "") ? $post->post_excerpt : $post->post_title ;
		$permalink = pvsg_video_EscapeXMLEntities(get_permalink($post->id));

		foreach ($matches as $match) {
			$id = $match [2];
			$fix =  $c++==0?'':' [Video '. $c .'] ';
			
			if(count($videos) >= 5) return $xml;
			
			if (in_array($id, $videos))
				continue;

			array_push($videos, $id);

			$xml .= "\n <url>\n";
			$xml .= " <loc>$permalink</loc>\n";
			$xml .= " <video:video>\n";
			$xml .= "  <video:player_loc allow_embed=\"yes\" autoplay=\"autoplay=1\">https://www.dailymotion.com/embed/video/$id</video:player_loc>\n";
			$xml .= "  <video:thumbnail_loc>https://www.dailymotion.com/thumbnail/video/$id</video:thumbnail_loc>\n";
			$xml .= "  <video:title>" . htmlspecialchars($post->post_title) . $fix . "</video:title>\n";
			$xml .= "  <video:description>" . $fix . htmlspecialchars($excerpt) . "</video:description>\n";


			$xml .= "  <video:publication_date>".date (DATE_W3C, strtotime ($post->post_date_gmt))."</video:publication_date>\n";

			$posttags = get_the_tags($post->id); if ($posttags) {
				$tagcount=0;
				foreach ($posttags as $tag) {
					if ($tagcount++ > 32) break;
					$xml .= "   <video:tag>$tag->name</video:tag>\n";
				}
			}

			$postcats = get_the_category($post->id); if ($postcats) {
				foreach ($postcats as $category) {
					$xml .= "   <video:category>$category->name</video:category>\n";
					break;
				}
			}

			$xml .= " </video:video>\n </url>";
		}
	}

	return $xml;
}

function pvsg_get_vimeo($post,&$videos){
	$xml = '';
	
	if (strpos($post->post_content,"vimeo.com") && preg_match_all ("/vimeo.com\/(video\/|embed\/video\/)([0-9]*)/", $post->post_content, $matches, PREG_SET_ORDER)) {
		$c = 0;
		$excerpt = ($post->post_excerpt != "") ? $post->post_excerpt : $post->post_title ;
		$permalink = pvsg_video_EscapeXMLEntities(get_permalink($post->id));		
		foreach ($matches as $match) {			
			$id = $match [2];
			$fix =  $c++==0?'':' [Video '. $c .'] ';

			if(count($videos) >= 5) return $xml;
			
			if (in_array($id, $videos))
				continue;
			
			array_push($videos, $id);

			$request = wp_remote_get("https://vimeo.com/api/oembed.json?url=https://vimeo.com/$id");
			$vcontent = wp_remote_retrieve_body( $request );			
			$vcontent = @json_decode($vcontent,true);
			
			
			
			$xml .= "\n <url>\n";
			$xml .= " <loc>$permalink</loc>\n";
			$xml .= " <video:video>\n";
			$xml .= "  <video:player_loc allow_embed=\"yes\" autoplay=\"autoplay=1\">https://vimeo.com/$id</video:player_loc>\n";
			if(isset($vcontent['thumbnail_url']))$xml .= "  <video:thumbnail_loc>".$vcontent['thumbnail_url']."</video:thumbnail_loc>\n";
			$xml .= "  <video:title>" . htmlspecialchars($post->post_title) . $fix . "</video:title>\n";
			$xml .= "  <video:description>" . $fix . htmlspecialchars($excerpt) . "</video:description>\n";

			$xml .= "  <video:publication_date>".date (DATE_W3C, strtotime ($post->post_date_gmt))."</video:publication_date>\n";

			$posttags = get_the_tags($post->id); if ($posttags) {
				$tagcount=0;
				foreach ($posttags as $tag) {
					if ($tagcount++ > 32) break;
					$xml .= "   <video:tag>$tag->name</video:tag>\n";
				}
			}

			$postcats = get_the_category($post->id); if ($postcats) {
				foreach ($postcats as $category) {
					$xml .= "   <video:category>$category->name</video:category>\n";
					break;
				}
			}

			$xml .= " </video:video>\n </url>";
		}
	}

	return $xml;
}

function pvsg_get_hdwplayer($post,&$videos){
	$xml = '';

	if (strpos($post->post_content,"[hdwplayer") && preg_match_all ("/\[hdwplayer.*id=([0-9]+)/", $post->post_content, $matches, PREG_SET_ORDER)) {
		$c = 0;
		$excerpt = ($post->post_excerpt != "") ? $post->post_excerpt : $post->post_title ;
		$permalink = pvsg_video_EscapeXMLEntities(get_permalink($post->id));
		global $wpdb;
		foreach ($matches as $match) {
			$id = $match [1];
			$posttags = get_the_tags($post->id);
			$postcats = get_the_category($post->id);
			/* if (in_array($id, $videos))
				continue;

			array_push($videos, $id); */
			
			$config  = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."hdwplayer WHERE id = %d",trim($id)));
			
			$pvideos = array();
			
			if($config->videoid){
				$pvideos = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."hdwplayer_videos WHERE id = %d",$config->videoid));
			}else if($config->playlistid){
				$pvideos = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."hdwplayer_videos WHERE playlistid = %d",$config->playlistid));
			}			
			
			if(!empty($pvideos)){
				foreach ($pvideos as $video){

					if(count($videos) >= 5) return $xml;
	
					if (in_array($video->id, $videos))
						continue;
					
					array_push($videos, $video->id);
					
					$fix =  $c++==0?'':' [Video '. $video->id .'] ';
					
					$xml .= "\n <url>\n";
					$xml .= " <loc>$permalink</loc>\n";
					$xml .= " <video:video>\n";
					$xml .= "  <video:player_loc allow_embed=\"yes\" autoplay=\"autoplay=1\">".$video->video."</video:player_loc>\n";
					if($video->preview)$xml .= "  <video:thumbnail_loc>".$video->preview."</video:thumbnail_loc>\n";
					$xml .= "  <video:title>" . htmlspecialchars($post->post_title) . $fix . "</video:title>\n";
					$xml .= "  <video:description>" . $fix . htmlspecialchars($excerpt) . "</video:description>\n";
					
					$xml .= "  <video:publication_date>".date (DATE_W3C, strtotime ($post->post_date_gmt))."</video:publication_date>\n";
					
					if ($posttags) {
						$tagcount=0;
						foreach ($posttags as $tag) {
							if ($tagcount++ > 32) break;
							$xml .= "   <video:tag>$tag->name</video:tag>\n";
						}
					}
					
					if ($postcats) {
						foreach ($postcats as $category) {
							$xml .= "   <video:category>$category->name</video:category>\n";
							break;
						}
					}
					
					$xml .= " </video:video>\n </url>";
				}
			}
		}
	}
	return $xml;
}
?>