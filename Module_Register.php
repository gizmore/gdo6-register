<?php
namespace GDO\Register;

use GDO\Core\Module;
use GDO\Date\GDO_Duration;
use GDO\Net\GDO_Url;
use GDO\Template\GDO_Bar;
use GDO\Type\GDO_Checkbox;
use GDO\Type\GDO_Int;
use GDO\UI\GDO_Link;
use GDO\User\Session;
use GDO\User\User;
use GDO\Form\GDO_Form;
/**
 * Registration module.
 *
 * @author gizmore
 * @version 5.0
 * @since 1.0
 */
class Module_Register extends Module
{
	##############
	### Module ###
	##############
	public function isCoreModule() { return true; }
	public function getClasses() { return array('GDO\Register\UserActivation'); }
	public function onLoadLanguage() { $this->loadLanguage('lang/register'); }
	public function href_administrate_module() { return href('Register', 'Admin'); }

	##############
	### Config ###
	##############
	public function getConfig()
	{
		return array(
			GDO_Checkbox::make('captcha')->initial('0'),
			GDO_Checkbox::make('guest_signup')->initial('1'),
			GDO_Checkbox::make('email_activation')->initial('1'),
		    GDO_Duration::make('email_activation_timeout')->initial('72600')->min(0)->max(31536000),
		    GDO_Checkbox::make('admin_activation')->initial('0'),
			GDO_Int::make('ip_signup_count')->initial('1')->min(0)->max(100),
			GDO_Duration::make('ip_signup_duration')->initial('72600')->min(0)->max(31536000),
			GDO_Checkbox::make('force_tos')->initial('1'),
			GDO_Url::make('tos_url')->reachable()->allowLocal()->initial($this->getMethodHREF('TOS')),
			GDO_Checkbox::make('activation_login')->initial('1'),
		);
	}
	public function cfgCaptcha() { return $this->getConfigValue('captcha'); }
	public function cfgGuestSignup() { return $this->getConfigValue('guest_signup'); }
	public function cfgEmailActivation() { return $this->getConfigValue('email_activation'); }
	public function cfgEmailActivationTimeout() { return $this->getConfigValue('email_activation_timeout'); }
	public function cfgAdminActivation() { return $this->getConfigValue('admin_activation'); }
	public function cfgMaxUsersPerIP() { return $this->getConfigValue('ip_signup_count'); }
	public function cfgMaxUsersPerIPTimeout() { return $this->getConfigValue('ip_signup_duration'); }
	public function cfgTermsOfService() { return $this->getConfigValue('force_tos'); }
	public function cfgTosUrl() { return $this->getConfigValue('tos_url'); }
	public function cfgActivationLogin() { return $this->getConfigValue('activation_login'); }
	################
	### Top Menu ###
	################
	public function hookRightBar(GDO_Bar $navbar)
	{
		if (Session::user()->isGhost())
		{
			$navbar->addField(GDO_Link::make('btn_register')->href($this->getMethodHREF('Form')));
		}
	}
	
	public function hookGuestForm(GDO_Form $form)
	{
	    $form->addField(GDO_Link::make('link_register')->href(href('Register', 'Form')));
	}
	
	public function hookLoginForm(GDO_Form $form)
	{
	    $form->addField(GDO_Link::make('link_register')->href(href('Register', 'Form')));
	    $form->addField(GDO_Link::make('link_register_guest')->href(href('Register', 'Guest')));
	}
	
	public function hookRegisterForm(GDO_Form $form)
	{
	    $form->addField(GDO_Link::make('link_register_guest')->href(href('Register', 'Guest')));
	}
	
}
