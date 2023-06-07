<?php

namespace Woofastcheck;

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

    wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woofastcheck-public.css', array(), $this->version, 'all');

    if ($this->has_shortcode) :
      wp_enqueue_style("select2");
    endif;
  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts()
  {

    wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woofastcheck-public.js', array('jquery'), $this->version, false);

    if ($this->has_shortcode) :
      wp_enqueue_script('selectWoo');
      wp_enqueue_script('wc-checkout');
      wp_enqueue_script('wc-country-select');
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

      echo do_shortcode("[woocommerce_checkout]");

      $content .= ob_get_clean();

    endif;

    return $content;
  }
}
