<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 *
 * Outputs a track() call whenever a product is added or removed from the shopping cart.
 */
class Trialfire_Tracker_Block_Cart extends Trialfire_Tracker_Block_Track {

  protected function _construct() {
    parent::_construct();

    $session = Mage::getSingleton("checkout/session");

    // Check if a product ID was added to the shopping cart.
    $productId = $session->getItemAddedToCart();
    if (!empty($productId)) {
      // Clear the product ID.
      $session->setItemAddedToCart(null);

      // Track the product added to the cart.
      $this->setTrackName('Added to Cart');
      $properties = $this->getProductInfo($productId);
      $properties['quoteId'] = $session->getQuoteId();
      $this->setTrackProperties($properties);
      return;
    }

    // Check if a product ID was removed from the shopping cart.
    $productId = $session->getItemRemovedFromCart();
    if (!empty($productId)) {
      // Clear the product ID.
      $session->setItemRemovedFromCart(null);

      // Track the product removed from the cart.
      $this->setTrackName('Removed from Cart');
      $properties = $this->getProductInfo($productId);
      $properties['quoteId'] = $session->getQuoteId();
      $this->setTrackProperties($properties);
      return;
    }

    // Nothing added or removed - don't render a track call.
    $this->setShouldTrack(false);
  }

  /**
   * Get the information about a product.
   */
  private function getProductInfo($productId) {
    $store = Mage::app()->getStore();
    $product = Mage::getModel('catalog/product')->load($productId);
    return array(
      'id' => $product->getId(),
      'sku' => $product->getSku(),
      'name' => $product->getName(),
      'price' => floatval($product->getPrice()),
      'currency' => $store->getCurrentCurrencyCode()
    );
  }

}
?>