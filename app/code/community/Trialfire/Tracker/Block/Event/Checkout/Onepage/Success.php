<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 *
 * Output a track() call which containing all the information about the completed order.
 */
class Trialfire_Tracker_Block_Event_Checkout_Onepage_Success extends Trialfire_Tracker_Block_Track {

  protected function _construct() {
    parent::_construct();

    $store = Mage::app()->getStore();
    $lastOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
    $order = Mage::getModel('sales/order')->loadByIncrementId($lastOrderId);

    // Build the list of products in the order.
    $products = array();
    foreach ($order->getAllVisibleItems() as $product) {
      $products[] = array(
        'id' => $product->getId(),
        'sku' => $product->getSku(),
        'name' => $product->getName(),
        'price' => $product->getPrice()
      );
    }

    $this->setTrackName('Completed Order');
    $this->setTrackProperties(array(
      'quoteId' => $order->getQuoteId(),
      'orderId' => $lastOrderId,
      'total' => floatval($order->getGrandTotal()),
      'shipping' => floatval($order->getShippingAmount()),
      'shippingMethod' => $order->getShippingDescription(),
      'tax' => floatval($order->getTaxAmount()),
      'coupon' => $order->getCouponCode(),
      'discount' => floatval($order->getDiscountAmount()),
      'currency' => $store->getCurrentCurrencyCode(),
      'products' => $products
    ));
  }

}
?>