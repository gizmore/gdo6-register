<?php
namespace GDO\Register\Method;

use GDO\Captcha\GDO_Captcha;
use GDO\Core\GDO_Hook;
use GDO\DB\GDO;
use GDO\Form\GDO_AntiCSRF;
use GDO\Form\GDO_Form;
use GDO\Form\GDO_Submit;
use GDO\Form\MethodForm;
use GDO\Mail\GDO_Email;
use GDO\Mail\Mail;
use GDO\Net\GDO_IP;
use GDO\Register\Module_Register;
use GDO\Register\UserActivation;
use GDO\Template\Message;
use GDO\Type\GDO_Base;
use GDO\Type\GDO_Checkbox;
use GDO\Type\GDO_Password;
use GDO\User\GDO_Username;
use GDO\User\User;
use GDO\Form\GDO_Validator;

class Form extends MethodForm
{
    public function isUserRequired() { return false; }
    
    public function getUserType() { return 'ghost'; }
	
	public function createForm(GDO_Form $form)
	{
		$module = Module_Register::instance();
		$form->addField(GDO_Username::make('user_name')->required());
		$form->addField(GDO_Validator::make()->validator('user_name', [$this, 'validateUniqueUsername']));
		$form->addField(GDO_Validator::make()->validator('user_name', [$this, 'validateUniqueIP']));
		$form->addField(GDO_Password::make('user_password')->required());
		if ($module->cfgEmailActivation())
		{
		    $form->addField(GDO_Email::make('user_email')->required());
		    $form->addField(GDO_Validator::make()->validator('user_email', [$this, 'validateUniqueEmail']));
		}
		if ($module->cfgTermsOfService())
		{
			$form->addField(GDO_Checkbox::make('tos')->required()->label('tos_label', [$module->cfgTosUrl()]));
		}
		if ($module->cfgCaptcha())
		{
			$form->addField(GDO_Captcha::make('captcha'));
		}
		$form->addField(GDO_Submit::make()->label('btn_register'));
		$form->addField(GDO_AntiCSRF::make());
		
		GDO_Hook::call('RegisterForm', $form);
	}
	
	function validateUniqueIP(GDO_Form $form, GDO_Base $field)
	{
		$ip = GDO::quoteS(GDO_IP::current());
		$cut = time() - Module_Register::instance()->cfgMaxUsersPerIPTimeout();
		$count = User::table()->countWhere("user_register_ip={$ip} AND user_register_time>{$cut}");
		$max = Module_Register::instance()->cfgMaxUsersPerIP();
		return $count < $max ? true : $field->error('err_ip_signup_max_reached', [$max]);
	}
	
	public function validateUniqueUsername(GDO_Form $form, GDO_Username $username, $value)
	{
	    $existing = User::table()->getByName($value);
		return $existing ? $username->error('err_username_taken') : true;
	}

	public function validateUniqueEmail(GDO_Form $form, GDO_Email $email, $value)
	{
		$count = User::table()->countWhere("user_email={$email->quotedValue()}");
		return $count === 0 ? true : $email->error('err_email_taken');
	}
	
	public function formInvalid(GDO_Form $form)
	{
		return $this->error('err_register');
	}
	
	public function formValidated(GDO_Form $form)
	{
		return $this->onRegister($form);
	}
	
	################
	### Register ###
	################
	public function onRegister(GDO_Form $form)
	{
		$module = Module_Register::instance();
		
		$activation = UserActivation::table()->blank($form->getFormData());
		$activation->setVar('user_register_ip', GDO_IP::current());
		$activation->save();
		
		if ($module->cfgEmailActivation())
		{
			return $this->onEmailActivation($activation);
		}
		else
		{
			return new Message('msg_activating', [$activation->getHref()]);
		}
	}
	
	public function onEmailActivation(UserActivation $activation)
	{
		$mail = new Mail();
		$mail->setSubject(t('mail_activate_title', [sitename()]));
		$args = array($activation->getUsername(), sitename(), $activation->getUrl());
		$mail->setBody(t('mail_activate_body', $args));
		$mail->setSender(GWF_BOT_EMAIL);
		$mail->setReceiver($activation->getEmail());
		$mail->sendAsHTML();
		return new Message('msg_activation_mail_sent');
	}
	
}
