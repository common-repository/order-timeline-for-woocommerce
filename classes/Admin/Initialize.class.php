<?php
namespace OTFW\Admin;

class Initialize
{
    public static $instance;

    public static function getInstance()
    {
        if ( !self::$instance instanceof self )
            self::$instance = new self();
        
        return self::$instance;
    }

    private function __construct()
    {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 10, 1 );
    }

    public function enqueue($currentScreen)
    {
        $this->styles($currentScreen);
        $this->scripts($currentScreen);
    }

    private function styles($currentScreen)
    {
        wp_enqueue_style( 'otfw' );

        if ( 'woocommerce_page_otfw-options' === $currentScreen )
        {
            wp_enqueue_style( 'otfw-admin' );
            wp_enqueue_style( 'otfw-icons' );
        }
    }

    private function scripts($currentScreen)
    {   
        if ( 'woocommerce_page_otfw-options' === $currentScreen )
        {
            wp_enqueue_script( 'otfw-admin' );
        }        
    }
}
