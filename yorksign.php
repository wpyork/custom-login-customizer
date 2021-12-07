<?php
/*
* Plugin Name   : Custom Login Customizer
* Plugin URI    : https://github.com/wpyork/custom-login-customizer/
* Description   : Wordpress Custom login page by WPyork
* Version       : 1.0.0
* Author        : wpyork
* Author URI    : https://wpyork.com/
* License       : GPLv2 or later
* Domain Path   : /languages
* Text Domain   : yorksign
* @package        LoginCustomizer
* @author Coders Time <wpyork143@gmail.com>
*/

if(!defined('ABSPATH')) exit; // Exit if accessed directly

class YorkCustomSignin {
    /**
     * Construct method 
     * Load immediate code
     * @version 1.0.0
     * @author WP York
     * */

    public function __construct()
    {
        add_action( 'login_enqueue_scripts', array( $this, 'gen_login_logo' ) );
        add_action( 'login_head', array( $this,'gen_login_head' ) );
        add_filter( 'login_headerurl', array( $this,'gen_login_logo_url' ) );
        add_filter( 'login_headertext', array( $this,'gen_login_logo_url_title' ) );        
        add_filter( 'wp_login_errors', array( $this, 'gen_registered_success_message' ), 10, 2 );
        add_filter( 'login_title', array( $this, 'custom_login_title' ), 99 );
        add_filter( 'admin_footer_text', array( $this, 'remove_footer_admin' ));  
        add_action( 'admin_print_scripts-user-new.php', array( $this,'add_description_jquery' ) );   
        add_filter( 'plugin_action_links_' . WP_WCSM_BASENAME, array( $this, 'action_links' ) ); 
        add_action( 'plugins_loaded', array( $this, 'localization_setup' ) ); /*Localize our plugin*/

        /*customizer*/
        add_action( 'customize_register', array( $this,'customizer_login_settings' ) );  
    }

