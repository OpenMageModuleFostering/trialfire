<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Trialfire_Tracker_Model_Observer {

  /**
   * Do not modify the blocks and responses if module output is disabled.
   */
  private function isModuleDisabled() {
    $helper = Mage::helper('trialfire_tracker');
    return !($helper->isModuleEnabled() && $helper->isModuleOutputEnabled());
  }

  /**
   * Unset the session property used to track changes to the customer.
   * This forces an identify() call after login, even if the customer hasn't changed.
   * Mainly to handle cases of different accounts logging into Magento from one browser.
   */
  public function customerLogin(Varien_Event_Observer $observer) {
    if ($this->isModuleDisabled()) {
      return $this;
    }

    $session = Mage::getSingleton('customer/session')->unsTfUpdatedAt();
  }

  /**
   * Fired when an item is added to the shopping cart.
   * The product ID is stored in the session.
   * A template block will lookup the SKU and fire an event at render time.
   */
  public function addedToCart(Varien_Event_Observer $observer) {
    if ($this->isModuleDisabled()) {
      return $this;
    }

    switch (Mage::app()->getRequest()->getActionName()) {
      case 'loginPost':
        // Logging in during a guest checkout will add guest's items to the logged in customer's existing quote.
        // Ignore the items being added to the quote during this process.
        // Quote IDs for viewed items, etc. may be incorrectly reported due to changing quote ID in this case.
        break;
      default:
        // Record the item ID in the session.
        $productId = $observer->getQuoteItem()->getProduct()->getId();
        $session = Mage::getSingleton("checkout/session");
        $session->setItemAddedToCart($productId);
        break;
    }
  }

  /**
   * Fired when an item is removed from the shopping cart.
   * The product ID is stored in the session.
   * A template block will lookup the SKU and fire an event at render time.
   */
  public function removedFromCart(Varien_Event_Observer $observer) {
    if ($this->isModuleDisabled()) {
      return $this;
    }

    // Record the item ID in the session.
    $productId = $observer->getQuoteItem()->getProduct()->getId();
    $session = Mage::getSingleton("checkout/session");
    $session->setItemRemovedFromCart($productId);
  }

  /**
   * Hook into block rendering logic and inject the tracking code into the checkout method block
   * (checkout.onepage.login) and billing information block (checkout.onepage.billing).
   */
  public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer) {
    if ($this->isModuleDisabled()) {
      return $this;
    }

    switch ($observer->getEvent()->getBlock()->getNameInLayout()) {
      case 'minicart_content':
        // Add the "Cart" block to the minicart content.
        $this->minicartContent($observer);
        break;
      case 'checkout.onepage.login':
        // Saw the "Checkout Method" step because they are not logged in.
        $this->checkoutOnepageRenderLogin($observer);
        break;
      case 'checkout.onepage.billing':
        // Always rendered into the onepage checkout.
        $this->checkoutOnepageRenderBilling($observer);
        break;
      case 'checkout.onepage.shipping':
        // Always rendered into the onepage checkout.
        $this->checkoutOnepageRenderShipping($observer);
        break;

    }

    return $this;
  }

  /**
   * Inject a track() call into the minicart.
   *
   * This handles the case where an item is removed via the minicart. We inject a track call into the HTML that
   * replaces the minicart content.
   *
   * This is a block rendering event - modify the HTML in the transport object.
   */
  private function minicartContent(Varien_Event_Observer $observer) {
    // Pull the original block HMTL from transport.
    $blockHtml = $observer->getEvent()->getTransport()->getHtml();

    // Generate the track call to inject into the block.
    $trackHtml = Mage::app()->getLayout()
      ->createBlock('trialfire_tracker/cart')
      ->toHtml();

    // Put the modified block HTML into transport.
    $observer->getEvent()->getTransport()->setHtml($blockHtml . $trackHtml);
  }

  /**
   * Inject a track() call into the login block.
   *
   * This is the "Checkout Method" step which is displayed when the user is a guest. It will be displayed along with
   * the billing step block. However, the billing step block will contain additional fields to facilitate a guest
   * checkout and possibly registration.
   *
   * If they login, the page will reload and begin the checkout process again.
   *
   * This is a block rendering event - modify the HTML in the transport object.
   */
  private function checkoutOnepageRenderLogin(Varien_Event_Observer $observer) {
    // Pull the original block HMTL from transport.
    $blockHtml = $observer->getEvent()->getTransport()->getHtml();

    // Generate the track call to inject into the block.
    $session = Mage::getSingleton("checkout/session");
    $trackHtml = Mage::app()->getLayout()
      ->createBlock('trialfire_tracker/track')
      ->setTrackName('Viewed Checkout Method Step')
      ->setTrackProperties(array(
        'quoteId' => $session->getQuoteId()
      ))
      ->toHtml();

    // Put the modified block HTML into transport.
    $observer->getEvent()->getTransport()->setHtml($blockHtml . $trackHtml);
  }

  /**
   * Inject a track() call into the billing block.
   *
   * This is rendered but hidden until the section is displayed. Inject a script to fire a track event when a
   * mouseover event is generated over the billing section.
   *
   * This is a block rendering event - modify the HTML in the transport object.
   */
  private function checkoutOnepageRenderBilling(Varien_Event_Observer $observer) {
    // Pull the original block HMTL from transport.
    $blockHtml = $observer->getEvent()->getTransport()->getHtml();

    // Generate the track call to inject into the block.
    $session = Mage::getSingleton("checkout/session");
    $trackHtml = Mage::app()->getLayout()
      ->createBlock('trialfire_tracker/track')
      ->setTemplate('trialfire/tracker/track_observe.phtml')
      ->setTrackName('Viewed Checkout Billing Step')
      ->setTrackProperties(array(
        'quoteId' => $session->getQuoteId()
      ))
      ->setObserveSelector('co-billing-form')
      ->setObserveEvent('mouseover')
      ->toHtml();

    // Put the modified block HTML into transport.
    $observer->getEvent()->getTransport()->setHtml($blockHtml . $trackHtml);
  }

  /**
   * Inject a track() call into the shipping block.
   *
   * This is rendered but hidden until the section is displayed. Inject a script to fire a track event when a
   * mouseover event is generated over the shipping section.
   *
   * This is a block rendering event - modify the HTML in the transport object.
   */
  private function checkoutOnepageRenderShipping(Varien_Event_Observer $observer) {
    // Pull the original block HMTL from transport.
    $blockHtml = $observer->getEvent()->getTransport()->getHtml();

    // Generate the track call to inject into the block.
    $session = Mage::getSingleton("checkout/session");
    $trackHtml = Mage::app()->getLayout()
      ->createBlock('trialfire_tracker/track')
      ->setTemplate('trialfire/tracker/track_observe.phtml')
      ->setTrackName('Viewed Checkout Shipping Step')
      ->setTrackProperties(array(
        'quoteId' => $session->getQuoteId()
      ))
      ->setObserveSelector('co-shipping-form')
      ->setObserveEvent('mouseover')
      ->toHtml();

    // Put the modified block HTML into transport.
    $observer->getEvent()->getTransport()->setHtml($blockHtml . $trackHtml);
  }

  /**
   * Saving the billing information - which means starting the shipping step or the shipping method step.
   *
   * This is a controller action - modify the response body.
   */
  public function checkoutOnepageSaveBillingOrShipping(Varien_Event_Observer $observer) {
    if ($this->isModuleDisabled()) {
      return $this;
    }

    // Decode the original JSON response.
    $body = $observer->getEvent()->getControllerAction()->getResponse()->getBody();
    $json = Mage::helper('core')->jsonDecode($body);

    if ($json['goto_section'] === 'shipping_method') {
      // Generate the track call to inject into the block.
      $session = Mage::getSingleton("checkout/session");
      $json['update_section']['html'] .= Mage::app()->getLayout()
        ->createBlock('trialfire_tracker/track')
        ->setTrackName('Viewed Checkout Shipping Method Step')
        ->setTrackProperties(array(
          'quoteId' => $session->getQuoteId()
        ))
        ->toHtml();
    }

    // Encode the modified JSON and return.
    $body = Mage::helper('core')->jsonEncode($json);
    $observer->getEvent()->getControllerAction()->getResponse()->setBody($body);
  }

  /**
   * Saving the shipping method - which means starting the payment step.
   *
   * This is a controller action - modify the response body.
   */
  public function checkoutOnepageSaveShippingMethod(Varien_Event_Observer $observer) {
    if ($this->isModuleDisabled()) {
      return $this;
    }

    // Decode the original JSON response.
    $body = $observer->getEvent()->getControllerAction()->getResponse()->getBody();
    $json = Mage::helper('core')->jsonDecode($body);

    if ($json['goto_section'] === 'payment') {
      // Generate the track call to inject into the block.
      $session = Mage::getSingleton("checkout/session");
      $json['update_section']['html'] .= Mage::app()->getLayout()
        ->createBlock('trialfire_tracker/track')
        ->setTrackName('Viewed Checkout Payment Step')
        ->setTrackProperties(array(
          'quoteId' => $session->getQuoteId()
        ))
        ->toHtml();
    }

    // Encode the modified JSON and return.
    $body = Mage::helper('core')->jsonEncode($json);
    $observer->getEvent()->getControllerAction()->getResponse()->setBody($body);
  }

  /**
   * Saving the payment method - which means starting the review step.
   *
   * This is a controller action - modify the response body.
   */
  public function checkoutOnepageSavePayment(Varien_Event_Observer $observer) {
    if ($this->isModuleDisabled()) {
      return $this;
    }

    // Decode the original JSON response.
    $body = $observer->getEvent()->getControllerAction()->getResponse()->getBody();
    $json = Mage::helper('core')->jsonDecode($body);

    if ($json['goto_section'] === 'review') {
      // Generate the track call to inject into the block.
      $session = Mage::getSingleton("checkout/session");
      $json['update_section']['html'] .= Mage::app()->getLayout()
        ->createBlock('trialfire_tracker/track')
        ->setTrackName('Viewed Checkout Review Step')
        ->setTrackProperties(array(
          'quoteId' => $session->getQuoteId()
        ))
        ->toHtml();
    }

    // Encode the modified JSON and return.
    $body = Mage::helper('core')->jsonEncode($json);
    $observer->getEvent()->getControllerAction()->getResponse()->setBody($body);
  }

}
?>