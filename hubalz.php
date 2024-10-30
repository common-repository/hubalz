<?php
/**
* Plugin Name: Hubalz
* Plugin URI: https://www.hubalz.com/
* Description: This plugin installs the Hubalz script to your site.
* Version: 1.1
* Author: Hubalz Team
**/

function hubalz_script() {
    $apikey = get_option('hubalz_apikey');
    ?>
    <script>
        var hubalzscript = document.createElement("script");
        hubalzscript.src = "https://www.hubalz.com/script.js";
        hubalzscript.async = 1;
        hubalzscript.dataset.apikey = "<?php echo esc_js($apikey); ?>";
        <?php if (get_option('hubalz_input_tracking') == 1) { ?>
            hubalzscript.dataset.noInputTracking = 1;
        <?php } ?>
        document.getElementsByTagName('head')[0].append(hubalzscript);
    </script>
    <?php
};

function hubalz_script_enqueue($is_foot){
    $script_name = 'hubalz-script-' . ($is_foot ? 'foot' : 'head') . '-script';

    $apikey = get_option('hubalz_apikey');
    
    wp_register_script($script_name, 'https://www.hubalz.com/script.js', array(), null, $is_foot);
    wp_enqueue_script($script_name);
    wp_script_add_data($script_name, 'async', '1');
    wp_localize_script($script_name, 'hubalz_script', array(
        'apikey' => $apikey,
        'noInputTracking' => get_option('hubalz_input_tracking') == 1
    ));
    
}
function hubalz_enqueue_scripts_head() {
    hubalz_script_enqueue(false);
}
function hubalz_enqueue_scripts_foot() {
    hubalz_script_enqueue(true);
}


// Hubalz script automatically stops doing it's stuff when there's another instance, so adding it multiple times is safe (at least it should be safe, i hope so)
add_action( 'wp_enqueue_scripts', 'hubalz_enqueue_scripts_head' );
add_action( 'wp_enqueue_scripts', 'hubalz_enqueue_scripts_foot' );
add_action('wp_head', 'hubalz_script');

add_filter( 'script_loader_tag', function ( $tag, $handle ) {

	if ( 'hubalz-script-foot-script' !== $handle && 'hubalz-script-head-script' !== $handle) {
		return $tag;
	}

    $apikey = get_option('hubalz_apikey');

	return str_replace( ' src', ' async data-apikey="' . esc_js($apikey) . '" ' . (get_option('hubalz_input_tracking') == 1 ? 'data-no-input-tracking="1"' : '') . ' src', $tag );
}, 10, 2 );


// create an admin menu where the user can edit the apikey, with logo.png as the logo
function hubalz_admin_menu() {
    add_menu_page('Hubalz',
    'Hubalz',
    'manage_options',
    'hubalz',
    'hubalz_admin_page',
    plugins_url('logo.png', __FILE__),
    99);
}

add_action('admin_menu', 'hubalz_admin_menu');

function hubalz_admin_page() {
    ?>
    <div class="wrap">
        <h1>Hubalz</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields("hubalz_section");
            do_settings_sections("hubalz");
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function hubalz_display_apikey() {
    ?>
    <input type="text" name="hubalz_apikey" id="hubalz_apikey" value="<?php echo esc_js(get_option('hubalz_apikey')); ?>" style="width: 32ch" />
    <?php
}
function hubalz_display_input_tracking() {
    ?>
    <input type="checkbox" name="hubalz_input_tracking" id="hubalz_input_tracking" value="1" <?php checked(1, get_option('hubalz_input_tracking'), true); ?> />
    <?php
}

function hubalz_settings() {
    add_settings_section("hubalz_section", "Configuration", null, "hubalz");
    add_settings_field("hubalz_apikey", "Domain Token", "hubalz_display_apikey", "hubalz", "hubalz_section");
    add_settings_field("hubalz_input_tracking", "Input tracking disabled", "hubalz_display_input_tracking", "hubalz", "hubalz_section");
    register_setting("hubalz_section", "hubalz_apikey");
    register_setting("hubalz_section", "hubalz_input_tracking");
}

add_action("admin_init", "hubalz_settings");
?>