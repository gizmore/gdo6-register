<?php
namespace GDO\Register\Method;

use GDO\Captcha\GDT_Captcha;
use GDO\Core\GDT_Hook;
use GDO\Date\Time;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Net\GDT_IP;
use GDO\Register\Module_Register;
use GDO\User\GDT_Username;
use GDO\User\GDO_User;
use GDO\Form\GDT_Validator;

class Guest extends MethodForm
{
    public function isUserRequired() { return false; }
    
    public function getUserType() { return 'ghost'; }
	
	public function isEnabled()
	{
		return Module_Register::instance()->cfgGuestSignup();
	}
	
	public function createForm(GDT_Form $form)
	{
		$form->addField(GDT_Username::make('user_guest_name')->required());
		$form->addField(GDT_Validator::make()->validator('user_guest_name', [$this, 'validateGuestNameTaken']));
		if (Module_Register::instance()->cfgCaptcha())
		{
			$form->addField(GDT_Captcha::make());
		}
		$form->addField(GDT_Submit::make()->label('btn_signup_guest'));
		$form->addField(GDT_AntiCSRF::make());
		GDT_Hook::call('GuestForm', $form);
	}

	public function validateGuestNameTaken(GDT_Form $form, GDT_Username $field, $value)
	{
	    if (GDO_User::table()->countWhere('user_guest_name='.quote($value)))
	    {
	        return $field->error('err_guest_name_taken');
	    }
	    return true;
	}
	
	public function formValidated(GDT_Form $form)
	{
		$user = GDO_User::table()->blank($form->getFormData());
		$user->setVars(array(
			'user_type' => GDO_User::GUEST,
			'user_register_ip' => GDT_IP::current(),
			'user_register_time' => Time::getDate(),
		));
		$user->insert();
		
		$authResponse = \GDO\Login\Method\Form::make()->loginSuccess($user);

		GDT_Hook::call('UserActivated', $user);
		
		return $this->message('msg_registered_as_guest', [$user->displayName()])->add($authResponse);
	}
}