<?php
namespace GDO\Register\Method;

use GDO\Core\Method;
use GDO\Register\GDO_UserActivation;
use GDO\Util\Common;
use GDO\User\GDO_User;

final class AdminActivate extends Method
{
	public function getPermission() { return 'staff'; }
	public function execute()
	{
		$activation = GDO_UserActivation::table()->find(Common::getGetString('id'));
		
		# Activate wrapped in user change
		$me  = GDO_User::current();
		GDO_User::$CURRENT = GDO_User::ghost();
		$user = Activate::make()->activateToken($activation);
		GDO_User::$CURRENT = $me;
		
		return $this->message('msg_user_activated', [$user->displayNameLabel()]);
	}
}
