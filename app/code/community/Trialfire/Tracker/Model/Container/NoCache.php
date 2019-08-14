<?php
/**
 * @category    Trialfire
 * @package     Trialfire_Tracker
 * @author      Mark Lieberman <mark@trialfire.com>
 * @copyright   Copyright (c) Trialfire
 *
 * A container that never caches for a full page cache hole punch.
 */
class Trialfire_Tracker_Model_Container_NoCache extends Enterprise_PageCache_Model_Container_Abstract
{

  protected function _getCacheId()
  {
    return false;
  }

  protected function _renderBlock()
  {
      $block = $this->_placeholder->getAttribute('block');
      $block = new $block;

      // Only needed if the block uses a template
      $block->setTemplate($this->_placeholder->getAttribute('template'));

      return $block->toHtml();
  }

  protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
  {
      return false;
  }

}
?>