<?php
namespace GDO\Register;

use GDO\Core\GDO_Module;
use GDO\Date\GDT_Duration;
use GDO\Net\GDT_Url;
use GDO\UI\GDT_Bar;
use GDO\UI\GDT_Page;
use GDO\DB\GDT_Checkbox;
use GDO\DB\GDT_Int;
use GDO\UI\GDT_Link;
use GDO\Session\GDO_Session;
use GDO\Form\GDT_Form;
use GDO\UI\GDT_Button;
use GDO\Mail\GDT_Email;
use GDO\User\GDT_Realname;
use GDO\Core\Module_Core;

/**
 * Registration module.
 * 
 * Users that await activation are stored in a separate table, GDO_UserActivation.
 * This way, usernames or emails don't get burned.
 * 
 * This module features Guest Signup.
 * This module features Email Activation.
 * This module features Instant Activation.
 * This module features Admin Signup Moderation Activation.
 * This module features Terms of Service and Privacy pages.
 *
 * @TODO Guest to Member conversion.
 *
 * @author gizmore
 * @version 6.10
 * @since 3.00
 * 
 * @see Module_ActivationAlert
 * @see GDO_UserActivation
 */
class Module_Register extends GDO_Module
{
    public $module_priority = 40;
    
	##############
	### Module ###
	##############
	public function getDependencies() { return ['Cronjob']; }
	public function getClasses() { return [GDO_UserActivation::class]; }
	public function onLoadLanguage() { $this->loadLanguage('lang/register'); }
	public function href_administrate_module() { return href('Register', 'Admin'); }

	##############
	### Config ###
	##############
	public function getConfig()
	{
		return [
			GDT_Checkbox::make('captcha')->initial('1'),
			GDT_Checkbox::make('guest_signup')->initial('1'),
			GDT_Checkbox::make('email_activation')->initial('1'),
			GDT_Duration::make('email_activation_timeout')->initial("2h")->min(0)->max(31536000),
		    GDT_Checkbox::make('admin_activation')->initial('0'),
		    GDT_Checkbox::make('admin_activation_test')->initial('0'),
		    GDT_Int::make('ip_signup_count')->initial('2')->min(0)->max(100),
			GDT_Duration::make('ip_signup_duration')->initial('24h')->min(0)->max(31536000),
			GDT_Checkbox::make('force_tos')->initial('1'),
			GDT_Url::make('tos_url')->reachable()->allowLocal()->initial(href('Register', 'TOS')),
			GDT_Url::make('privacy_url')->reachable()->allowLocal()->initial(href('Core', 'Privacy')),
			GDT_Checkbox::make('activation_login')->initial('1'),
			GDT_Checkbox::make('signup_password_retype')->initial('1'),
			GDT_Email::make('signup_mail_sender')->initial(GWF_BOT_EMAIL),
			GDT_Realname::make('signup_mail_sender_name')->icon('email')->initial(GWF_BOT_NAME),
		    GDT_Checkbox::make('right_bar')->initial('1'),
		];
	}
	public function cfgCaptcha() { return $this->getConfigValue('captcha'); }
	public function cfgGuestSignup() { return Module_Core::instance()->cfgAllowGuests() && $this->getConfigValue('guest_signup'); }
	public function cfgEmailActivation() { return $this->getConfigValue('email_activation'); }
	public function cfgEmailActivationTimeout() { return $this->getConfigValue('email_activation_timeout'); }
	public function cfgAdminActivation() { return $this->getConfigValue('admin_activation'); }
	public function cfgAdminActivationTest() { return $this->getConfigValue('admin_activation_test'); }
	public function cfgMaxUsersPerIP() { return $this->getConfigValue('ip_signup_count'); }
	public function cfgMaxUsersPerIPTimeout() { return $this->getConfigValue('ip_signup_duration'); }
	public function cfgTermsOfService() { return $this->getConfigValue('force_tos'); }
	public function cfgTosUrl() { return $this->getConfigVar('tos_url'); }
	public function cfgPrivacyUrl() { return $this->getConfigVar('privacy_url'); }
	public function cfgActivationLogin() { return $this->getConfigValue('activation_login'); }
	public function cfgPasswordRetype() { return $this->getConfigValue('signup_password_retype'); }
	public function cfgMailSender() { return $this->getConfigVar('signup_mail_sender'); }
	public function cfgMailSenderName() { return $this->getConfigVar('signup_mail_sender_name'); }
	public function cfgRightBar() { return $this->getConfigValue('right_bar'); }
	
	############
	### Init ###
	############
	public function onInitSidebar()
	{
// 	    if ($this->cfgRightBar())
	    {
    		if (GDO_Session::user()->isGhost())
    		{
    	        $navbar = GDT_Page::$INSTANCE->rightNav;
    			$navbar->addField(GDT_Link::make('btn_register')->href(href('Register', 'Form')));
    		}
	    }
	}

	##################
	### Admin tabs ###
	##################
	public function renderAdminBar()
	{
	    $tabs = GDT_Bar::make()->horizontal();
	    $tabs->addField(GDT_Link::make('link_activations')->href(href('Register', 'Activations')));
	    GDT_Page::$INSTANCE->topTabs->addField($tabs);
	}
	
	##################
	### Form Hooks ###
	##################
	public function hookLoginForm(GDT_Form $form)
	{
	    /** @var $cont \GDO\UI\GDT_Container **/
	    $cont = $form->getField('btncont');
	    $cont->addField(GDT_Button::make('link_register')->secondary()->href(href('Register', 'Form')));
		if ($this->cfgGuestSignup())
		{
			$cont->addField(GDT_Button::make('link_register_guest')->secondary()->href(href('Register', 'Guest')));
		}
	}
	
	public function hookRegisterForm(GDT_Form $form)
	{
		if ($this->cfgGuestSignup())
		{
			$form->addField(GDT_Button::make('link_register_guest')->secondary()->href(href('Register', 'Guest')));
		}
	}
	
	public function hookGuestForm(GDT_Form $form)
	{
		$form->addField(GDT_Button::make('link_register')->secondary()->href(href('Register', 'Form')));
	}
	
}
