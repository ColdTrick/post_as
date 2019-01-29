<?php

namespace ColdTrick\PostAs;

class PluginSettings {
	
	/**
	 * Convert arrays to a comma separated string so the plugin setting can be saved
	 *
	 * @param \Elgg\Hook $hook 'setting', 'plugin'
	 *
	 * @return void|string
	 */
	public static function convertArrayToString(\Elgg\Hook $hook) {
		
		if ($hook->getParam('plugin_id') !== 'post_as') {
			// not correct plugin
			return;
		}
		
		$value = $hook->getValue();
		if (!is_array($value)) {
			// not an array
			return;
		}
		
		$value = array_unique($value);
		$value = array_filter($value);
		
		return implode(',', $value);
	}
}
