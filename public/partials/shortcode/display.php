<div class="woofastcheck-select-product woofastcheck-holder">
  <?php
  foreach ($products as $i => $_product) :
    $product = wc_get_product($_product['id']);
    $pgs = carbon_get_post_meta($product->get_id(), 'available_payment_gateway');

    $checked = $i === 0 ? 'checked' : '';

  ?>
    <label>
      <input type="radio" name="product" value="<?php echo $product->get_id(); ?>" <?= $checked; ?> data-sku="<?= $product->get_sku(); ?>" data-pgs="<?= implode(",", $pgs); ?>" />
      <span class=" description">
        <h3 class="name"><?php echo $product->get_name(); ?></h3>
        <?php echo wpautop($product->get_description()); ?>
      </span>
    </label>
  <?php endforeach; ?>
  <div class="button-holder">
    <button type="button" class="button button-nav woofastcheck-next-checkout-button">
      Next
      <i class="fa fa-chevron-right"></i>
    </button>
  </div>
</div>
<div class="woofastcheck-checkout-form woofastcheck-holder">
  <div class="button-holder">
    <button type="button" class="button button-nav button-primary woofastcheck-back-product-button">
      <i class="fa fa-chevron-left"></i>
      Back
    </button>
  </div>
  <?php echo do_shortcode("[woocommerce_checkout]"); ?>
</div>