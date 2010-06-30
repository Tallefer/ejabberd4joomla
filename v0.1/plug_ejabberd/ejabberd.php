<?php
/**
 * @version		0.1
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2008 Subho.me , All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
error_reporting(E_ALL);
ini_set('display_errors', '1');
*/

/**
 * eJabberd Authentication Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 1.5
 */
class plgAuthenticationEjabberd extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param	object	$subject	The object to observe
	 * @param	array	$config		An array that holds the plugin configuration
	 * @since	1.5
	 */
	function plgAuthenticationEjabberd(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->_config = $config;
		
		// temporary: create our ejabberd user table if not exists
		$db =& JFactory::getDBO();
		$query = "
		CREATE TABLE IF NOT EXISTS users (
					username varchar(250) PRIMARY KEY,
					password text NOT NULL,
					created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
				) CHARACTER SET utf8;
		";
		$db->setQuery( $query );
		$result = $db->query();
		
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param	array	$credentials	Array holding the user credentials
	 * @param	array	$options		Array of extra options
	 * @param	object	$response		Authentication response object
	 * @return	boolean
	 * @since	1.5
	 */
	function onAuthenticate( $credentials, $options, &$response )
	{

		$response->status = JAUTHENTICATE_STATUS_FAILURE;
		
		// first piggyback on joomla auth
		if (class_exists('plgAuthenticationJoomla')) {
			$JAuth = new plgAuthenticationJoomla($this->_subject, $this->_config);
			$JAuth->onAuthenticate( $credentials, $options, $response );
		}

		// see if Joomla auth passed
		if ($response->status == JAUTHENTICATE_STATUS_SUCCESS) {
			
			// Get a database object
			$db =& JFactory::getDBO();
			
			// prepare username and password
			$username = $db->Quote( $credentials['username'] );
			$password = $db->Quote( $credentials['password'] );
	
			// sync the jos_users password with users password
			$query = "INSERT INTO users "
				." SET username = $username, password = $password"
				." ON DUPLICATE KEY UPDATE password = $password"
				;
			$db->setQuery( $query );
			$result = $db->query();

			return true;
		}
		else
		{
			$response->status			= JAUTHENTICATE_STATUS_FAILURE;
			$response->error_message	= 'Could not authenticate';
			return false;
		}
	}
}
