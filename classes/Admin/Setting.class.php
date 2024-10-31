<?php
namespace OTFW\Admin;

class Setting
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
        add_action( 'woocommerce_order_note_added', array( $this, 'addOrderStatus' ), 10, 2 );

		add_action( 'admin_menu', array( $this, 'otfwOptionsMenu' ) );
		add_action( 'admin_init', array( $this, 'otfwOptionsSections' ) );
		add_action( 'admin_init', array( $this, 'otfwOptionFields' ) );
    }

	public function otfwOptionsMenu() 
	{
		add_submenu_page(
			'woocommerce',
			__( 'Order Timeline Settings', 'order-timeline-for-woocommerce' ),
			__( 'Order Timeline', 'order-timeline-for-woocommerce' ),
			'manage_woocommerce',
			'otfw-options',
			array( $this, 'otfwOptionsRender' ),
			1
		);
	}

	public function otfwOptionsRender()
	{
		settings_errors();
	?>
        <div class="wrap joms-settings">
            <div class="joms-fields">
				<h1><?php echo esc_html__( 'Order Timeline for WooCommerce', 'order-timeline-for-woocommerce' ); ?></h1>

				<form method="POST" action="options.php">
					<?php 
					wp_nonce_field('update-options');
					
					settings_fields( 'otfw-options' ); //option group , should match with register_setting('otfw-options') 
					do_settings_sections( 'otfw-options' ); // setting page slug 'otfw-options'
					submit_button();
					?>
				</form>
			</div>
			<div class="joms-recommendations">
				<h2><?php echo esc_html__( 'Recommended Plugins', 'order-timeline-for-woocommerce' ); ?></h2>
				<a href="//wordpress.org/plugins/ultimate-coupon-for-woocommerce" target="_blank">
					<img src="<?php echo esc_url( OTFW_RESOURCES . '/images/ucfw-banner.png' ); ?>" alt="">
				</a>
			</div>
        </div>
	<?php
	}

	public function otfwOptionsSections()
	{
		add_settings_section( 'otfwOptions_section_general', __( 'General Settings', 'order-timeline-for-woocommerce' ), array(), 'otfw-options' ); //'otfw-options' is page slug
		add_settings_section( 'otfwOptions_section_icons', __( 'Status Icons', 'order-timeline-for-woocommerce' ), array(), 'otfw-options' ); //'otfw-options' is page slug
	}

	public function otfwOptionFields()
	{
		$fields = array(

			array(
				'label'   => esc_html__( 'Order Details', 'order-timeline-for-woocommerce' ),
				'id'      => 'otfwOptions_toggle',
				'type'    => 'toggle',
				'section' => 'otfwOptions_section_general',
				'description' => esc_html__('Enable/Disable Default Order Details.', 'order-timeline-for-woocommerce' ),
			),

			array(
				'label'   => esc_html__( 'Timeline Color', 'order-timeline-for-woocommerce' ),
				'id'      => 'otfwOptions_color',
				'type'    => 'color',
				'section' => 'otfwOptions_section_general',
				'description' => esc_html__('Select color for the timeline.', 'order-timeline-for-woocommerce' ),
			),

			array(
				'label'   => esc_html__( 'Background', 'order-timeline-for-woocommerce' ),
				'id'      => 'otfwOptions_background',
				'type'    => 'color',
				'section' => 'otfwOptions_section_general',
				'description' => esc_html__('Select background for the timeline.', 'order-timeline-for-woocommerce' ),
			),

			array(
				'label'   => esc_html__( 'Icon Color', 'order-timeline-for-woocommerce' ),
				'id'      => 'otfwOptions_icon_color',
				'type'    => 'color',
				'section' => 'otfwOptions_section_icons',
				'description' => esc_html__('Select icon color for the timeline.', 'order-timeline-for-woocommerce' ),
			)
		);

		foreach ( wc_get_order_statuses() as $orderStatus )
		{
			$statusId = str_replace(' ', '-', strtolower($orderStatus));
			$fields[] = array(
				'label'   => esc_html__( "{$orderStatus} Icon", 'order-timeline-for-woocommerce' ),
				'id'      => "otfwOptions_{$statusId}_icon",
				'type'    => 'icons',
				'section' => 'otfwOptions_section_icons',
				'description' => esc_html__("Choose icon for {$orderStatus} status", 'order-timeline-for-woocommerce' ),
			);
		}

		foreach ( $fields as $field )
		{
			add_settings_field(
				$field['id'], 
				$field['label'], 
				array( $this, 'otfwOptionFieldsGenerator' ), 
				'otfw-options', // page slug 
				$field['section'], 
				$field 
			);

			switch ( $field['type'] )
			{
				case 'toggle':
				case 'checkbox':
				case 'radio':
					register_setting( 'otfw-options', $field['id']);
					break;
				
				default:
					register_setting( 'otfw-options', $field['id'], array( 'sanitize_callback' => 'esc_attr' ) );
			}
		}

	}

	public function otfwOptionFieldsGenerator( $field )
	{
		$value = get_option( $field['id'] );

		switch ( $field['type'] )
		{
			case 'textarea':
				printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>',
					esc_attr( $field['id'] ),
					isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '',
					esc_html( $value )
				);
				break;

			case 'select':
				$options = $field['options'];

				echo '<select id="'. esc_attr( $field['id'] ) .'" name="'. esc_attr( $field['id'] ) .'">';
				foreach( $options as $option )
				{
					$selected = ($value === $option) ? 'selected' : '';
					printf( '<option value="%s" %s>%s</option>',
						esc_attr( $option ),
						esc_attr( $selected ),
						esc_html( $option )
					);
				}
				echo "</select>";
				break;

			case 'toggle':
				$checked = ( is_array($value) && in_array('on', $value) ) ? 'checked' : '';
				printf( '<div class="otfw_switch">
						<input type="checkbox" name="%s[]" id="%s" value="on" %s>
						<label for="%s"></label>
					</div>',
					esc_attr( $field['id'] ),
					esc_attr( $field['id'] ),
					esc_attr( $checked ),
					esc_attr( $field['id'] )
				);
				break;

			case 'checkbox':
				$options = $field['options'];

				foreach( $options as $option )
				{	
					$checked = ( is_array($value) && in_array($option, $value) ) ? 'checked' : '';

					printf( '<input type="checkbox" name="%s[]" value="%s" %s> %s <br>',
						esc_attr( $field['id'] ),
						esc_attr( $option ),
						esc_attr( $checked ),
						esc_html( $option )
					);
				}
				break;
			
			case 'radio':
				$options = $field['options'];

				foreach( $options as $option )
				{	
					$checked = '';
					if( is_array($value) && in_array($option, $value) )
					{
						$checked = 'checked';
					}

					printf( '<input type="radio" name="%s[]" value="%s" %s> %s <br>',
						esc_attr( $field['id'] ),
						esc_attr( $option ),
						esc_attr( $checked ),
						esc_html( $option )
					);
				}
				break;
			
			case 'icons':
				printf('<div id="%s" class="jomps-icons">
					<ul class="jomps-icons-selector">
						<li><span class="icon-checkmark"></span></li>
						<li><span class="icon-loop"></span></li>
						<li><span class="icon-stop"></span></li>
						<li><span class="icon-pause"></span></li>
						<li><span class="icon-cross"></span></li>
						<li><span class="icon-warning"></span></li>
						<li><span class="icon-star-full"></span></li>
						<li><span class="icon-clipboard"></span></li>
						<li><span class="icon-power-cord"></span></li>
						<li><span class="icon-ticket"></span></li>
						<li><span class="icon-cart"></span></li>
						<li><span class="icon-coin-dollar"></span></li>
						<li><span class="icon-compass"></span></li>
						<li><span class="icon-clock"></span></li>
						<li><span class="icon-hour-glass"></span></li>
						<li><span class="icon-spinner"></span></li>
						<li><span class="icon-lock"></span></li>
						<li><span class="icon-gift"></span></li>
						<li><span class="icon-fire"></span></li>
						<li><span class="icon-briefcase"></span></li>
						<li><span class="icon-airplane"></span></li>
						<li><span class="icon-shield"></span></li>
						<li><span class="icon-power"></span></li>
						<li><span class="icon-cloud-fill"></span></li>
						<li><span class="icon-cloud-download"></span></li>
						<li><span class="icon-cloud-upload"></span></li>
						<li><span class="icon-cloud-check"></span></li>
						<li><span class="icon-bookmarks"></span></li>
					</ul>
					<input type="hidden" name="%s" value="%s">
				</div>', esc_attr( $field['id'] ), esc_attr( $field['id'] ), esc_attr( $value ));
				break;
				
			default:
				printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s">',
					esc_attr( $field['id'] ),
					esc_attr( $field['type'] ),
					isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '',
					esc_attr( $value )
				);
		}

		if ( isset( $field['description'] ) )
		{
			if ( $desc = $field['description'] )
				printf( '<p class="description">%s</p>', esc_html( $desc ) );
		}
	}


    /**
     * Add Order status with Note
     *
     */
    public function addOrderStatus( $comment_id, $order ) 
    {
        update_comment_meta( intval($comment_id), 'otfw_order_status', $order->get_status() );
    }

    /**
     * Process and save settings
     *
     * @return mixed array|boolean
     */
    public static function processSettings( $settings )
	{}
}
