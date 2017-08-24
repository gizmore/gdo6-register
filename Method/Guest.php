<?php
namespace GDO\Register\Method;

use GDO\Captcha\GDO_Captcha;
use GDO\Core\Application;
use GDO\Core\GDO_Hook;
use GDO\Date\Time;
use GDO\Form\GDO_AntiCSRF;
use GDO\Form\GDO_Form;
use GDO\Form\GDO_Submit;
use GDO\Form\MethodForm;
use GDO\Net\GDO_IP;
use GDO\Register\Module_Register;
use GDO\User\GDO_Username;
use GDO\User\User;
use GDO\Form\GDO_Validator;

class Guest extends MethodForm
{
    public function isUserRequired() { return false; }
    
    public function getUserType() { return 'ghost'; }
	
	public function isEnabled()
	{
		return Module_Register::instance()->cfgGuestSignup();
	}
	
	public function createForm(GDO_Form $form)
	{
		$form->addField(GDO_Username::make('user_guest_name')->required());
		$form->addField(GDO_Validator::make()->validator('user_guest_name', [$this, 'validateGuestNameTaken']));
		if (Module_Register::instance()->cfgCaptcha())
		{
			$form->addField(GDO_Captcha::make());
		}
		$form->addField(GDO_Submit::make()->label('btn_signup_guest'));
		$form->addField(GDO_AntiCSRF::make());
		GDO_Hook::call('GuestForm', $form);
	}

	public function validateGuestNameTaken(GDO_Form $form, GDO_Username $field, $value)
	{
	    if (User::table()->countWhere('user_guest_name='.quote($value)))
	    {
	        return $field->error('err_guest_name_taken');
	    }
	    return true;
	}
	
	public function formValidated(GDO_Form $form)
	{
		$user = User::table()->blank($form->getFormData());
		$user->setVars(array(
			'user_type' => User::GUEST,
			'user_register_ip' => GDO_IP::current(),
			'user_register_time' => Time::getDate(),
		));
		$user->insert();
		
		$authResponse = \GDO\Login\Method\Form::make()->loginSuccess($user);

		GDO_Hook::call('UserActivated', $user);
		
		return $this->message('msg_registered_as_guest', [$user->displayName()])->add($authResponse);
	}
}
