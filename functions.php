<?php 

/*
* functions.php is only needed to enable WordPress Features we would like to use later for our REST API
*/

function blank_wordpress_theme_support() {

		 #Enables RSS Feed Links
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );

        add_theme_support( 'custom-logo' );
        add_theme_support(
			'custom-logo',
			array(
				'height'      => 256,
				'width'       => 256,
				'flex-height' => true,
				'flex-width'  => true,
				'header-text' => array( 'site-title', 'site-description' ),
			)
		);
    }

add_action( 'after_setup_theme', 'blank_wordpress_theme_support' );

/* Disable WordPress Admin Bar for all users */
add_filter( 'show_admin_bar', '__return_false' );

add_action('graphql_register_types', function() {
    register_graphql_field('Page', 'heroType', [
        'type' => 'String',
        'description' => 'The type of the hero section',
        'resolve' => function($post) {
            return get_post_meta($post->ID, 'hero_type', true);
        }
    ]);

    register_graphql_field('Page', 'heroHeading', [
        'type' => 'String',
        'description' => 'The heading for the hero section',
        'resolve' => function($post) {
            return get_post_meta($post->ID, 'hero_heading', true);
        }
    ]);

    register_graphql_field('Page', 'heroSubheading', [
        'type' => 'String',
        'description' => 'The subheading for the hero section',
        'resolve' => function($post) {
            return get_post_meta($post->ID, 'hero_subheading', true);
        }
    ]);

    register_graphql_field('Page', 'heroButtonText', [
        'type' => 'String',
        'description' => 'Text for the hero section button',
        'resolve' => function($post) {
            return get_post_meta($post->ID, 'hero_button_text', true);
        }
    ]);

    register_graphql_field('Page', 'heroButtonUrl', [
        'type' => 'String',
        'description' => 'URL for the hero section button',
        'resolve' => function($post) {
            return get_post_meta($post->ID, 'hero_button_url', true);
        }
    ]);

    register_graphql_field('Page', 'heroImage', [
        'type' => 'String',
        'description' => 'Hero Image URL',
        'resolve' => function($post) {
            $image_id = get_post_meta($post->ID, 'hero_image', true);
            return $image_id ? wp_get_attachment_image_url($image_id, 'full') : null;
        }
    ]);
});

function add_hero_meta_box() {
    add_meta_box(
        'hero_meta_box',               // ID of the meta box
        'Hero Section',                // Title of the meta box
        'display_hero_meta_box',       // Callback function to display fields
        'page',                        // Which post type to add this to (in this case, pages)
        'normal',                      // Context (normal, side, etc.)
        'high'                         // Priority
    );
}
add_action('add_meta_boxes', 'add_hero_meta_box');

