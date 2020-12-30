<?php
namespace GDO\Register\Method;

use GDO\Captcha\GDT_Captcha;
use GDO\Core\Application;
use GDO\Core\GDT_Hook;
use GDO\Core\GDO;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Mail\GDT_Email;
use GDO\Mail\Mail;
use GDO\Net\GDT_IP;
use GDO\Register\Module_Register;
use GDO\Register\GDO_UserActivation;
use GDO\Core\GDT;
use GDO\DB\GDT_Checkbox;
use GDO\Date\Time;
use GDO\User\GDT_Password;
use GDO\User\GDT_Username;
use GDO\User\GDO_User;
use GDO\Util\BCrypt;
use GDO\Form\GDT_Validator;
use GDO\Core\GDT_Template;
use GDO\UI\GDT_Panel;
use GDO\Core\GDT_Response;
use GDO\UI\GDT_Message;
use GDO\UI\GDT_Bar;

class Form extends MethodForm
{
	public function isUserRequired() { return false; }
	
	public function getUserType() { return 'ghost'; }
	
	public function renderPage()
	{
	    if (Module_Register::instance()->cfgAdminActivation())
	    {
	        $response = GDT_Response::makeWith(GDT_Panel::make()->html(t('moderation_info')));
	        return $response->add(parent::renderPage());
	    }
	    return parent::renderPage();
	}
	
	public function createForm(GDT_Form $form)
	{
		$module = Module_Register::instance();
		
		if ($module->cfgAdminActivationTest())
		{
		    $form->addField(GDT_Message::make('ua_message')->label('user_signup_text'));
		}
		
		$form->addField(GDT_Username::make('user_name')->required());
		$form->addField(GDT_Validator::make()->validator('user_name', [$this, 'validateUniqueUsername']));
		$form->addField(GDT_Validator::make()->validator('user_name', [$this, 'validateUniqueIP']));
		$form->addField(GDT_Password::make('user_password')->required());
		
		if ($module->cfgPasswordRetype())
		{
			$form->addField(GDT_Password::make('password_retype')->required()->label('password_retype'));
			$form->addField(GDT_Validator::make()->validator('password_retype', [$this, 'validatePasswordRetype']));
		}
		if ($module->cfgEmailActivation())
		{
			$form->addField(GDT_Email::make('user_email')->required());
			$form->addField(GDT_Validator::make()->validator('user_email', [$this, 'validateUniqueEmail']));
		}
		if ($module->cfgTermsOfService())
		{
			$form->addField(GDT_Checkbox::make('tos')->required()->label('tos_label', [$module->cfgTosUrl(), $module->cfgPrivacyURL()]));
			$form->addField(GDT_Validator::make()->validator('tos', [$this, 'validateTOS']));
		}
		if ($module->cfgCaptcha())
		{
			$form->addField(GDT_Captcha::make('captcha'));
		}
		$form->addField(GDT_AntiCSRF::make());

		$cont = GDT_Bar::make('btncont')->horizontal();
		$cont->addField(GDT_Submit::make()->label('btn_register'));
		$form->addField($cont);
		
		GDT_Hook::callHook('RegisterForm', $form);
	}
	
	function validatePasswordRetype(GDT_Form $form, GDT $field)
	{
		if ($field->getVar() !== $form->getField('user_password')->getVar())
		{
			return $field->error('err_password_retype');
		}
		return true;
	}
	
	function validateUniqueIP(GDT_Form $form, GDT $field)
	{
		$ip = GDO::quoteS(GDT_IP::current());
		$cut = Application::$TIME - Module_Register::instance()->cfgMaxUsersPerIPTimeout();
		$cut = Time::getDate($cut);
		$count = GDO_User::table()->countWhere("user_register_ip={$ip} AND user_register_time>'{$cut}'");
		$max = Module_Register::instance()->cfgMaxUsersPerIP();
		return $count < $max ? true : $field->error('err_ip_signup_max_reached', [$max]);
	}
	
	public function validateUniqueUsername(GDT_Form $form, GDT_Username $username, $value)
	{
		$existing = GDO_User::table()->getByName($value);
		return $existing ? $username->error('err_username_taken') : true;
	}

	public function validateUniqueEmail(GDT_Form $form, GDT_Email $email, $value)
	{
		$count = GDO_User::table()->countWhere("user_email=".GDO::quoteS($email->getVar()));
		return $count === 0 ? true : $email->error('err_email_taken');
	}
	
	public function validateTOS(GDT_Form $form, GDT_Checkbox $field)
	{
		return $field->getValue() ? true : $field->error('err_tos_not_checked');
	}
	
	
	public function formInvalid(GDT_Form $form)
	{
		return $this->error('err_register');
	}
	
	public function formValidated(GDT_Form $form)
	{
		return $this->onRegister($form);
	}
	
	################
	### Register ###
	################
	public function onRegister(GDT_Form $form)
	{
		$module = Module_Register::instance();
		
		# TODO: GDT_Password should know it comes from form for a save... b 
		$password = $form->getField('user_password');
		$password->var(BCrypt::create($password->getVar())->__toString());
		
		$activation = GDO_UserActivation::table()->blank($form->getFormData());
		$activation->setVar('user_register_ip', GDT_IP::current());
		$activation->save();
		
		if ($module->cfgEmailActivation())
		{
			return $this->onEmailActivation($activation);
		}
		else
		{
			return Activate::make()->activate($activation->getID(), $activation->getToken());
		}
	}
	
	public function onEmailActivation(GDO_UserActivation $activation)
	{
		$module = Module_Register::instance();
		$mail = new Mail();
		$mail->setSubject(t('mail_activate_subj', [sitename()]));
		$body = $this->getMailBody($activation);
// 		$args = array($activation->getUsername(), sitename(), $activation->getUrl());
		$mail->setBody($body);
		$mail->setSender($module->cfgMailSender());
		$mail->setSenderName($module->cfgMailSenderName());
		$mail->setReceiver($activation->getEmail());
		$mail->sendAsHTML();
		return $this->message('msg_activation_mail_sent');
	}
	
	public function getMailBody(GDO_UserActivation $activation)
	{
		$tVars = array(
			'username' => $activation->getUsername(),
			'activation_url' => $activation->getUrl(),
		);
		return GDT_Template::php('Register', 'mail/activate.php', $tVars);
	}
	
}
