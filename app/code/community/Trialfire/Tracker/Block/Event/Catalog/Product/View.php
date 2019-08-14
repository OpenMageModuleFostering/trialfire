<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 */
class Trialfire_Tracker_Block_Event_Catalog_Product_View extends Trialfire_Tracker_Block_Track {

  protected function _construct() {
    parent::_construct();

    $store = Mage::app()->getStore();
    $product = Mage::registry('current_product');

    $this->setTrackName('Viewed Product');
    $this->setTrackProperties(array(
      'id' => $product->getId(),
      'sku' => $product->getSku(),
      'name' => $product->getName(),
      'price' => floatval($product->getPrice()),
      'currency' => $store->getCurrentCurrencyCode()
    ));
  }

}
?>