function display_hero_meta_box($post) {
    // Add a nonce field to verify later
    wp_nonce_field(basename(__FILE__), 'hero_nonce');

    // Get current values (if they exist)
    $hero_type = get_post_meta( $post->ID, 'hero_type', true );
    $hero_heading = get_post_meta( $post->ID, 'hero_heading', true );
    $hero_subheading = get_post_meta( $post->ID, 'hero_subheading', true );
    $hero_button_text = get_post_meta( $post->ID, 'hero_button_text', true );
    $hero_button_url = get_post_meta( $post->ID, 'hero_button_url', true );
    $hero_image_id = get_post_meta($post->ID, 'hero_image', true);
    $hero_image_url = $hero_image_id ? wp_get_attachment_image_url($hero_image_id, 'medium') : '';

    ?>
    
    <label for="hero_type">Hero Type</label>
    <select name="hero_type" id="hero_type" class="widefat">
        <option value="none" <?php selected( $hero_type, 'none' ); ?>>None</option>
        <option value="standard" <?php selected( $hero_type, 'standard' ); ?>>Standard</option>
        <option value="image" <?php selected( $hero_type, 'image' ); ?>>Image</option>
    </select>

    <label for="hero_heading">Heading</label>
    <input type="text" name="hero_heading" value="<?php echo esc_attr( $hero_heading ); ?>" class="widefat" />

    <label for="hero_subheading">Subheading</label>
    <input type="text" name="hero_subheading" value="<?php echo esc_attr( $hero_subheading ); ?>" class="widefat" />

    <label for="hero_button_text">Button Text</label>
    <input type="text" name="hero_button_text" value="<?php echo esc_attr( $hero_button_text ); ?>" class="widefat" />

    <label for="hero_button_url">Button URL</label>
    <input type="text" name="hero_button_url" value="<?php echo esc_attr( $hero_button_url ); ?>" class="widefat" />
    
    <label for="hero_image">Hero Image</label>
    <div id="hero_image_wrapper">
        <button class="button" id="hero_image_button">Select Image</button>
        <input type="hidden" name="hero_image" id="hero_image" value="<?php echo esc_attr(get_post_meta($post->ID, 'hero_image', true)); ?>" />
        <div id="hero_image_preview">
            <?php if ($hero_image_url) : ?>
                <img src="<?php echo esc_url($hero_image_url); ?>" alt="Hero Image" style="max-width: 100%; height: auto;">
            <?php endif; ?>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var frame;

            // Open the media frame
            $('#hero_image_button').click(function(e) {
                e.preventDefault();

                // If the frame already exists, reopen it
                if (frame) {
                    frame.open();
                    return;
                }

                // Create the frame
                frame = wp.media({
                    title: 'Select or Upload Hero Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false // Allow only one image to be selected
                });

                // When an image is selected, update the input field with the image URL
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#hero_image').val(attachment.id); // Save the ID, not URL
                    $('#hero_image_preview').html('<img src="' + attachment.url + '" alt="Hero Image" style="max-width: 100%; height: auto;">');
                });

                // Open the frame
                frame.open();
            });
        });
    </script>
    <?php
}

function save_hero_meta($post_id) {
    // Check nonce for security
    if (!isset($_POST['hero_nonce']) || !wp_verify_nonce($_POST['hero_nonce'], basename( __FILE__ ))) {
        return $post_id;
    }

    // Save each field
    if (isset($_POST['hero_type'])) {
        update_post_meta($post_id, 'hero_type', sanitize_text_field($_POST['hero_type']));
    }

    if (isset($_POST['hero_heading'])) {
        update_post_meta($post_id, 'hero_heading', sanitize_text_field($_POST['hero_heading']));
    }

    if (isset($_POST['hero_subheading'])) {
        update_post_meta($post_id, 'hero_subheading', sanitize_text_field($_POST['hero_subheading']));
    }

    if (isset($_POST['hero_button_text'])) {
        update_post_meta($post_id, 'hero_button_text', sanitize_text_field($_POST['hero_button_text']));
    }

    if (isset($_POST['hero_button_url'])) {
        update_post_meta($post_id, 'hero_button_url', sanitize_text_field($_POST['hero_button_url']));
    }

    if (isset($_POST['hero_image'])) {
        $hero_image_id = intval($_POST['hero_image']);
        update_post_meta($post_id, 'hero_image', $hero_image_id);
    }
}
add_action('save_post', 'save_hero_meta');

function register_custom_blocks() {
    // Register script for the Text and Image block
    wp_register_script(
        'text-image-block-editor-script',
        get_template_directory_uri() . '/build/text-image.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        filemtime(get_template_directory() . '/build/text-image.js'),
        true // Load script in the footer
    );

    // Register script for the Text block
    wp_register_script(
        'text-block-editor-script',
        get_template_directory_uri() . '/build/text.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        filemtime(get_template_directory() . '/build/text.js'),
        true // Load script in the footer
    );

    // Register script for the Team rollup block
    wp_register_script(
        'team-rollup-editor-script',
        get_template_directory_uri() . '/build/team-rollup.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        filemtime(get_template_directory() . '/build/team-rollup.js'),
        true // Load script in the footer
    );

    // Register script for the Tabbed content block
    wp_register_script(
        'tabbed-content-editor-script',
        get_template_directory_uri() . '/build/tabbed-content.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        filemtime(get_template_directory() . '/build/tabbed-content.js'),
        true // Load script in the footer
    );

    // Register the Text and Image block
    register_block_type('custom/text-image', [
        'editor_script' => 'text-image-block-editor-script',
    ]);

    // Register the Text block
    register_block_type('custom/text', [
        'editor_script' => 'text-block-editor-script',
    ]);

    // Register the Team Rollup block
    register_block_type('custom/team-rollup', [
        'editor_script' => 'team-rollup-editor-script',
    ]);

    // Register the Tabbed content block
    register_block_type('custom/tabbed-content', [
        'editor_script' => 'tabbed-content-editor-script',
    ]);
}

