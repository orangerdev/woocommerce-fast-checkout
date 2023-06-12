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
   * Payment channels
   * @since   1.0.0
   * @access  protected
   * @var     array
   */
  protected $payment_channels = [];

  /**
   * Variable for main options
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @var     Carbon_Fields\Container
   */
  protected $main_options;

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
   * Set payment channel options for the theme options
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @return  array
   */
  public function set_payment_channels()
  {
    if (is_array($this->payment_channels) && count($this->payment_channels) === 0) :
      $gateways = \WC()->payment_gateways->get_available_payment_gateways();
      foreach ((array) $gateways as $key => $gateway) :
        $this->payment_channels[$key] = $gateway->title;
      endforeach;
    endif;

    return $this->payment_channels;
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
    $this->main_options = Container::make('theme_options', __('eCom Config', 'docquity'))
      ->add_tab(__('Payment Fee Setup', 'docquity'), [

        Field::make('complex', 'payment', __('Payment Channel', 'docquity'))
          ->add_fields([
            Field::make('select', 'channel', 'Channel')
              ->set_options([$this, 'set_payment_channels'])
              ->set_required(true)
              ->set_width(50),
            Field::make('text', 'fee', 'Fee')
              ->set_attribute('type', 'number')
              ->set_required(true)
              ->set_width(30),
            Field::make('select', 'fee_type', 'Fee Type')
              ->add_options([
                'percent' => 'Percent',
                'flat' => 'Flat'
              ])
              ->set_width(20)
          ])
          ->set_layout('tabbed-vertical')
          ->set_header_template('<% if(channel) { %><%= channel %> <% } %>'),
      ]);

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

    $fields = [
      Field::make("checkbox", "only_one_in_cart", "Make sure only one product in cart")
        ->set_option_value("yes"),
      Field::make('checkbox', "enable_custom_payment_gateway", "Overwrite Payment Gateway")
        ->set_option_value("yes"),
      Field::make('multiselect', 'available_payment_gateway', __('Available Payment Gateway', 'dcm_phil'))
        ->add_options([$this, 'set_payment_channels'])
        ->set_conditional_logic(array(
          'relation' => 'AND',
          [
            'field' => 'enable_custom_payment_gateway',
            'value' => true
          ]
        ))
    ];

    Container::make('post_meta', __('Configuration', 'woofastcheck'))
      ->where('post_type', '=', CPT_PRODUCT)
      ->add_fields(
        apply_filters("woofastcheck/product/settings", $fields)
      );
  }

  /**
   * Exclude dir folder from updraft backup
   * @uses    updraftplus_exclude_directory, priority 10
   * @author  Ridwan Arifandi
   * @since   1.0.0
   * @param   bool $filter
   * @param   string $dir
   * @return  bool
   */
  public function exclude_directory($filter, $dir)
  {
    return (basename($dir) === '.git') ? true : $filter;
  }
}
