<?php
namespace GDO\Register\Method;

use GDO\Core\Method;
use GDO\Register\GDO_UserActivation;
use GDO\Util\Common;

final class AdminActivate extends Method
{
    public function getPermission() { return 'staff'; }
	public function execute()
	{
	    $activation = GDO_UserActivation::table()->find(Common::getGetString('id'));
		$user = Activate::make()->activateToken($activation);
		return $this->message('msg_user_activated', [$user->displayNameLabel()]);
	}
}
