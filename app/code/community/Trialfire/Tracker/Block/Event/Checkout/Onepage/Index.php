<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 *
 * Output a track() call containing all the product information going into the checkout process.
 */
class Trialfire_Tracker_Block_Event_Checkout_Onepage_Index extends Trialfire_Tracker_Block_Track {

  protected function _construct() {
    parent::_construct();

    $store = Mage::app()->getStore();
    $quote = Mage::getModel('checkout/cart')->getQuote();
    $session = Mage::getSingleton('checkout/session');

    // Build the list of products in the quote.
    $products = array();
    foreach ($quote->getAllVisibleItems() as $product) {
      $products[] = array(
        'id' => $product->getId(),
        'sku' => $product->getSku(),
        'name' => $product->getName(),
        'price' => floatval($product->getPrice()),
        'quantity' => intval($product->getQty())
      );
    }

    $this->setTrackName('Started Checkout');
    $this->setTrackProperties(array(
      'quoteId' => $session->getQuoteId(),
      'currency' => $store->getCurrentCurrencyCode(),
      'products' => $products
    ));
  }

}
?>