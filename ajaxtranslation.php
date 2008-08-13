<?php
/*
Plugin Name: Google AJAX Translation
Plugin URI: http://blog.libinpan.com/2008/08/04/google-ajax-translation-wordpress-plugin/
Description: Add <a href="http://code.google.com/apis/ajaxlanguage/">Google AJAX Translation</a> onto your blog. This plugin enables your blog readers translate your blog posts or comments into other languages.
Author: Libin Pan and Michael Klein
Version: 0.3.1
Stable tag: 0.3.1
Author URI: http://libinpan.com

Installation:
	1. Download the plugin and unzip it (didn't you already do this?).
	2. Put the 'google-ajax-translation' folder into your wp-content/plugins/ directory.
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
- .3.1
. fixed some html-bugs (missing alt-Tags, etc.) (Michael Klein)

- .3.0
. encapsulate the plugin in a class. No global vars needed anymore, faster code (Michael Klein)
. Better support of capabilities-model (WP 2.6)

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

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*/


if (!class_exists('GoogleTranslation')) {
  class GoogleTranslation {

    var $optionPrefix = 'google_translation_';
    var $version      = '0.3.1';
    var $pluginUrl    = 'http://wordpress.org/extend/plugins/google-ajax-translation/';
    var $authorUrl    = 'http://blog.libinpan.com/2008/08/04/google-ajax-translation-wordpress-plugin/';

    var $languages = array(
      'en'    => 'English',
      'zh-CN' => 'Chinese(S)',
      'zh-TW' => 'Chinese(T)',
      'fr'    => 'French',
      'ar'    => 'Arabic',
      'bg'    => 'Bulgarian',
      'hr'    => 'Croatian',
      'cs'    => 'Czech',
      'da'    => 'Danish',
      'nl'    => 'Dutch',
      'fi'    => 'Finnish',
      'de'    => 'German',
      'el'    => 'Greek',
      'hi'    => 'Hindi',
      'it'    => 'Italian',
      'ja'    => 'Japanese',
      'ko'    => 'Korean',
      'no'    => 'Norwegian',
      'pl'    => 'Polish',
      'pt'    => 'Portuguese',
      'ro'    => 'Romanian',
      'ru'    => 'Russian',
      'es'    => 'Spanish',
      'sv'    => 'Swedish'
    );

    var $options = array(                               // default values for options
      'linkStyle'  => 'images',
      'postEnable' => false,
      'languages'  => array()
    );

    var $textDomain = 'wpgt';
    var $languageFileLoaded = false;
    var $pluginRoot = '';
    
    function getPluginUrl() {
    	$path = dirname(__FILE__);
    	$path = str_replace("\\","/",$path);
    	$path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path,strpos($path,"wp-content/")));
    	return $path;
    }
    
    function GoogleTranslation() {                      // Constructor
      $this->pluginRoot = $this->getPluginUrl();

      foreach ($this->options as $k=>$v) {              // get options from DB
        $this->options[$k] = get_option($this->optionPrefix.$k);
      }

      // Add action and filter hooks to WordPress
      add_action('admin_menu',   array(&$this, 'addOptionsPage'));
      add_action('wp_footer',    array(&$this, 'insertJs'));
      add_action('admin_footer', array(&$this, 'insertJs'));
      add_filter('comment_text', array(&$this, 'processComment'));
      
      if ($this->options['postEnable']) {
        add_filter('the_content',array(&$this, 'processContent'));
      }
    }

    function addOptionsPage(){
      add_options_page('Google Translation', 'Google Translation', 'manage_options', basename(__FILE__), array(&$this, 'outputOptionsPanel'));
    }

    function loadLanguageFile() {                       // loads language files according to locale
      if(!$this->languageFileLoaded) {
        load_plugin_textdomain($this->textDomain, $this->pluginRoot.'languages');
        $this->languageFileLoaded = true;
      }
    }

    function outputOptionsPanel() {
      $a = array();
      $p = $this->optionPrefix;
      foreach ($this->options as $k=>$v) $a[] = $p.$k;  // prefix all option-vars
      $page_options = join(',', $a);

      echo '<div class="wrap">';
      echo '<h2>'.__('Google Ajax Translation', $this->textDomain).'</h2> ';
      echo '<p>'.__('Version').'&nbsp;'.$this->version;
      echo ' | <a href="'.$this->authorUrl.'" target="_blank" title="'.__('Visit author homepage').'">Homepage</a>';
      echo ' | <a href="'.$this->pluginUrl.'" target="_blank" title="'.__('Visit plugin homepage').'">Plugin Homepage</a>';
      echo ' | <a target="_blank" title="Donate" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=libin_pan%40hotmail%2ecom&amp;item_name=Google%20Ajax%20Translation%20WP%20Plugin&amp;item_number=Support%20Open%20Source&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=USD&amp;lc=US&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8">Donate</a>';
      echo '</p>';
      echo '<form method="post" action="options.php">';
      wp_nonce_field('update-options');
      echo '<table class="form-table"> 
      <tr valign="top">
        <th scope="row">'.__('Link Style', $this->textDomain).'</th>
        <td>
          <p>
            <label><input name="'.$p.'linkStyle" type="radio" value="text" '. (($this->options['linkStyle'] == 'text')  ? 'checked="checked"':'').' /> Language Text</label><br />
            <label><input name="'.$p.'linkStyle" type="radio" value="image" '.(($this->options['linkStyle'] == 'image') ? 'checked="checked"':'').' /> Flag Icon</label>
          </p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">'.__('Enable post translation', $this->textDomain).'</th>
        <td>
          <input name="'.$p.'postEnable" type="checkbox" '.(($this->options['postEnable']) ? 'checked="checked"':'') .' />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">'.__('Languages').'</th>
        <td>';
          foreach ($this->languages as $k=>$v) {
            echo '<label><input type="checkbox" name="'.$p.'languages[]" value="'.$k.'" ';
            if (in_array($k,(array)$this->options['languages'])) echo 'checked="checked"';
            echo ' />&nbsp;<img src="'.$this->pluginRoot.'flags/'.$k.'.png" title="'.$v.'" />&nbsp;'.$v.'</label><br />';
          }
      echo '</td></tr></table>
      <p class="submit">
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="'.$page_options.'" />
        <input type="submit" name="Submit" value="'.__('Save Changes').'" />
      </p></form></div>';
    }

    function getLanguageLinks($type, $id) {
      $s = '';
      switch ($this->options['linkStyle']) {
        case 'image':
          foreach($this->options['languages'] as $lg) {
            $s .= " <a href=\"javascript:google_translate('$lg','$type',$id);\"><img src=\""
            .$this->pluginRoot.'flags/'.$lg.'.png" alt="'.$this->languages[$lg].'" title="'.$this->languages[$lg].'" /></a>';
          }
          break;
        case 'text':
        default:
          foreach($this->options['languages'] as $lg) {
            $s .= " <a href=\"javascript:google_translate('$lg','$type',$id);\">".$this->languages[$lg].'</a>';
          }
          break;
      }
      return $s;
    }

    function processContent($content = '') {
      global $post;
      if (!is_feed()) {                                 // ignore feeds
        //$this->loadLanguageFile();  // for future use
        $id = $post->ID;
        $content = '<div id="translate_post_'.$id.'"><p></p>'
          .$content
          .'</div><hr /><p>View this Post in:'.$this->getLanguageLinks('post', $id).'</p>';
      }
      return $content;
    }

    function processComment($content = '') {
      global $comment;
      $id = $comment->comment_ID;
      return '<div id="translate_comment_'.$id.'"><p></p>'
         .$content
         .'</div><hr /><p>View this Comment in:'.$this->getLanguageLinks('comment', $id).'</p>';
    }


    function insertJs() {
      echo <<<EOT
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load('language', '1');
    var original_posts = new Array();
    var original_comments = new Array();
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
          original_text = original_comments[id];
          if (original_text == null)
            original_text = original_comments[id] = text_node.innerHTML;
          break;
      }
      return original_text;
    }
    function google_translate_callback() {}
    google.setOnLoadCallback(google_translate_callback);
</script>
EOT;
    }
  }
}

if (class_exists('GoogleTranslation')) {                // instantiate the class
  $GoogleTranslation = new GoogleTranslation();
}
?>