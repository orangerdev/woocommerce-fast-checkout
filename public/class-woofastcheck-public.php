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
   * Register font family via HTML code
   * @uses    wp_head, priority 10, 1
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @return  void
   */
  public function register_font_family()
  {

    if ($this->has_shortcode) :
?>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@500;600;700&family=Manrope:wght@500;600;700&display=swap" rel="stylesheet">
<?php
    endif;
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

          \WC()->cart->add_to_cart($this->products[0]['id'], 1);

        endif;

        $this->has_shortcode = true;

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
   * Get post data from update review order and checkout process
   * @uses    woofastcheck/checkout/postdata, priority 10, 1
   * @author  Ridwan Arifandi;
   * @since   1.0.0
   * @param   array  $post_data  Post data
   * @return  array              Modified post data
   */
  public function get_post_data($post = array())
  {
    $post_data = array();

    if (isset($post['post_data'])) :
      wp_parse_str($post['post_data'], $post_data);
    else :
      $post_data = $_POST;
    endif;

    return $post_data;
  }

  /**
   * Validate checkout data
   * @uses    woocommerce_checkout_process, priority 10, 1
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @param   array     $post_data  Post data
   * @param   WC_Error  $errors     Error object
   * @return  void
   */
  public function validate_checkout_data($post_data, $errors)
  {

    print_r($post_data);
    if (
      isset($post_data['billing_confirm_email']) &&
      $post_data['billing_email'] !== $post_data['billing_confirm_email']
    ) :

      $errors->add(
        'billing_confirm_email_validation',
        'Email address does not match',
        array(
          'id' => 'billing_confirm_email'
        )
      );
    endif;
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

    $fields['billing']['billing_confirm_email'] = array(
      'label'       => __('Re-Type Email Address', 'woocommerce'),
      'required'    => true,
      'class'       => array('form-row-wide', 'form-row-last'),
      'clear'       => true,
      'type'        => 'email',
      'priority'    => 111,
    );

    $fields['billing']['billing_email']['title'] = __("Email Address");
    $fields['billing']['billing_email']['class'] = array('form-row-wide', 'form-row-first');

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

    $overwrite_templates = [
      'checkout/payment.php',
      'checkout/form-billing.php',
      'checkout/form-shipping.php'
    ];
    if (in_array($template_name, $overwrite_templates)) :
      $template = \WOOFASTCHECK_PLUGIN_DIR . 'public/partials/' . $template_name;
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

          $fee = apply_filters('woofastcheck/convenience-fee', $fee, $payment, $cart);

          $cart->add_fee('Transaction Fee', $fee, true);
          break;
        endif;
      endforeach;
    endif;
  }

  public function add_order_review_open_class()
  {
    echo "<div class='woocommerce-order-review-holder'>";
  }

  public function add_order_review_close_class()
  {
    echo "</div>";
  }
}
