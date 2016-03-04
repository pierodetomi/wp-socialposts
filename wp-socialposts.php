<?php
/**
* Plugin Name: WP Social Posts
* Plugin URI: http://pierodetomi.com/wordpress-plugins/socialposts
* Description: Get social with social sharing buttons on every post in your WordPress blog.
* Version: 1.0
* Author: Piero De Tomi
* Author URI: http://pierodetomi.com
* License: MIT
*/


// ************************************************************
// Frontend rendering functions
// ************************************************************
function wpsocialposts_add_social_buttons($content) {
    $show_on_posts = get_option("show_on_posts");
    $show_on_pages = get_option("show_on_pages");
    
    $is_post = is_singular();
    $is_page = is_page();
    
    $buttons = "";
    
    if($is_page) {
        if($show_on_pages) {
            $custom_fields = get_post_custom();
            
            $hide_buttons = isset($custom_fields) && isset($custom_fields["wpsocialposts_hide_buttons"])  ? $custom_fields["wpsocialposts_hide_buttons"][0] : 0;
            
            if( !$hide_buttons )
                $buttons = wpsocialposts_get_buttons_html();
        }
    }
    else if($is_post) {
        if($show_on_posts) {
            $custom_fields = get_post_custom();
            
            $hide_buttons = isset($custom_fields) && isset($custom_fields["wpsocialposts_hide_buttons"])  ? $custom_fields["wpsocialposts_hide_buttons"][0] : 0;
            
            if( !$hide_buttons )
                $buttons = wpsocialposts_get_buttons_html();
        }
    }
    
    return $buttons.$content;
};

function wpsocialposts_get_buttons_html() {
    $show_gplus_btn = get_option("show_gplus_btn");
    $show_facebook_btn = get_option("show_facebook_btn");
    $show_linkedin_btn = get_option("show_linkedin_btn");
    $show_twitter_btn = get_option("show_twitter_btn");
        
    // Add sharing button at the top of page/page content
    $social = '<div class="wp-socialposts-buttons">';
    
    if($show_gplus_btn)
        $social .= '<div class="gplus-button"><div class="g-plus" data-action="share" data-annotation="bubble"></div></div>';
    
    if($show_facebook_btn)
        $social .= '<div class="fb-button"><div class="fb-share-button" data-layout="button_count"></div></div>';
    
    if($show_linkedin_btn)
        $social .= '<div class="linkedin-button"><script src="//platform.linkedin.com/in.js" type="text/javascript"></script><script type="IN/Share" data-counter="right"></script></div>';
    
    if($show_twitter_btn)
        $social .= '<div class="twitter-button"><a href="https://twitter.com/share" class="twitter-share-button" data-via="pierodetomi" data-lang="it">Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script></div>';
    
    $social .= '</div>';
    
    return $social;
}

function wpsocialposts_load_plugin_css() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style("social-buttons", $plugin_url."css/social-buttons.css");
}

