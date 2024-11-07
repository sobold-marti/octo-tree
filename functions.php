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

function register_custom_blocks() {
    // Register script for the Text and Image block
    wp_register_script(
        'text-image-block-editor-script',
        get_template_directory_uri() . '/build/text-image.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        filemtime(get_template_directory() . '/build/text-image.js'), // Corrected comma
        true // Load script in the footer
    );

    // Register script for the Text block
    wp_register_script(
        'text-block-editor-script',
        get_template_directory_uri() . '/build/text.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        filemtime(get_template_directory() . '/build/text.js'), // Corrected comma
        true // Load script in the footer
    );

    // Register script for the Text block
    wp_register_script(
        'team-rollup-editor-script',
        get_template_directory_uri() . '/build/team-rollup.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        filemtime(get_template_directory() . '/build/team-rollup.js'), // Corrected comma
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
                    $('#mytheme_logo').val(attachment.url); // Set the image URL
                    $('#mytheme-logo-preview').attr('src', attachment.url).show(); // Show the preview
                    $('.remove-logo').show(); // Show the remove button
                });
                mediaUploader.open();
            });

            // Remove the logo
            $('.remove-logo').on('click', function(e) {
                e.preventDefault();
                $('#mytheme_logo').val(''); // Clear the value
                $('#mytheme-logo-preview').hide(); // Hide the preview
                $(this).hide(); // Hide the remove button
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