    /**
     * Customizer defination
     * 
     * */
    public function customizer_login_settings( $wp_customize ) 
    {
        /*Customize logo panel*/
        $wp_customize->add_panel( 'ysign_options_panel', array(
            'priority'       => 100,
            'capability'     => 'edit_theme_options',
            'theme_supports' => '',
            'title'          => esc_html__( 'Custom Login Settings', 'yorksign' ),
        ) );

        /*Add Sections for Post Settings.*/
        $wp_customize->add_section( 'york_sign_logo', array(
            'title'    => esc_html__( 'Login Logo', 'yorksign' ),
            'priority' => 10,
            'panel'    => 'ysign_options_panel',
        ) );

        /*Get Default Settings.*/
        $default = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ) , 'full' );;
        $wp_customize->add_setting( 'yorksign[logo][url]', array(
            'default'           => $default[0], /*Add Default Image URL */
            'type'              => 'option',
            'sanitize_callback' => 'esc_url_raw',
        ));
     
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'yorksign_logo', array(
            'label' => esc_html__( 'Upload Login Panel Logo', 'yorksign' ),
            'priority' => 40,
            'section' => 'york_sign_logo',
            'settings' => 'yorksign[logo][url]',
        )));

        /*Customize login panel background*/
        $wp_customize->add_section( 'york_sign_bg', array(
            'title'    => esc_html__( 'Login Background', 'yorksign' ),
            'priority' => 10,
            'panel'    => 'ysign_options_panel',
        ) );

        /*Get Default Settings.*/
        $wp_customize->add_setting( 'yorksign[bg][url]', array(
            'default'           => '', /*Add Default Image URL */
            'type'              => 'option',
            'sanitize_callback' => 'esc_url_raw',
        ));
     
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'yorksign_bg', array(
            'label' => esc_html__( 'Upload Login Background', 'yorksign' ),
            'priority' => 10,
            'section' => 'york_sign_bg',
            'settings' => 'yorksign[bg][url]',
        )));

    }

    /**
     * Load jquery
     * 
    */
    public function add_description_jquery ( ) 
    {
        $asset_file_link = plugins_url( '/assets/', __FILE__ );
        $folder_path= __DIR__ .'/assets/';

        wp_enqueue_script( 'description-add', $asset_file_link . 'js/description-add.js',['jquery'],filemtime($folder_path.'js/description-add.js'), true );
    }

    /**
     * footer credit 
     * */

    public function remove_footer_admin ( ) 
    {
        echo sprintf( '<span id="footer-thankyou"> %s <a href="%s" target="_blank"> %s </a> </span>', esc_html__('Developed by', 'yorksign'), esc_html__('https://wpyork.com/','yorksign'), esc_html__('WP York','yorksign'));
    }

    /**
     * Change wordpress login page title content
    */
    public function custom_login_title ( $origtitle ) 
    {
        return 'Login Page - ' . get_bloginfo('name');
    }

    /**
     * show registered message
     * */
    public function gen_registered_success_message( $errors, $redirect_to )
    {
       if( isset( $errors->errors['registered'] ) )
       {
         $tmp = $errors->errors;   

         $old = __('Registration complete. Please check your email.');
         $new = 'Registration successfully complete.';

         foreach( $tmp['registered'] as $index => $msg )
         {
           if( $msg === $old )
               $tmp['registered'][$index] = $new;        
         }
         $errors->errors = $tmp;
         unset( $tmp );
       }  
       return $errors;
    }

    /**
     * login logo load
     * */
    public function gen_login_logo() 
    {    
        $asset_file_link = plugins_url( '/assets/', __FILE__ );
        $folder_path= __DIR__ .'/assets/';
        wp_enqueue_style( 'custom-login', $asset_file_link . 'css/style-login.css', [], filemtime($folder_path.'css/style-login.css') );

        $custom_logo = get_option('yorksign');
        $logo_url = ( strlen($custom_logo['logo']['url']) > 10 ) ? $custom_logo['logo']['url'] : wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ) , 'full' )[0];
        $custom_logo_css = "#login h1 a, .login h1 a {background-image: url('".$logo_url."');max-height: 100px;max-width: 380px;background-size: 100% 100%;background-repeat: no-repeat;padding-top: 0;padding-bottom: 0;margin-bottom: 20px;}";
            wp_add_inline_style( 'custom-login', $custom_logo_css );    

        if ( strlen($custom_logo['bg']['url']) > 10 ) {
            $custom_css = "body.login{background-image: url('".$custom_logo['bg']['url']."');background-repeat: no-repeat;background-size: auto;background-position: center;}";
            wp_add_inline_style( 'custom-login', $custom_css );
        }

    }

    /**
     * login head
     * */
    public function gen_login_head()
    {
        add_filter( 'gettext', array( $this,'gen_gettext' ), 10, 3 );
    }

    /**
     * translate text 
     * */
    public function gen_gettext( $translated_text, $text_to_translate, $textdomain )
    {
        if ( 'Username or Email Address' == $text_to_translate ) {
            $translated_text = __( 'Username or Email', 'login-form' );
        } elseif ( 'Password' == $text_to_translate ) {
            $translated_text = __( 'Your Password', 'login-form' );
        }
        return $translated_text;
    }

    /**
     * return home url*/
    public function gen_login_logo_url() 
    {
        return home_url();
    }

    /**
     * get blog info
    */

    public function gen_login_logo_url_title() 
    {
        return get_bloginfo('name');
    }

    /**
     * Stylesheet
     * return null
     * */

    public function gen_login_stylesheet() 
    {
        $asset_file_link = plugins_url( '/assets/', __FILE__ );
        $folder_path= __DIR__ .'/assets/';

        wp_enqueue_style( 'custom-login', $asset_file_link . 'css/style-login.css', [], filemtime($folder_path.'css/style-login.css') );
        // wp_enqueue_script( 'custom-login', $asset_file_link . 'js/style-login.js',['jquery'],filemtime($folder_path.'js/style-login.js'), true );
        // wp_enqueue_script( 'description-add', $asset_file_link . 'js/description-add.js',['jquery'],filemtime($folder_path.'js/description-add.js'), true );
    }

    /**
     * Show action links on the plugin screen
     *
     * @param mixed $links
     * @return array
     */
    public function action_links( $links ) 
    {
        return array_merge(
        [
            '<a href="' . admin_url( 'wp-admin/customize.php?return=%2Fwp-admin%2Fplugins.php' ) . '">' . __( 'Settings', 'yorksign' ) . '</a>',
            '<a href="' . esc_url( 'https://wordpress.org/support/plugin/custom-login-customizer/reviews/#new-post' ) . '">' . __( 'Review', 'yorksign' ) . '</a>',
            '<a href="' . esc_url( 'https://wordpress.org/support/plugin/custom-login-customizer/' ) . '">' . __( 'Support', 'yorksign' ) . '</a>'
        ], $links );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() 
    {
        load_plugin_textdomain( 'wcsm', false, dirname( WP_WCSM_BASENAME ) . '/languages/' );
    }

}

new YorkCustomSignin();
