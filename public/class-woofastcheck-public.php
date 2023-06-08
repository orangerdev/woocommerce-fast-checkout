<?php

namespace Woofastcheck;

use function PHPSTORM_META\map;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Woofastcheck
 * @subpackage Woofastcheck/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woofastcheck
 * @subpackage Woofastcheck/public
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Front
{

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Shortcode name
   * 
   * @since   1.0.0
   * @access  private
   * @var     string    $shortcode    The shortcode name
   */
  private $shortcode = "woofastcheck";

  /**
   * Check if shortcode is used in current page
   * 
   * @since   1.0.0
   * @access  private
   * @var     boolean    $has_shortcode    True if shortcode is used in current page
   */
  private $has_shortcode = false;

  /**
   * Selected products
   * 
   * @since   1.0.0
   * @access  public
   * @var     array    $products    Array of selected products
   */
  public $products = array();

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct($plugin_name, $version)
  {

    $this->plugin_name = $plugin_name;
    $this->version = $version;
  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles()
  {
    if ($this->has_shortcode) :

      wp_register_style(
        'fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css',
        [],
        '4.7.0',
        'all'
      );

      wp_enqueue_style(
        $this->plugin_name,
        plugin_dir_url(__FILE__) . 'css/woofastcheck-public.css',
        array(
          'select2',
          'fontawesome'
        ),
        $this->version,
        'all'
      );
    endif;
  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts()
  {
    if ($this->has_shortcode) :
      wp_enqueue_script(
        $this->plugin_name,
        plugin_dir_url(__FILE__) . 'js/woofastcheck-public.js',
        array(
          'jquery',
          'selectWoo',
          'wc-checkout',
          'wc-country-select'
        ),
        $this->version,
        true
      );

      wp_localize_script(
        $this->plugin_name,
        'woofastcheck',
        array(
          'addtocart' => [
            'url' => add_query_arg(
              array(
                'wc-ajax' => 'add_to_cart',
              ),
              home_url('/')
            ),
          ],
          'nonce' => wp_create_nonce('woofastcheck-nonce'),
        )
      );
    endif;
  }

  /**
   * Add class to body if shortcode is used in current page
   * @uses    body_class, priority 10, 1
   * @param   array  $classes  Body classes
   * @return  array            Modified body classes
   */
  public function add_body_class(array $classes)
  {
    if ($this->has_shortcode) :
      $classes[] = 'woofastcheck woocommerce woocommerce-page woocommerce-checkout';
    endif;

    return $classes;
  }

  /**
   * Register shortcode
   * @uses    init, priority 10
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @return  void
   */
  public function register_shortcode()
  {
    add_shortcode(
      $this->shortcode,
      array($this, 'display_shortcode')
    );
  }

  /**
   * Remove other items in cart if only_this_product is true
   * @uses    woocommerce_ajax_added_to_cart, priority 10
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @return  void
   */
  public function remove_other_items($product_id)
  {
    $only_this_product = carbon_get_post_meta($product_id, "only_one_in_cart");

    if (!$only_this_product)
      return;

    \WC()->cart->empty_cart();
    \WC()->cart->add_to_cart($product_id, 1);
  }

  /**
   * Add selected product to cart
   * @uses    template_redirect, priority 10
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @return  void
   */
  public function add_to_cart()
  {
    global $post;
    if (is_page()) :
      if (has_shortcode($post->post_content, $this->shortcode)) :

        \WC()->cart->empty_cart();

        $this->products = carbon_get_the_post_meta('products');

        if (is_array($this->products) && count($this->products) > 0) :

          $this->has_shortcode = true;

          \WC()->cart->add_to_cart($this->products[0]['id'], 1);

        endif;

      endif;
    endif;
  }

  /**
   * Display shortcode content, registerd by woofastcheck
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @param   string|array  $atts     Shortcode attributes
   * @param   string        $content  Shortcode content
   * @return  string                  Modified shortcode content
   */
  public function display_shortcode($atts, string $content)
  {

    if (
      is_array($this->products) &&
      count($this->products) > 0
    ) :
      $products = $this->products;

      ob_start();

      require_once WOOFASTCHECK_PLUGIN_DIR . 'public/partials/shortcode/display.php';

      $content .= ob_get_clean();

    endif;

    return $content;
  }

  /**
   * Modify checkout fields
   * @uses    woocommerce_checkout_fields, priority 10
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @param   array  $fields  Checkout fields
   * @return  array           Modified checkout fields
   */
  public function modify_checkout_fields(array $fields)
  {

    $fields['billing']['selected_payment_gateway'] = array(
      'type' => 'hidden',
      'default' => '',
    );

    return $fields;
  }

  /**
   * Modify template part location
   * @uses    wc_get_template_part, priority 10, 3
   * @param   string  $template  Template part location
   * @param   string  $slug      Template part slug
   * @param   string  $name      Template part name
   * @return  string             Modified template part location
   * @author  Ridwan Arifandi
   * @since   1.0.0
   */
  public function get_template_part($template, $template_name)
  {

    if ('checkout/payment.php' === $template_name) :
      $template = WOOFASTCHECK_PLUGIN_DIR . 'public/partials/checkout/payment.php';
    endif;

    return $template;
  }

  /**
   * Add convenience fee based on selected payment gateway
   * @uses    woocommerce_cart_calculate_fees, priority 999
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @return  void
   */
  public function add_convenience_fee($cart)
  {
    $chosen_payment_id = \WC()->session->get('chosen_payment_method');

    if (empty($chosen_payment_id))
      return;

    $payments = carbon_get_theme_option('payment');

    if (is_array($payments) && count($payments) > 0) :
      foreach ((array) $payments as $payment) :
        if ($payment['channel'] === $chosen_payment_id) :
          $fee = floatval($payment['fee']);

          if ("percent" === $payment['fee_type']) :
            $fee = $cart->subtotal * $fee / 100;
          endif;

          $cart->add_fee('Transaction Fee', $fee, true);
          break;
        endif;
      endforeach;
    endif;
  }
}
