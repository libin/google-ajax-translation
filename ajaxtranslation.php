<?php
/*
Plugin Name: Google AJAX Translation
Plugin URI: http://blog.libinpan.com/2008/08/04/google-ajax-translation-wordpress-plugin/
Description: Add <a href="http://code.google.com/apis/ajaxlanguage/">Google AJAX Translation</a> onto your blog. This plugin enables your blog readers translate your blog posts or comments into other languages.
Author: Libin Pan
Version: 0.2.0
Stable tag: 0.2.0
Author URI: http://libinpan.com

Installation:
	1. Download the plugin and unzip it (didn't you already do this?).
	2. Put the 'ajaxtranslation.php' file into your wp-content/plugins/ directory.
	3. Go to the Plugins page in your WordPress Administration area and click 'Activate' next to Google AJAX Translation.
	4. Have fun with your blog readers.
	5. Change the settings from Setting -> Google Translation Admin Page 
		
Notes:
  - Right now only support translating the first 500 characters of your blog comments
  - I am using Google Ajax Translation to detect your text languages. It may not be 100% right, but close.
  - If you want to use it with your post too, please comment out line 84. But please notice all the html tag will be filtered out as google will translate the content inside of tags, which could mess your blog.
	- If you want to do some changes and want to share with all of us, please feel free to contact me @ libinpan@gmail.com or leave comments
	
TODO:
  - Keep the format of post and comment
  - Support more than 500 characters?

Version history:
- .2.0
Thanks Michael Klein from alquanto.de for:
. Add Flag ICONs link style
. Add Flag ICONs
Others changes:
Add Admin Configuration Page
. Link Style: Text and Image
. Enable/Disable Post Translation
. Choose languages from the whole list

- .1.1
Small updates:
. Working on Admin/Comments pages too
. Fixed the comment format problem found by Sean

- .1
Initial Release

/*
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*/

$languages = array(
  'en'    => 'English',
  'zh-CN' => 'Chinese(S)',
  'zh-TW' => 'Chinese(T)',
  'fr'    => 'French',
  'ar'    => 'Arabic',
	'bg'		=> 'Bulgarian',
	'hr'		=> 'Croatian',
	'cs'		=> 'Czech',
	'da'		=> 'Danish',
  'nl'    => 'Dutch',
	'fi'		=> 'Finnish',
  'de'    => 'German',
  'el'    => 'Greek',
	'hi'		=> 'Hindi',
  'it'    => 'Italian',
  'ja'    => 'Japanese',
  'ko'    => 'Korean',
	'no'		=> 'Norwegian',
	'pl'		=> 'Polish',
  'pt'    => 'Portuguese',
	'ro'		=> 'Romanian',
  'ru'    => 'Russian',
  'es'    => 'Spanish',
	'sv'		=> 'Swedish'
);

function gt_add_options_page(){
  if(function_exists('add_options_page')){
    add_options_page('Google Translation', 'Google Translation', 9, basename(__FILE__), 'gt_options_panel');
  }
}

function gt_check_languages($gt_languages) {
	global $languages;
	if (!is_array($gt_languages)) {
		$gt_languages = ($gt_languages=="") ? array_keys($languages) : array($gt_languages);
		update_option("gt_languages", $gt_languages);
	}
	return $gt_languages;
}

