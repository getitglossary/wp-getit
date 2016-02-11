<?php

/**
 * Plugin name: WP GETIT
 * Plugin URI: https://github.com/getitglossary/wp-getit
 * Description: WordPress plugin to show definitions from the Get It Glosary
 * Version: 1.0
 * Author: Robin Layfield
 * Author URI: http://minervation.com
 */

define( 'GETIT_URL',     plugin_dir_url( __FILE__ )  );
define( 'GETIT_PATH',    plugin_dir_path( __FILE__ ) );
define( 'GETIT_VERSION', '1.0' );

function getit_activate()
{
    // Set defaults
    $getit_definition_css        = 'getit_definition_css';
    $getit_accent_color     = 'getit_accent_color';
    $getit_link_style       = 'getit_link_style';
    $getit_not_found        = 'getit_not_found';

    update_option( $getit_definition_css, '
 #getit_subtitle {
  font-size: small;
  margin-top: -20px;
  margin-bottom: 10px;
}

#getit_definition {
    border: 1px solid grey;
    background: white;
    padding: 10px;
}

#getit_definition strong {
  color: rebeccapurple;
}

#getit_terms {
    width: 100%;
    margin-bottom: 10px;
    font-size: 18px !important;
}

#getit_subtitle {
margin-top: -10px; display:block;
}' );
    update_option( $getit_accent_color, '#ff1493' );
    update_option( $getit_link_style, 'border-bottom: 2px dashed #ff1493' );
    update_option( $getit_not_found, 'The term you are looking for has not been found' );

}
register_activation_hook( __FILE__, 'getit_activate' );

function getit_init()
{
}
add_action( 'plugins_loaded', 'getit_init' );

