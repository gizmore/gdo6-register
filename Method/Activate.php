<?php
namespace GDO\Register\Method;

use GDO\Core\GDT_Hook;
use GDO\Core\Method;
use GDO\Core\GDO;
use GDO\Register\Module_Register;
use GDO\Register\GDO_UserActivation;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\Login\Method\Form;

class Activate extends Method
{
	public function execute()
	{
		return $this->activate(Common::getRequestString('id'), Common::getRequestString('token'));
	}
	
	public function activateToken(GDO_UserActivation $activation)
	{
		$activation->markDeleted();
		$user = GDO_User::current();
		$user->setVars($activation->getGDOVars());
		$user->setVar('user_type', 'member');
		$user->save();
		GDT_Hook::callWithIPC('UserActivated', $user);
		return $user;
	}
	
	public function activate($id, $token)
	{
		$id = GDO::quoteS($id);
		$token = GDO::quoteS($token);
		if (!($activation = GDO_UserActivation::table()->findWhere("ua_id={$id} AND ua_token={$token}")))
		{
			return $this->error('err_no_activation');
		}
		
		if ($activation->isDeleted())
		{
			GDT_Hook::callHook('AlreadyActivated');
			return $this->message('msg_already_activated');
		}
		
		$user = $this->activateToken($activation);
		
		$response = $this->message('msg_activated', [$user->displayName()]);
		
		GDT_Hook::callWithIPC('UserAvtivated', $user);
		
		if (Module_Register::instance()->cfgActivationLogin())
		{
			Form::make()->loginSuccess($user);
			$response->add($this->message('msg_authenticated', [$user->displayName()]));
		}
		
		return $response;
	}
	
}
