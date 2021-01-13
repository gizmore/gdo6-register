<?php
namespace GDO\Register\Method;

use GDO\Core\GDT_Hook;
use GDO\Core\GDT_Template;
use GDO\Core\Method;
use GDO\Core\GDO;
use GDO\Register\Module_Register;
use GDO\Register\GDO_UserActivation;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\Login\Method\Form;
use GDO\DB\GDT_String;
use GDO\DB\GDT_Checkbox;
use GDO\Date\Time;
use GDO\Mail\Mail;
use GDO\Language\Trans;

/**
 * Activate a user via token.
 * Call activation from other activators as well.
 * @author gizmore
 * @version 6.10    
 * @since 3.04
 */
class Activate extends Method
{
	public function gdoParameters()
	{
		return array(
			GDT_String::make('id')->notNull(),
			GDT_String::make('token')->notNull(),
		    GDT_Checkbox::make('convert_guest')->initial('1')->notNull(),
		);
	}
	
	public function convertGuest() { return $this->gdoParameterValue('convert_guest'); }
	
	public function execute()
	{
		return $this->activate(Common::getRequestString('id'), Common::getRequestString('token'));
	}
	
	public function activate($id, $token)
	{
	    # Check token
		$id = GDO::quoteS($id);
		$token = GDO::quoteS($token);
		$convert = $this->convertGuest();
		if (!($activation = GDO_UserActivation::table()->findWhere("ua_id={$id} AND ua_token={$token}")))
		{
			return $this->error('err_token');
		}

		# Check deleted activation.
		if ($activation->isDeleted())
		{
		    GDT_Hook::callHook('AlreadyActivated');
		    return $this->message('msg_already_activated');
		}
		
		if ($activation->isConfirmed())
		{
		    GDT_Hook::callHook('AlreadyActivated');
		    return $this->message('msg_already_confirmed');
		}
		
		# Mail confirmed
		$activation->saveVars([
		    'ua_email_confirmed' => Time::getDate(),
		]);
		
		
		# Moderate
		if (Module_Register::instance()->cfgAdminActivation())
		{
		    $this->message('msg_registration_confirmed_but_moderation');
		    $this->sendModerationMails($activation);
		}
		else
		{
    		# Activate
		    $user = $this->activateToken($activation, $convert);
    		$this->message('msg_activated', [$user->displayName()]);
    
    		if ($convert)
    		{
        		# Login after Activation
        		if (Module_Register::instance()->cfgActivationLogin())
        		{
        			return Form::make()->loginSuccess($user);
        		}
    		}
		}
			
	}
	
	/**
	 * Optionally turn current user into member for instant and mail.
	 * Do not for admin moderation.
	 * @param GDO_UserActivation $activation
	 * @return \GDO\User\GDO_User
	 */
	public function activateToken(GDO_UserActivation $activation, $convertGuest=false)
	{
	    $activation->markDeleted();
	    
	    if ($convertGuest)
	    {
    	    $user = GDO_User::current();
    	    $user->setVars($activation->getGDOVars());
	    }
	    else
	    {
	        $user = GDO_User::blank($activation->getGDOVars());
	    }

	    $user->setVar('user_type', 'member');
	    
	    $user->save();
	    
	    GDT_Hook::callWithIPC('UserActivated', $user);
	    
	    return $user;
	}
	
	############
	### Mail ###
	############
	private function sendModerationMails(GDO_UserActivation $activation)
	{
	    foreach (GDO_User::staff() as $user)
	    {
	        $this->sendModerationMail($activation, $user);
	    }
	}
	
	private function sendModerationMail(GDO_UserActivation $activation, GDO_User $user)
	{
	    $module = Module_Register::instance();
	    $mail = new Mail();
	    $mail->setSubject(tusr($user, 'mail_moderate_subj', [sitename()]));
	    $body = $this->getMailBody($activation, $user);
	    $mail->setBody($body);
	    $mail->setSender($module->cfgMailSender());
	    $mail->setSenderName($module->cfgMailSenderName());
	    $mail->sendToUser($user);
	}
	
	public function getMailBody(GDO_UserActivation $activation, GDO_User $user)
	{
	    $tVars = array(
	        'username' => $user->displayNameLabel(),
	        'nick' => $activation->getUsername(),
	        'email' => $activation->getEmail(),
	        'ip' => $activation->getIP(),
	        'message' => $activation->getMessage(),
	    );
	    
	    $old = Trans::$ISO;
	    Trans::setISO($user->getLangISO());
	    $body = GDT_Template::php('Register', 'mail/moderation.php', $tVars);
	    Trans::setISO($old);
	    return $body;
	}
	
}
