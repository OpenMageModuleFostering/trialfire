<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 *
 * Outputs the tracking script at the end of <head> on every page.
 */
class Trialfire_Tracker_Block_Head extends Mage_Core_Block_Template {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('trialfire/tracker/head.phtml');
    }

    /**
     * Get the Trialfire API Token from the extension settings.
     */
    public function getApiToken() {
        return Mage::helper('trialfire_tracker')->getApiToken();
    }

    /**
     * Get the Trialfire Asset URL from the extension settings.
     */
    public function getAssetUrl() {
        return Mage::helper('trialfire_tracker')->getAssetUrl();
    }

}
?>