<?php
/**
*
* @package phpBB Extension - tas2580 Hide Bots
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\hidebots\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user\user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\config\config				$config				Config Object
	* @param \phpbb\template\template			$template				Template object
	* @param \phpbb\user						$user				User object
	* @param \phpbb\request\request				$request				Request object
	* @param \phpbb\cache\driver\driver_interface	$cache				Cache driver interface
	* @param string							$phpbb_root_path		phpbb_root_path
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\user $user)
	{
		$this->config = $config;
		$this->user = $user;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.obtain_users_online_string_modify'			=> 'obtain_users_online_string_modify',
		);
	}

	public function obtain_users_online_string_modify($event)
	{
		$online_users = $event['online_users'];
		$user_online_link = $event['user_online_link'];
		foreach ($event['rowset'] as $row)
		{
			if($row['user_type'] === 2)
			{
				unset($online_users['online_users'][$row['user_id']]);
				unset($user_online_link[$row['user_id']]);
				$online_users['total_online']--;
				$online_users['visible_online']--;
			}
		}

		$visible_online = $this->user->lang('REG_USERS_TOTAL', (int) $online_users['visible_online']);
		$hidden_online = $this->user->lang('HIDDEN_USERS_TOTAL', (int) $online_users['hidden_online']);
		if ($this->config['load_online_guests'])
		{
			$guests_online = $this->user->lang('GUEST_USERS_TOTAL', (int) $online_users['guests_online']);
			$l_online_users = $this->user->lang('ONLINE_USERS_TOTAL_GUESTS', (int) $online_users['total_online'], $visible_online, $hidden_online, $guests_online);
		}
		else
		{
			$l_online_users = $this->user->lang('ONLINE_USERS_TOTAL', (int) $online_users['total_online'], $visible_online, $hidden_online);
		}

		$online_userlist = implode(', ', $user_online_link);

		if (!$online_userlist)
		{
			$online_userlist = $this->user->lang['NO_ONLINE_USERS'];
		}

		$item_caps = strtoupper($event['item']);
		if ($event['item_id'] === 0)
		{
			$online_userlist = $this->user->lang['REGISTERED_USERS'] . ' ' . $online_userlist;
		}
		else if ($this->config['load_online_guests'])
		{
			$online_userlist = $this->user->lang('BROWSING_' . $item_caps . '_GUESTS', $online_users['guests_online'], $online_userlist);
		}
		else
		{
			$online_userlist = sprintf($this->user->lang['BROWSING_' . $item_caps], $online_userlist);
		}

		$event['l_online_users'] = $l_online_users;
		$event['online_userlist'] = $online_userlist;
	}
}