function getit_scripts()
{
    wp_enqueue_script('js-toolpop', plugins_url( '/assets/js/jquery.toolpop.js', __FILE__ ), array('jquery'));
    wp_enqueue_style('css-toolpop', plugins_url( '/assets/css/jquery.toolpop.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'getit_scripts' );

function getit_css() {
    $getit_definition_css          = 'getit_definition_css';

    // Read in existing option value from the database
    $definition_css         = get_option( $getit_definition_css );
    $output = "\r\n<style>
/* GET IT GLOSSARY */
" . $definition_css . "
</style>";

    echo $output;
}
add_action( 'wp_head', 'getit_css' );

function getit_admin_settings()
{
    add_options_page( 'Get it Glossary', 'Get it Glossary', 'manage_options', 'get_it_glossary',  'getit_plugin_options' );
}
add_action( 'admin_menu', 'getit_admin_settings' );

function getit_plugin_options()
{
    // variables for the field and option names
    $getit_definition_css          = 'getit_definition_css';
    $getit_accent_color     = 'getit_accent_color';
    $getit_link_style       = 'getit_link_style';
    $getit_not_found        = 'getit_not_found';

    $hidden_field_name      = 'getit_submit_hidden';

    // Read in existing option value from database
    $definition_css              = get_option( $getit_definition_css );
    $accent_color           = get_option( $getit_accent_color );
    $link_style             = get_option( $getit_link_style );
    $not_found              = get_option( $getit_not_found );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ):

        // Read their posted value
        $definition_css          = $_POST[ $getit_definition_css ];
        $accent_color       = $_POST[ $getit_accent_color ];
        $link_style         = $_POST[ $getit_link_style ];
        $not_found          = $_POST[ $getit_not_found ];

        // Save the posted value in the database
        update_option( $getit_definition_css, $definition_css );
        update_option( $getit_accent_color, $accent_color );
        update_option( $getit_link_style, $link_style );
        update_option( $getit_not_found, $not_found );

        // Put an settings updated message on the screen
       echo '<div class="updated"><p>';
       _e( 'Updated!', 'getitglossary' );
       echo '</p></div>';
    endif;

   // Now display the settings editing screen

?>
<div class="wrap">
<form name="form1" method="post" action="">
<?php echo "<h1>" . __( 'Get it Glossary settings', 'getitglossary' ) . "</h1>"; ?>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
<hr/>
<h2>Popup settings</h2>

<p><?php _e("Accent colour", 'getitglossary' ); ?><br/>
<input type="text" name="<?php echo $getit_accent_color; ?>" value="<?php echo $accent_color; ?>" size="20">
</p>

<?php echo "<h3>" . __( 'Link style', 'getitglossary' ) . "</h3>"; ?>
<textarea rows="3" cols="60" name="<?php echo $getit_link_style; ?>"><?php echo $link_style; ?></textarea>
</p>

<?php echo "<h3>" . __( 'Definition not found message', 'getitglossary' ) . "</h3>"; ?>
<textarea rows="2" cols="60" name="<?php echo $getit_not_found; ?>"><?php echo $not_found; ?></textarea>
</p>
<hr/>
<h2>Sidebar settings</h2>
<?php echo "<h3>" . __( 'Sidebar definition CSS', 'getitglossary' ) . "</h3>"; ?>
<textarea rows="25" cols="60" name="<?php echo $getit_definition_css; ?>"><?php echo $definition_css; ?></textarea>
</p>

<hr/>
<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
</div>

<?php
}

function getit_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array (
		'term' => ''
	), $atts ) );

    $accent_color       = get_option( 'getit_accent_color' );
    $definition_css          = get_option( 'getit_definition_css' );
    $link_style         = get_option( 'getit_link_style' );
    $not_found          = get_option( 'getit_not_found' );

	$output = '';

	if( empty( $term ) ):
	    $term = $content;
    endif;

    if( empty( $content ) ):
        $content = $term;
    endif;

	$link = '<a href="http://getitglossary.org/term/' . strtolower( str_replace( ' ', '+', $term ) ) . '" target="_getit">View the full definition at GetitGlossary.org &rarr;</a>';

    // retrieve definitions
    $transient_name = 'getit_term_' . $term;
    $definition = get_transient( $transient_name );
    if( false === $definition ):
      $definition = file_get_contents( 'http://getitglossary.org/v1/terms/' . strtolower( str_replace( ' ', '+', $term ) ) );
      if( 2 == strlen( $definition ) ):
        $definition = json_encode( array( array( 'term' => $term, 'definition' => $not_found ) ) );
        $link = '<a href="http://getitglossary.org/terms/" target="_getit">More definitions at GetitGlossary.org &rarr;</a>';
      endif;
      $expiration_time = 60*60*24*7;//in second
      set_transient( $transient_name, $definition, $expiration_time );
    endif;

    $definition = json_decode( $definition )[0];
	$pattern = '/(\[)([\w\s,\'+-]*)(\|)([\w\s,\'+-]*)(\])/';
	$pattern_alt = '/(\[)([\w\s,\']*)(\])/';

	// replace with links
	// $replacement = '<a href="http://getitglossary.org/term/$4" target="_getit">$2</a>';
	// $replacement_alt = '<a href="http://getitglossary.org/term/$2" target="_getit">$2</a>';

	// replace with bold terms
	$replacement = '<strong>$2</strong>';
	$replacement_alt = '<strong>$2</strong>';

	$definition->definition = preg_replace( $pattern, $replacement, $definition->definition);
	$definition->definition = preg_replace( $pattern_alt, $replacement_alt, $definition->definition);

    // add a period to the definition if one is not already present
    $definition->definition .= ( '.' !== substr( $definition->definition, -1 ) ) ? '.' : '';

    $output = '<a style="color: ' . $accent_color . '; ' . $link_style . '" href="#pop-' . $term . '" data-term="' . $term . '" data-definition=\'' . str_replace ( "\'", "&lsquo;", addslashes( $definition->definition) ) . '\' data-getit_link=\'' . $link . '\' title="Click to view the GetitGlossary.org definition of this term" >' . $content . '</a>';

    return $output;
}
add_shortcode( 'getit', 'getit_shortcode' );


// Creating the widget
class getit_widget extends WP_Widget {

