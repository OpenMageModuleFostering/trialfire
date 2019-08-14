<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 */
class Trialfire_Tracker_Helper_Data extends Mage_Core_Helper_Data {

    const CONFIG_API_TOKEN = 'trialfire_tracker/settings/api_token';

    const CONFIG_ASSET_URL = 'trialfire_tracker/settings/asset_url';

    // Settings

    /**
     * Get the Trialfire API Token from the extension settings.
     */
    public function getApiToken($store = null) {
      return Mage::getStoreConfig(self::CONFIG_API_TOKEN, $store);
    }

    /**
     * Get the Trialfire Asset URL from the extension settings.
     */
    public function getAssetUrl($store = null) {
      return Mage::getStoreConfig(self::CONFIG_ASSET_URL, $store);
    }

    // Helpers

    /**
     * Safely encode a PHP object as JSON.
     */
    public function getJsonArray($array) {
      // Jackson handles Unicode escape sequences.
      return json_encode(array_filter($array));

      /*
      if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
        // Can use JSON_UNESCAPED_UNICODE
        return json_encode($array, JSON_UNESCAPED_UNICODE);
      } else {
        // Adapted from Inchoo_KISSmetris but it's wrong.
        // Only works if strings are ISO-8859-1.
        // See: http://stackoverflow.com/a/7382130
        array_walk_recursive($array, function(&$item, $key) {
          if (is_string($item)) {
              $item = htmlentities($item, ENT_NOQUOTES);
          }
        });
        return html_entity_decode(json_encode($arr));
      }
      */
    }

}
?>