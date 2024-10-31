<?php
/**
 * OTFW class
 *
 * @class OTFW The class that holds the entire plugin
 */
final class OTFW
{
    public static $instance;

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * Singleton Pattern
     *
     * @return object
     */
    public static function getInstance()
    {
        if ( !self::$instance instanceof self )
            self::$instance = new self();

        return self::$instance;
    }

    /**
     * Constructor for the OTFW class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct()
    {
        $this->defineConstants();

        register_activation_hook( __FILE__,   array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded',   array( $this, 'bootSystem' ) );
        add_action( 'plugins_loaded',   array( $this, 'run' ) );
        add_action( 'activated_plugin', array($this, 'activationRedirect') );

        add_filter( 'plugin_action_links_' . plugin_basename(__DIR__) . '/order-timeline-for-woocommerce.php', array( $this, 'settingLink' ) );
        add_filter( 'plugin_row_meta', array( $this, 'helpLinks' ), 10, 2);
    }

    /**
     * Define the constants
     * @return void
     */
    public function defineConstants()
    {
        define( 'OTFW_VERSION',       $this->version );
        define( 'OTFW_FILE',          __FILE__ );
        define( 'OTFW_PATH',          dirname( OTFW_FILE ) );
        define( 'OTFW_CLASSES',       OTFW_PATH . '/classes' );
        define( 'OTFW_ADMIN_CLASSES', OTFW_CLASSES . '/Admin' );
        define( 'OTFW_FRONT_CLASSES', OTFW_CLASSES . '/Front' );
        define( 'OTFW_URL',           plugins_url( '', OTFW_FILE ) );
        define( 'OTFW_RESOURCES',     OTFW_URL . '/resources' );
    }

    /**
     * Boots System
     */
    public function bootSystem()
    {
        if ( !class_exists('woocommerce') )
        {
            add_action( 'admin_notices',         array( $this, 'requiredWoocommerce' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'noticeScripts' ) );
            return;
        }
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function run()
    {
        if ( !class_exists('woocommerce') )
            return;
            
        $this->includes();
        
        add_action( 'init', array( $this, 'init_classes' ) );
        add_action( 'init', array( $this, 'localization_setup' ) );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes()
    {
        include_once OTFW_ADMIN_CLASSES . '/Initialize.class.php';
        include_once OTFW_ADMIN_CLASSES . '/Setting.class.php';

        include_once OTFW_FRONT_CLASSES . '/Initialize.class.php';
        include_once OTFW_FRONT_CLASSES . '/Timeline.class.php';

        include_once OTFW_CLASSES       . '/Resources.class.php';
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {
        if ( $this->is_request( 'admin' ) )
        {
            \OTFW\Admin\Initialize::getInstance();
            \OTFW\Admin\Setting::getInstance();
        }

        if ( $this->is_request( 'front' ) )
        {
            \OTFW\Front\Initialize::getInstance();
            \OTFW\Front\Timeline::getInstance();
        }

        \OTFW\Resources::getInstance();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain( 'order-timeline-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate()
    {
        $installed = get_option( 'otfw_installed' );

        if ( !$installed )
            update_option( 'otfw_installed', time() );

        update_option( 'otfw_version', OTFW_VERSION );
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {}

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or front.
     *
     * @return bool
     */
    private function is_request( $type )
    {
        switch ( $type )
        {
            case 'admin' :
                return is_admin();

            case 'ajax' :
                return defined( 'DOING_AJAX' );

            case 'rest' :
                return defined( 'REST_REQUEST' );

            case 'cron' :
                return defined( 'DOING_CRON' );

            case 'front' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    /**
     * Enqueue scripts for notice
     */
    public function noticeScripts()
    {
        wp_enqueue_style( 'otfw-admin-notice', OTFW_RESOURCES . '/css/admin-notice.css', false, filemtime( OTFW_PATH . '/resources/css/admin-notice.css' ) );
    }

    function requiredWoocommerce()
    {
        if ( !class_exists('woocommerce') )
        {
            echo '<div class="otfw-plugin-required-notice notice notice-warning">
                <div class="otfw-admin-notice-content">
                <h2>OTFW Required dependency.</h2>
                <p>Please ensure you have the <strong>WooCommerce</strong> plugin installed and activated.</p>
                </div>
            </div>';
        }
    }

    /**
     * Redirect to plugin page on activation
     *
     */
    public function activationRedirect( $plugin ) 
    {
        if( plugin_basename(__DIR__) . '/order-timeline-for-woocommerce.php' == $plugin && class_exists('woocommerce') )
            exit( wp_redirect( admin_url( '/admin.php?page=otfw-options' ) ) );
    }

    /**
     * Setting page link in plugin list
     *
     */
    public function settingLink( $links ) 
    {
	    $settingLink = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( '/admin.php?page=otfw-options' ) ), esc_html__( 'Settings', 'order-timeline-for-woocommerce' ) );
	    $links[] = $settingLink;
	    $links[] = $premiumLink;

	    return $links;
	}

    /**
     * Plugin row links
     *
     */
    public function helpLinks( $links, $plugin )
    {
        if ( plugin_basename(__DIR__) . '/order-timeline-for-woocommerce.php' != $plugin )
            return $links;
        
        $supportLink = sprintf( '<a href="%s">%s</a>', esc_url( '//forum.jompha.com' ), esc_html__( 'Community Support', 'order-timeline-for-woocommerce' ) );

        $links[] = $supportLink;
    
        return $links;
    }
}
