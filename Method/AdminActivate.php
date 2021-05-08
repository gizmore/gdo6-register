<?php
namespace GDO\Register\Method;

use GDO\Core\GDT_Template;
use GDO\Core\Method;
use GDO\Core\MethodAdmin;
use GDO\Register\GDO_UserActivation;
use GDO\Util\Common;
use GDO\Register\Module_Register;
use GDO\User\GDO_User;
use GDO\Language\Trans;
use GDO\Mail\Mail;

final class AdminActivate extends Method
{
    use MethodAdmin;
    
    public function getPermission() { return 'staff'; }
    
    public function beforeExecute()
    {
        $this->renderNavBar();
        Module_Register::instance()->renderAdminBar();
    }
	
	public function execute()
	{
		$activation = GDO_UserActivation::table()->find(Common::getGetString('id'));
		
		if ($activation->isDeleted())
		{
		    return $this->message('msg_already_activated');
		}
		
		# Activate
		$user = Activate::make()->activateToken($activation);
		
		$this->sendMail($user);
		
		return $this->message('msg_user_activated', [$user->displayNameLabel()]);
	}
	
	private function sendMail(GDO_User $user)
	{
	    $module = Module_Register::instance();
	    $mail = new Mail();
	    $mail->setSubject(tusr($user, 'mail_activate2_subj', [sitename()]));
	    $body = $this->getMailBody($user);
	    $mail->setBody($body);
	    $mail->setSender($module->cfgMailSender());
	    $mail->setSenderName($module->cfgMailSenderName());
	    $mail->sendToUser($user);
	}
	
	public function getMailBody(GDO_User $user)
	{
	    $tVars = array(
	        'username' => $user->displayNameLabel(),
	        'admin' => GDO_User::current()->displayNameLabel(),
	        'url' => url(GDO_MODULE, GDO_METHOD),
	    );
	    $old = Trans::$ISO;
	    Trans::setISO($user->getLangISO());
	    $body = GDT_Template::php('Register', 'mail/activate2.php', $tVars);
	    Trans::setISO($old);
	    return $body;
	}
	
}
