<?php
namespace OTFW\Front;

class Initialize
{
    public static $instance;
    public $wcUserDashboard;

    public static function getInstance()
    {
        if ( !self::$instance instanceof self )
            self::$instance = new self();
        
        return self::$instance;
    }

    private function __construct()
    {   
        $this->wcUserDashboard = get_option('woocommerce_myaccount_page_id'); // another way wc_get_page_id( 'myaccount' )
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
        add_action( 'wp_head',            array( $this, 'inlineStyles' ) );
    }
   
    public function enqueue()
    {   
        if ( get_the_ID() === intval( $this->wcUserDashboard ) )
        {
            $this->styles();
            $this->scripts();
        }
    }

    private function styles()
    {   
        wp_enqueue_style( 'otfw-front' );
        wp_enqueue_style( 'otfw-icons' );
    }

    private function scripts()
    {   
        wp_enqueue_script( 'otfw-front' );
    }

    /**
     * Inline styles
     *
     * @return array
     */
    public function inlineStyles(){}
}
