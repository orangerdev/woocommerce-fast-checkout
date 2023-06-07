<?php

namespace Woofastcheck;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Woofastcheck
 * @subpackage Woofastcheck/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woofastcheck
 * @subpackage Woofastcheck/admin
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Admin
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
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct($plugin_name, $version)
  {

    $this->plugin_name = $plugin_name;
    $this->version = $version;
  }

  /**
   * Load carbonfields
   * @uses    after_setup_theme, priority 10
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @return  void
   */
  public function crb_load()
  {
    \Carbon_Fields\Carbon_Fields::boot();
  }

  /**
   * Register carbonfields setting
   * @uses    carbon_fields_register_fields, priority 10
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @return  void
   */
  public function register_setting()
  {
    /**
     * Registering setting for page
     */
    Container::make('post_meta', __('Configuration', 'woofastcheck'))
      ->where('post_type', '=', CPT_PAGE)
      ->add_fields([
        Field::make("association", 'products', __("Selected Product", "woofastcheck"))
          ->set_types([
            [
              'type' => 'post',
              'post_type' => CPT_PRODUCT
            ]
          ])
      ]);

    /**
     * Registering setting for product
     */
    Container::make('post_meta', __('Configuration', 'woofastcheck'))
      ->where('post_type', '=', CPT_PRODUCT)
      ->add_fields([
        Field::make("checkbox", "only_one_in_cart", "Make sure only one product in cart")
          ->set_option_value("yes")
          ->set_default_value("")
      ]);
  }
}
