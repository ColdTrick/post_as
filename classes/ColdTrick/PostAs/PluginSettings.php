<?php

namespace ColdTrick\PostAs;

/**
 * Plugin setting event listener
 */
class PluginSettings {
	
	/**
	 * Convert arrays to a comma separated string so the plugin setting can be saved
	 *
	 * @param \Elgg\Event $event 'setting', 'plugin'
	 *
	 * @return null|string
	 */
	public static function convertArrayToString(\Elgg\Event $event): ?string {
		if ($event->getParam('plugin_id') !== 'post_as') {
			// not correct plugin
			return null;
		}
		
		$value = $event->getValue();
		if (!is_array($value)) {
			// not an array
			return null;
		}
		
		$value = array_unique($value);
		$value = array_filter($value);
		
		return implode(',', $value);
	}
}
