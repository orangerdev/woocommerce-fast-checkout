<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Woofastcheck
 * @subpackage Woofastcheck/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woofastcheck
 * @subpackage Woofastcheck/includes
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Woofastcheck
{

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      Woofastcheck_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function __construct()
  {
    if (defined('WOOFASTCHECK_VERSION')) {
      $this->version = WOOFASTCHECK_VERSION;
    } else {
      $this->version = '1.0.0';
    }
    $this->plugin_name = 'woofastcheck';

    $this->load_dependencies();
    $this->set_locale();
    $this->define_admin_hooks();
    $this->define_public_hooks();
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Woofastcheck_Loader. Orchestrates the hooks of the plugin.
   * - Woofastcheck_i18n. Defines internationalization functionality.
   * - Woofastcheck_Admin. Defines all hooks for the admin area.
   * - Woofastcheck_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies()
  {

    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woofastcheck-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woofastcheck-i18n.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-woofastcheck-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-woofastcheck-public.php';

    $this->loader = new Woofastcheck_Loader();
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Woofastcheck_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale()
  {

    $plugin_i18n = new Woofastcheck_i18n();

    $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks()
  {

    $admin = new Woofastcheck\Admin($this->get_plugin_name(), $this->get_version());

    $this->loader->add_action('after_setup_theme', $admin, 'crb_load');
    $this->loader->add_action('carbon_fields_register_fields', $admin, 'register_setting', -1);
    $this->loader->add_filter('woofastcheck/main-options', $admin, 'get_main_options', 999999999);
    $this->loader->add_filter('updraftplus_exclude_directory', $admin, 'exclude_directory', 99, 2);
  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_public_hooks()
  {

    $public = new Woofastcheck\Front($this->get_plugin_name(), $this->get_version());

    $this->loader->add_action('init', $public, 'register_shortcode');

    $this->loader->add_action('woocommerce_ajax_added_to_cart', $public, 'remove_other_items');
    $this->loader->add_action('template_redirect', $public, 'add_to_cart');

    $this->loader->add_action('wp_enqueue_scripts', $public, 'enqueue_scripts');
    $this->loader->add_action('wp_enqueue_scripts', $public, 'enqueue_styles');

    $this->loader->add_action('wp_head', $public, 'register_font_family');

    $this->loader->add_filter('body_class', $public, 'add_body_class');
    $this->loader->add_filter('wc_get_template', $public, 'get_template_part', 10, 2);
    $this->loader->add_filter('woofastcheck/checkout/postdata', $public, 'get_post_data');

    $this->loader->add_action('woocommerce_after_checkout_validation', $public, 'validate_checkout_data', 99, 2);

    $this->loader->add_filter('woocommerce_checkout_fields', $public, 'modify_checkout_fields');
    $this->loader->add_action('woocommerce_cart_calculate_fees', $public, 'add_convenience_fee', 999);

    $this->loader->add_action('woocommerce_checkout_before_order_review_heading', $public, 'add_order_review_open_class');
    $this->loader->add_action('woocommerce_checkout_after_order_review_heading', $public, 'add_order_review_close_class');
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run()
  {
    $this->loader->run();
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name()
  {
    return $this->plugin_name;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     1.0.0
   * @return    Woofastcheck_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader()
  {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version()
  {
    return $this->version;
  }
}
