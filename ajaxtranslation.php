<?php
/*
Plugin Name: Google AJAX Translation
Plugin URI: http://blog.libinpan.com
Description: Add <a href="http://code.google.com/apis/ajaxlanguage/">Google AJAX Translation</a> onto your blog. This plugin enables your blog readers translate your blog comments into other languages. In the current version(0.1.*) I only enabled comment translation by default. This is because Google Ajax Translate only allow 500 characters right now (March 2008). But the function was already implemented, please feel free to uncomment line 84 if you want to try it. 
Author: Libin Pan
Version: 0.1.1
Stable tag: 0.1.1
Author URI: http://libinpan.com

Installation:
	1. Download the plugin and unzip it (didn't you already do this?).
	2. Put the 'ajaxtranslation.php' file into your wp-content/plugins/ directory.
	3. Go to the Plugins page in your WordPress Administration area and click 'Activate' next to Google AJAX Translation.
	4. Have fun with your blog readers.
		
Notes:
  - Right now only support translating the first 500 characters of your blog comments
  - I am using Google Ajax Translation to detect your text languages. It may not be 100% right, but close.
  - If you want to use it with your post too, please comment out line 84. But please notice all the html tag will be filtered out as google will translate the content inside of tags, which could mess your blog.
	- If you want to do some changes and want to share with all of us, please feel free to contact me @ libinpan@gmail.com or leave comments
	
TODO:
  - Add admin configuration page
  - Switch between flag icon and text for all these languages
  - Keep the format of post and comment
  - Support more than 500 characters?

Version history:
- .1.1
Small updates:
. Working on Admin/Comments pages too
. Fixed the comment format problem found by Sean

- .1
Initial Release
*/

/*
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*/

$languages = array('en' => 'English',
              'zh-CN' => 'Chinese(S)',
              'zh-TW' => 'Chinese(T)',
              'fr' => 'French',
              'ar' => 'Arabic',
              'nl' => 'Dutch',
              'de' => 'German',
              'el' => 'Greek',
              'it' => 'Italian',
              'ja' => 'Japanese',
              'ko' => 'Korean',
              'pt' => 'Portuguese',
              'ru' => 'Russian',
              'es' => 'Spanish');

function language_links($type, $id) {
  global $languages;
  $buf = '';
  foreach($languages as $key => $value) {
    $buf .= " <a href=\"javascript:google_translate('".$key."', '".$type."', ".$id.");\">".$value."</a>";
  }
  return $buf;
}

function translate_post_link($content) {
  global $post;
  $id = $post->ID;
  
	$post_link = "<div id='translate_post_"
	   .$id 
	   ."'><p></p>"
	   .$content
	   ."</div><hr/><p>View this Post in:"
     .language_links('post', $id)
		 ."</p>";

	return $post_link;
}

/* add_action('the_content', 'translate_post_link'); */

function translate_comment_link($content) {
  global $comment;
  $id = $comment->comment_ID;
  
	$comment_link = "<div id='translate_comment_"
	   .$id 
	   ."'><p></p>"
	   .$content
	   ."</div><hr/><p>View this Comment in:"
     .language_links('comment', $id)
		 ."</p>";

	return $comment_link;
}

add_action('comment_text', 'translate_comment_link');

function google_translate_js() {
  echo "<script type='text/javascript' src='http://www.google.com/jsapi'></script>
<script type='text/javascript'>
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
  ";
}

add_action('wp_footer', google_translate_js);
add_action('admin_footer', google_translate_js);
?>