function wpsocialposts_add_scripts() {
    $show_gplus_btn = get_option("show_gplus_btn");
    $show_facebook_btn = get_option("show_facebook_btn");
        
    if($show_gplus_btn) {
        echo '
<script src="https://apis.google.com/js/platform.js" async="" defer="" gapi_processed="true">
  {lang: \'it\'}
</script>';
    }
    
    if($show_facebook_btn)
        wpsocialposts_render_fb_script();
    
    // Linkedin and Twitter scripts are embedded directly in the DOM together with
    // their buttons, so there are no scripts to include here for them.
}

function wpsocialposts_render_fb_script() {
    echo '
<script>
    var fbRoot = document.createElement("div");
    fbRoot.id = "fb-root";
    
    var fbScript = document.createElement("script");
    fbScript.innerText = "(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = \"//connect.facebook.net/it_IT/sdk.js#xfbml=1&version=v2.5\"; fjs.parentNode.insertBefore(js, fjs); }(document, \"script\", \"facebook-jssdk\"));";
    
    var fbCode = document.createDocumentFragment();
    fbCode.appendChild(fbRoot);
    fbCode.appendChild(fbScript);
    
    var body = document.getElementsByTagName("body")[0];
    body.insertBefore(fbCode, body.childNodes[0]);
</script>';
}

add_action("wp_enqueue_scripts", "wpsocialposts_load_plugin_css");
add_action("wp_print_footer_scripts", "wpsocialposts_add_scripts");
add_filter("the_content", "wpsocialposts_add_social_buttons");




 
// *****************************************************************
// Backend admin functions (settings & co.)
// *****************************************************************
function wpsocialposts_init() {
    register_setting("wpsocialposts_options", "show_gplus_btn", "intval");
    register_setting("wpsocialposts_options", "show_facebook_btn", "intval");
    register_setting("wpsocialposts_options", "show_linkedin_btn", "intval");
    register_setting("wpsocialposts_options", "show_twitter_btn", "intval");
    
    register_setting("wpsocialposts_options", "show_on_posts", "intval");
    register_setting("wpsocialposts_options", "show_on_pages", "intval");
}

function wpsocialposts_custom_admin_menu() {
    add_options_page(
        'WP Social Posts', // page title
        'WP Social Posts', // menu title
        'manage_options', // capability
        'wpsocialposts_options', // menu slug
        'wpsocialposts_options_page' // callback
    );
}

function wpsocialposts_options_page() {
    if ( ! isset( $_REQUEST['settings-updated'] ) )
          $_REQUEST['settings-updated'] = false;
    
    ?>
    
    <div class="wrap">
          <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
          
          <form method="post" action="options.php">
            <?php
                settings_fields( 'wpsocialposts_options' );
            
                $show_gplus_btn = get_option("show_gplus_btn");
                $show_facebook_btn = get_option("show_facebook_btn");
                $show_linkedin_btn = get_option("show_linkedin_btn");
                $show_twitter_btn = get_option("show_twitter_btn");
                
                $show_on_posts = get_option("show_on_posts");
                $show_on_pages = get_option("show_on_pages");
            ?>
            
            <h2 class="title">Buttons to show</h2>
            <p>Choose which sharing buttons you want to show in your posts and pages.</p>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e( 'Google Plus', 'wpsocialposts' ); ?></th>
                    <td>
                        <input id="show_gplus_btn" type="checkbox" name="show_gplus_btn" value="1" <?php checked($show_gplus_btn == 1); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Facebook', 'wpsocialposts' ); ?></th>
                    <td>
                        <input id="show_facebook_btn" type="checkbox" name="show_facebook_btn" value="1" <?php checked($show_facebook_btn == 1); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'LinkedIn', 'wpsocialposts' ); ?></th>
                    <td>
                        <input id="show_linkedin_btn" type="checkbox" name="show_linkedin_btn" value="1" <?php checked($show_linkedin_btn == 1); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Twitter', 'wpsocialposts' ); ?></th>
                    <td>
                        <input id="show_twitter_btn" type="checkbox" name="show_twitter_btn" value="1" <?php checked($show_twitter_btn == 1); ?>" />
                    </td>
                </tr>
            </table>
            
            <h2 class="title">Button locations</h2>
            <p>
                Choose where to show sharing buttons.<br />
                <strong>NOTE:</strong> This option is global, but you'll still be able to override it in every single post/page, adding a custom field with key "wpsocialposts_hide_buttons" and value "1".</p>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e( 'Posts', 'wpsocialposts' ); ?></th>
                    <td>
                        <input id="show_on_posts" type="checkbox" name="show_on_posts" value="1" <?php checked($show_on_posts == 1); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Pages', 'wpsocialposts' ); ?></th>
                    <td>
                        <input id="show_on_pages" type="checkbox" name="show_on_pages" value="1" <?php checked($show_on_pages == 1); ?>" />
                    </td>
                </tr>
            </table>
            
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
        </form>
     </div>
    
    <?php
}

add_action("admin_menu", "wpsocialposts_custom_admin_menu");
add_action("admin_init", "wpsocialposts_init");

?>