function gt_options_panel() {
	global $languages;
  $gt_link_style = get_option('gt_link_style');
	$gt_post_enable = get_option('gt_post_enable');
	$gt_languages = gt_check_languages(get_option('gt_languages'));
?>

<div class="wrap"> 
  <h2><?php _e('Google Ajax Translation', 'wpgt') ?></h2> 
	<p>
		Version 0.2.0&nbsp;<a href="http://blog.libinpan.com/2008/08/04/google-ajax-translation-wordpress-plugin/" target="_blank" title="Homepage">Homepage</a>
		| <a href="http://wordpress.org/extend/plugins/google-ajax-translation/" target="_blank" title="Plugin Page">Plugin Page</a>
		| <a target="_blank" title="Donate" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=libin_pan%40hotmail%2ecom&amp;item_name=Google%20Ajax%20Translation%20WP%20Plugin&amp;item_number=Support%20Open%20Source&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=USD&amp;lc=US&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8">Donate</a>
	</p>
  <form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>
		<table class="form-table"> 
	  <tr valign="top">
			<th scope="row"><?php _e('Link Style', 'wpgt') ?></th>
			<td>
				<p><label><input name="gt_link_style"  type="radio" value="text" <?php if($gt_link_style == 'text') echo 'checked="checked"'; ?> /> Language Text</label><br />
				<label><input name="gt_link_style" type="radio" value="image" <?php if($gt_link_style == 'image') echo 'checked="checked"'; ?> /> Flag Icon</label></p>
			</td>
	  </tr>
		<tr valign="top">
    	<th scope="row">Enable Post Translation</th>
			<td>
				<input name='gt_post_enable' type='checkbox' value='yes' <?php if($gt_post_enable == "yes") echo "checked"; ?>>
			</td>
    </tr>
		<tr valign="top">
			<th scope="row"><?php _e('Languages', 'wpgt') ?></th>
			<td>
				<?php foreach ((array)$languages as $k=>$v){ ?>
					<input type="checkbox" name="gt_languages[]" value="<?=$k?>" <?=in_array($k,$gt_languages) ? 'checked="checked"':''?>>
					<img src="<?=gt_getpluginUrl().'flags/'.$k.'.png' ?>" title="<?=$v?>"/>
					<?=$v?>
					<br />
				<?php } ?>
			</td>
		</tr>
		</table>
    <p class="submit">
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="gt_link_style,gt_post_enable,gt_languages" />
			<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
		</p>
  </form> 
</div>

<?php 
}

add_action('admin_menu', 'gt_add_options_page');

function gt_getpluginUrl()
{
	$path = dirname(__FILE__);
	$path = str_replace("\\","/",$path);
	$path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path,strpos($path,"wp-content/")));
	return $path;
}

function language_links($type, $id) {
  global $languages;
  $gt_link_style = get_option('gt_link_style');
	$gt_post_enable = get_option('gt_post_enable');
	$gt_languages = gt_check_languages(get_option('gt_languages'));
  $buf = '';

  switch ($gt_link_style) {
    case 'image':
      foreach($gt_languages as $lg) {
        $buf .= " <a href=\"javascript:google_translate('$lg', '$type', $id);\"><img src=\""
        .gt_getpluginUrl().'flags/'.$lg.'.png" title="'.$languages[$lg].'" /></a>';
      }
      break;
    case 'text':
    default:
      foreach($gt_languages as $lg) {
        $buf .= " <a href=\"javascript:google_translate('$lg', '$type', $id);\">$languages[$lg]</a>";
      }
      break;
  }
  return $buf;
}

function translate_post_link($content) {
	if (get_option('gt_post_enable') != 'yes')
		return $content;
		
  global $post;
  $id = $post->ID;
  return '<span id="translate_post_'.$id.'">'.$content
    ."</span><hr /><p>View this Post in:".language_links('post', $id)."</p>";
}

add_action('the_content', 'translate_post_link');

function translate_comment_link($content) {
  global $comment;
  $id = $comment->comment_ID;

  return '<span id="translate_comment_'.$id.'">'
     .$content
     ."</span><hr/><p>View this Comment in:".language_links('comment', $id)."</p>";
}

add_action('comment_text', 'translate_comment_link');

function google_translate_js() {
  echo <<<EOT
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load('language', '1');
    var original_posts = new Array();
    var origianl_comments = new Array();
    function google_translate(lang, type, id){
      text_node = document.getElementById('translate_'+type+'_'+id);
      original_text = get_original_text(type, id, text_node);
      to_translate_text = original_text.replace(/<\/?[^>]*>/g, '');
      to_append_text = '';
      if (to_translate_text.length > 500)
        to_append_text = to_translate_text.substr(500);
      to_translate_text = to_translate_text.substr(0, 500);
      google.language.detect(to_translate_text, function(result) {
        if (!result.error && result.language) {
          google.language.translate(to_translate_text, result.language, lang, function(result) {
            if (!result.error)
              text_node.innerHTML = result.translation + to_append_text;
            else
              text_node.innerHTML = original_text;
          });
        }
      });
    }

    function get_original_text(type, id, text_node) {
      switch(type) {
        case 'post':
          original_text = original_posts[id];
          if (original_text == null)
            original_text = original_posts[id] = text_node.innerHTML;
          break;
        case 'comment':
          original_text = origianl_comments[id];
          if (original_text == null)
            original_text = origianl_comments[id] = text_node.innerHTML;
          break;
      }
      return original_text;
    }
    function google_translate_callback() {}
    google.setOnLoadCallback(google_translate_callback);
</script>
EOT;
}

add_action('wp_footer', google_translate_js);
add_action('admin_footer', google_translate_js);
?>