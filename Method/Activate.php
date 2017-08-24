<?php
namespace GDO\Register\Method;

use GDO\Core\GDO_Hook;
use GDO\Core\Method;
use GDO\DB\GDO;
use GDO\Register\Module_Register;
use GDO\Register\UserActivation;
use GDO\User\User;
use GDO\Util\Common;
use GDO\Login\Method\Form;

class Activate extends Method
{
	public function execute()
	{
		return $this->activate(Common::getRequestString('id'), Common::getRequestString('token'));
	}
	
	public function activate(string $id, string $token)
	{
		$id = GDO::quoteS($id);
		$token = GDO::quoteS($token);
		if (!($activation = UserActivation::table()->findWhere("ua_id={$id} AND ua_token={$token}")))
		{
			return $this->error('err_no_activation');
		}
		$activation->delete();
		
		$user = User::table()->blank($activation->getGDOVars());
		$user->setVars(array(
			'user_type' => 'member',
		));
		$user->insert();
		
		$response = $this->message('msg_activated', [$user->displayName()]);
		
		GDO_Hook::call('UserActivated', $user);
		
		if (Module_Register::instance()->cfgActivationLogin())
		{
			Form::make()->loginSuccess($user);
			$response->add($this->message('msg_authenticated', [$user->displayName()]));
		}
		
		return $response;
	}
	
}
