<?php
// Add a menu for our option page
// Hier niet nodig omdat galleriffic-bootstrap instellingen op de media pagina staan
// Draw the option page
// init text domain
add_action('admin_init', 'wp_galleriffic_bootstrap_textdomain');

function wp_galleriffic_bootstrap_textdomain() {
    if (function_exists('load_plugin_textdomain')) {
        load_plugin_textdomain('wp_galleriffic_bootstrap', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

// Register and define the settings
add_action('admin_init', 'wp_galleriffic_bootstrap_admin_init');

function wp_galleriffic_bootstrap_admin_init() {
    $option_name = 'wp_galleriffic_bootstrap_options';
    register_setting(
            'media', 'wp_galleriffic_bootstrap_options', 'wp_galleriffic_bootstrap_validate_options'
    );

    add_settings_section(
            'wp_galleriffic_bootstrap_main', __('Fotogalerij Instellingen', 'wp_galleriffic_bootstrap'), 'wp_galleriffic_bootstrap_section_text', 'media'
    );

    add_settings_field('size', __('Selecteer Afmeting', 'wp_galleriffic_bootstrap'), 'wp_galleriffic_bootstrap_field_select', 'media', 'wp_galleriffic_bootstrap_main', array(
        'options-name' => $option_name,
        'id' => 'size',
        'class' => '',
        'value' => array(
            'medium' => __('Medium', 'wp_galleriffic_bootstrap'),
            'large' => __('Large', 'wp_galleriffic_bootstrap'),
        ),
        'label' => __('Selecteer welke media afmeting voor de gallerie gebruikt moet worden.', 'wp_galleriffic_bootstrap'),
    ));
    add_settings_field('caption', __('Hoogte Caption in px', 'wp_galleriffic_bootstrap'), 'wp_galleriffic_bootstrap_field_int', 'media', 'wp_galleriffic_bootstrap_main');
}

// Draw the section header
function wp_galleriffic_bootstrap_section_text() {

    _e('<p><strong>Deze foto gallerie vervangt de default WP gallerie.<?strong></p>', 'wp_galleriffic_bootstrap');
}

/**
 * The callback function to display our selection dropdown
 * @param  Array $args An array of our arguments passed in the add_settings_field() function
 * @return HTML
 *
 */
function wp_galleriffic_bootstrap_field_select($args) {
    // Set the options-name value to a variable
    $name = $args['options-name'] . '[' . $args['id'] . ']';

    // Get the options from the database
    $options = get_option($args['options-name']);
    ?>

    <select name="<?php echo $name; ?>" id="<?php echo $args['id']; ?>" <?php if (!empty($args['class'])) echo 'class="' . $args['class'] . '" '; ?>>
        <?php foreach ($args['value'] as $key => $value) : ?>
            <option value="<?php esc_attr_e($key); ?>"<?php if (isset($options[$args['id']])) selected($key, $options[$args['id']], true); ?>><?php esc_attr_e($value); ?></option>
        <?php endforeach; ?>
    </select>
    <label for="<?php echo $args['id']; ?>" style=""><?php esc_attr_e($args['label']); ?></label>

    <?php
}

/**
 * The callback function to display the caption height
 * 
 * @return HTML
 * 
 */
function wp_galleriffic_bootstrap_field_int() {
    // get option ‘text_string’ value from the database
    $options = get_option('wp_galleriffic_bootstrap_options');
    $caption = $options['caption'];
// echo the field
   echo "<input id='caption' name='wp_galleriffic_bootstrap_options[caption]' type='number' class='small-text' value='$caption' />";
}

// Validate user input (we want text only)
function wp_galleriffic_bootstrap_validate_options($input) {
    $output[size] = 'medium';
    if ($input[size] === 'large') {
        $output[size] = 'large';
    }
    $output[caption] = absint( $input[caption] );
    return $output;
}
