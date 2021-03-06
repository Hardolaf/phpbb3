<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* A small class for 3.0.x (no autoloader in 3.0.x)
*/
class phpbb_captcha_factory
{
	/**
	* return an instance of class $name in file $name_plugin.php
	*/
	static public function get_instance($name)
	{
		global $phpbb_root_path, $phpEx;

		$name = basename($name);
		if (!class_exists($name))
		{
			include($phpbb_root_path . "includes/captcha/plugins/{$name}_plugin." . $phpEx);
		}
		$instance = call_user_func(array($name, 'get_instance'));
		return $instance;
	}

	/**
	* Call the garbage collector
	*/
	function garbage_collect($name)
	{
		global $phpbb_root_path, $phpEx;

		$name = basename($name);
		if (!class_exists($name))
		{
			include($phpbb_root_path . "includes/captcha/plugins/{$name}_plugin." . $phpEx);
		}
		$captcha = self::get_instance($name);
		$captcha->garbage_collect(0);
	}

	/**
	* return a list of all discovered CAPTCHA plugins
	*/
	function get_captcha_types()
	{
		global $phpbb_root_path, $phpEx, $phpbb_extension_manager;

		$captchas = array(
			'available'		=> array(),
			'unavailable'	=> array(),
		);

		$finder = $phpbb_extension_manager->get_finder();
		$captcha_plugin_classes = $finder
			->extension_directory('/captcha')
			->suffix('_plugin')
			->core_path('includes/captcha/plugins/')
			->get_classes();

		foreach ($captcha_plugin_classes as $class)
		{
			// check if this class needs to be loaded in legacy mode
			$old_class = preg_replace('/^phpbb_captcha_plugins_/', '', $class);
			if (file_exists($phpbb_root_path . "includes/captcha/plugins/$old_class.$phpEx") && !class_exists($old_class))
			{
				include($phpbb_root_path . "includes/captcha/plugins/$old_class.$phpEx");
				$class = preg_replace('/_plugin$/', '', $old_class);
			}

			if (call_user_func(array($class, 'is_available')))
			{
				$captchas['available'][$class] = call_user_func(array($class, 'get_name'));
			}
			else
			{
				$captchas['unavailable'][$class] = call_user_func(array($class, 'get_name'));
			}
		}

		return $captchas;
	}
}
