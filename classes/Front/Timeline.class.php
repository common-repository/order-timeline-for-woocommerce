<?php
namespace OTFW\Front;

class Timeline
{
    public static $instance;

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
     * Constructor
     *
     * @return void
     */
    private function __construct()
    {
        add_action( 'wp_head',                array( $this, 'styles' ) , 100);
        add_action( 'woocommerce_view_order', array( $this, 'orderDetails' ) , 1);
    }

    public function styles()
    {
        $optionsColor       = get_option('otfwOptions_color', '#4d4d4d');
        $optionsIconColor   = get_option('otfwOptions_icon_color', '#ffffff');
        $optionsBackground  = get_option('otfwOptions_background', '#f5f5f5');
        $orderUpdatesToggle = get_option('otfwOptions_toggle', false);

        if ( !$orderUpdatesToggle )
            $orderUpdatesToggle = array('off');
        
        $wooNotes = ('on' == $orderUpdatesToggle[0]) ? 'block' : 'none';
    ?>
        <style>
            .otfw_timeline {
                background-color: <?php echo esc_html($optionsBackground); ?>;
            }
            .otfw_timeline .timeline__header .process .xd,
            .line .content > div,        
            .line::before {
                background-color: <?php echo esc_html($optionsColor); ?>;
            }
            .woocommerce-OrderUpdates.commentlist.notes {
                display: <?php echo esc_html($wooNotes); ?>;
            }
            .otfw_timeline .line .content div > span {
                color: <?php echo esc_html( $optionsIconColor ); ?>;
            }
        </style>
      <?php
    }

    public function orderDetails($orderId)
    {
        $orderInfo = wc_get_order($orderId);

        $orderNotes = wc_get_order_notes(array(
            'order_id' => $orderId,
            'type'     => array(
                'customer',
                'internal'
            ),
            'order_by' => 'date_created',
        ));
    ?>
    <div class="otfw_timeline">
        <div class="line">
            <div class="line-content">
                <?php foreach ($orderNotes as $note) { ?>
                <div class="content">
                    <?php
                    $orderTimestamp = $note->date_created->getTimestamp();
                    $orderStatus    = get_comment_meta( $note->id, 'otfw_order_status', true );

                    if ( $orderStatus )
                        $orderStatus = str_replace(' ', '-', strtolower($orderStatus));

                    $orderIcon = $orderStatus ? get_option("otfwOptions_{$orderStatus}_icon", 'icon-hour-glass' ) : '';
                    ?>
                    <div><span class="<?php echo esc_html( $orderIcon ); ?>"></span></div>
                    <h4><?php echo date( 'd M, Y | g:i A', absint($orderTimestamp) ); ?></h4>
                    <h1><?php echo esc_html( ucwords($orderStatus) ); ?></h1>
                    <p><?php echo esc_html( $note->content ); ?></p>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
    }
}
