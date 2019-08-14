<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 *
 * Output an identify() call to populate the user traits for guest customers in Trialfire.
 */
class Trialfire_Tracker_Block_Event_Checkout_Onepage_Guest extends Trialfire_Tracker_Block_Identify {

  protected function _construct() {
    parent::_construct();

    $store = Mage::app()->getStore();
    $lastOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
    $order = Mage::getModel('sales/order')->loadByIncrementId($lastOrderId);

    if ($order->getCustomerIsGuest()) {
      // We cannot identify() the customer as there is no customer ID.
      // However, we can record traits about the anonymous visitor.
      $this->setShouldIdentify(true);
      $this->setUserId(null);
      $this->setUserTraits($this->getCustomerInfo($order));
    }
  }

  /**
   * Builds a user traits array from information about the customer.
   * Information is collected from the order because the customer is a guest.
   */
  private function getCustomerInfo($order) {
    $userTraits = array(
      'firstName' => $order->getCustomerFirstname(),
      'lastName' => $order->getCustomerLastname(),
      'email' => $order->getCustomerEmail()
    );

    // Get the date of birth if the attribute is enabled.
    $dateOfBirth = $order->getCustomerDob();
    if (!empty($dateOfBirth)) {
      $userTraits['dateOfBirth'] = explode(' ', $dateOfBirth, 2)[0];
    }

    // Get the gender if the attribute is enabled.
    $gender = $order->getCustomerGender();
    if (!empty($gender)) {
      // Get the attribute text for gender.
      $userTraits['gender'] = Mage::getResourceSingleton('customer/customer')
        ->getAttribute('gender')
        ->getSource()
        ->getOptionText($gender);
    }

    // Get address information.
    $billing = $order->getBillingAddress();
    if (!empty($billing)) {
      $userTraits['company'] = $billing->getCompany();
      $userTraits['country'] = $billing->getCountry();
      $userTraits['city'] = $billing->getCity();
      $userTraits['region'] = $billing->getRegion();
      $userTraits['postal'] = $billing->getPostcode();
      $userTraits['phone'] = $billing->getTelephone();

      // Implode the streets in the address into one line.
      $userTraits['address'] = implode("\n", $billing->getStreet());
    }

    return $userTraits;
  }

}
?>