  function __construct() {
  parent::__construct(
  // Base ID of your widget
  'getit_widget',

  // Widget name will appear in UI
  __('Get it Glossary', 'getitglossary'),

  // Widget description
  array( 'description' => __( 'Lookup definitions from the Getit Glossary', 'getitglossary' ), )
  );
}

// Creating widget front-end
// This is where the action happens
public function widget( $args, $instance ) {
    // variables for the field and option names
    $getit_accent_color     = 'getit_accent_color';
    $getit_link_style       = 'getit_link_style';

    // Read in existing option value from database
    $accent_color           = get_option( $getit_accent_color );
    $link_style             = get_option( $getit_link_style );

  $title = apply_filters( 'widget_title', $instance['title'] );

  // retrieve terms + definitions and store in a transient
  $transient_name = 'getit_all_terms';
  $terms = get_transient( $transient_name );
  if( false === $terms ):
    $terms = file_get_contents( 'http://getitglossary.org/v1/terms' );

    // post-process terms
    $definitions = json_decode( $terms );

    $pattern = '/(\[)([\w\s,\'+-]*)(\|)([\w\s,\'+-]*)(\])/';
	$pattern_alt = '/(\[)([\w\s,\']*)(\])/';
    $replacement = '<strong>$2</strong>';
    $replacement_alt = '<strong>$2</strong>';

    foreach( $definitions as $definition ):

    	$definition->definition = preg_replace( $pattern, $replacement, $definition->definition);
    	$definition->definition = preg_replace( $pattern_alt, $replacement_alt, $definition->definition);
        $definition->definition = str_replace ( "\'", "&lsquo;", addslashes( $definition->definition) );

        // add a period to the definition if one is not already present
        $definition->definition .= ( '.' !== substr( $definition->definition, -1 ) ) ? '.' : '';

    endforeach;

    // re-encode for storing in the database
    $terms = json_encode( $definitions );

    $expiration_time = 60*60*24*7; // 1 day
    set_transient( $transient_name, $terms, $expiration_time );
  endif;

  // before and after widget arguments are defined by themes
  echo $args['before_widget'];

  if ( ! empty( $title ) )
    echo $args['before_title'] . $title . $args['after_title'];

    echo "<p id=\"getit_subtitle\">from the <a href=\"http://getitglossary.org/\" target=\"_getit\">Get it Glossary &rarr;</a></p>";

?>
<!--
<input id="getit_terms" class="awesomplete" list="mylist" />
<datalist id="mylist">
	<?php
    	foreach( json_decode( $terms ) as $term ):
        echo "<option value=\"$term->definition\">$term->term</option>\r\n";
    endforeach;
    ?>
</datalist>
-->
<?php


  // display drop down of Get it terms
    echo "<select id=\"getit_terms\">\r\n
    <option val=\"\">Select a term</option>";
    foreach( json_decode( $terms ) as $term ):
        echo "<option value=\"$term->definition\">$term->term</option>\r\n";
    endforeach;
    echo "</select>";

    // inline style to highlight definition terms
    echo "<div id=\"getit_definition\"><h2>Definition</h2>
    <h2>[def-uh-nish-uh n]</h2>
    <p><em>&ldquo;the act of defining, or of making something definite, distinct, or clear:
We need a better definition of her responsibilities.&rdquo;</em></p>";

  // lookup terms & definitions from Getit
    echo $args['after_widget'];
  }

// Widget Backend
public function form( $instance ) {
  if ( isset( $instance[ 'title' ] ) ) {
    $title = $instance[ 'title' ];
  } else {
    $title = __( 'New title', 'getitglossary' );
  }

  // Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
    <?php
  }

  // Updating widget replacing old instances with new
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags($new_instance['title'] ) : '';

    return $instance;
  }
} // Class getit_widget ends here

// Register and load the widget
function getit_load_widget() {
    register_widget( 'getit_widget' );
}
add_action( 'widgets_init', 'getit_load_widget' );