// Hook into the init action to register the custom blocks
add_action('init', 'register_custom_blocks');

function mytheme_add_admin_menu() {
    add_menu_page(
        __( 'Theme Settings', 'mytheme' ),  // Page title
        __( 'Theme Settings', 'mytheme' ),  // Menu title
        'manage_options',                   // Capability
        'theme-settings',                   // Menu slug
        'mytheme_settings_page_html',       // Function to display page content
        'dashicons-admin-generic',          // Icon
        80                                  // Position
    );
}
add_action( 'admin_menu', 'mytheme_add_admin_menu' );

function mytheme_enqueue_media_uploader($hook) {
    // Only load the media uploader script on the theme settings page
    if ($hook !== 'toplevel_page_theme-settings') {
        return;
    }

    // Enqueue the WordPress media uploader
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'mytheme_enqueue_media_uploader');

function mytheme_settings_page_html() {
    // Check if the user has permission to access this page
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save the logo URL if the form is submitted
    if (isset($_POST['submit'])) {
        update_option('mytheme_logo', esc_url_raw($_POST['mytheme_logo']));
    }

    // Get the current logo value
    $logo = get_option('mytheme_logo');
    ?>

    <div class="wrap">
        <h1><?php _e('Theme Settings', 'mytheme'); ?></h1>

        <form method="post" action="">
            <?php settings_fields('mytheme_settings_group'); ?>
            <?php do_settings_sections('mytheme_settings_group'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Site Logo', 'mytheme'); ?></th>
                    <td>
                        <img id="mytheme-logo-preview" src="<?php echo esc_url($logo); ?>" style="max-width: 150px; display: <?php echo ($logo) ? 'block' : 'none'; ?>" />
                        <input type="hidden" id="mytheme_logo" name="mytheme_logo" value="<?php echo esc_attr($logo); ?>" />
                        <button type="button" class="button upload-logo"><?php _e('Upload Logo', 'mytheme'); ?></button>
                        <button type="button" class="button remove-logo" style="display: <?php echo ($logo) ? 'inline-block' : 'none'; ?>"><?php _e('Remove Logo', 'mytheme'); ?></button>
                        <p class="description"><?php _e('Upload your site logo here.', 'mytheme'); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var mediaUploader;

            $('.upload-logo').on('click', function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media.frames.file_frame = wp.media({
                    title: 'Choose Logo',
                    button: {
                        text: 'Choose Logo'
                    },
                    multiple: false
                });
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#mytheme_logo').val(attachment.url);
                    $('#mytheme-logo-preview').attr('src', attachment.url).show();
                    $('.remove-logo').show();
                });
                mediaUploader.open();
            });

            // Remove the logo
            $('.remove-logo').on('click', function(e) {
                e.preventDefault();
                $('#mytheme_logo').val('');
                $('#mytheme-logo-preview').hide();
                $(this).hide();
            });
        });
    </script>

    <?php
}

function mytheme_register_settings() {
    register_setting('mytheme_settings_group', 'mytheme_logo');
}
add_action('admin_init', 'mytheme_register_settings');

function mytheme_register_logo_rest_field() {
    register_rest_field( 'post', 'site_logo', array(
        'get_callback' => function() {
            return get_option('mytheme_logo');
        },
        'schema' => null,
    ));
}
add_action('rest_api_init', 'mytheme_register_logo_rest_field');

add_action( 'graphql_register_types', function() {
    register_graphql_field( 'RootQuery', 'siteLogo', [
        'type' => 'String',
        'description' => __( 'URL of the site logo', 'mytheme' ),
        'resolve' => function() {
            return get_option('mytheme_logo');
        }
    ]);
});

function register_custom_menus() {
    register_nav_menus(array(
        'primary-menu' => __('Primary Menu'),
    ));
}
add_action('after_setup_theme', 'register_custom_menus');
