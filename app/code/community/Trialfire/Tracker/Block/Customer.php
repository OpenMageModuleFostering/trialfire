<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 *
 * Outputs a Trialfire.identify() call whenever the logged in customer is updated.
 */
class Trialfire_Tracker_Block_Customer extends Trialfire_Tracker_Block_Identify {

  protected function _construct() {
    parent::_construct();

    if (Mage::helper('customer')->isLoggedIn()) {
      $session = Mage::getSingleton('customer/session');
      $customer = $session->getCustomer();

      switch (Mage::app()->getRequest()->getActionName()) {
        case 'success': // checkout/onepage/success
          // No need to re-identify existing customers after an order is placed.
          // Ignoring the update to supress the identify() call in the following case.
          $session->setTfUpdatedAt(strtotime($customer->getUpdatedAt()));

          // However, we do want to identify() during register and checkout.
          // Changing customer ID will trigger an identify() call in the following case.
          // no break;
        default:
          if ($this->customerWasUpdated($session, $customer)) {
            // Identify the customer to Trialfire.
            $this->setShouldIdentify(true);
            $this->setUserId($customer->getId());
            $this->setUserTraits($this->getCustomerInfo($customer));
          }
          break;
      }
    }
  }

  /**
   * True if the customer was updated since the last call, otherwise false.
   * Checks the customer information and primary billing address for changes.
   * Always true on the first call.
   */
  private function customerWasUpdated($session, $customer) {
    // We won't call identify() unless the customer was updated after this time.
    $tfUpdatedAt = $session->getTfUpdatedAt() ?: 0;
    $tfCustomerId = $session->getTfCustomerId() ?: 0;

    // Watch the customer itself and the primary billing address for changes.
    try {
      $checkUpdatedAt = strtotime($customer->getUpdatedAt());
      $billing = $customer->getPrimaryBillingAddress();
      if (!empty($billing)) {
        $billingUpdatedAt = strtotime($billing->getUpdatedAt());
        $checkUpdatedAt = max($checkUpdatedAt, $billingUpdatedAt);
      }

      // Copy updatedAt property into a new session variable and watch for changes.
      if (($checkUpdatedAt > $tfUpdatedAt) || ($tfCustomerId !== $customer->getId())) {
        $session->setTfUpdatedAt($checkUpdatedAt);
        $session->setTfCustomerId($customer->getId());
        return true;
      }
    } catch (Exception $e) {
      Mage::log('customerWasUpdated error: ' + $e->getMessage());
    }

    return false;
  }

  /**
   * Builds a user traits array from information about the customer.
   */
  private function getCustomerInfo($customer) {
    $userTraits = array(
      'firstName' => $customer->getFirstname(),
      'lastName' => $customer->getLastname(),
      'email' => $customer->getEmail()
    );

    // Get the date of birth if the attribute is enabled.
    $dateOfBirth = $customer->getDob();
    if (!empty($dateOfBirth)) {
      $userTraits['dateOfBirth'] = explode(' ', $dateOfBirth, 2)[0];
    }

    // Get the gender if the attribute is enabled.
    $gender = $customer->getGender();
    if (!empty($gender)) {
      // Get the attribute text for gender.
      $userTraits['gender'] = Mage::getResourceSingleton('customer/customer')
        ->getAttribute('gender')
        ->getSource()
        ->getOptionText($gender);
    }

    // Get address information.
    $billing = $customer->getPrimaryBillingAddress();
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