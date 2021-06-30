<?php
namespace GDO\Register;

use GDO\Core\GDO;
use GDO\Core\GDT_Serialize;
use GDO\DB\GDT_AutoInc;
use GDO\DB\GDT_CreatedAt;
use GDO\Mail\GDT_Email;
use GDO\Net\GDT_IP;
use GDO\User\GDT_Password;
use GDO\DB\GDT_Token;
use GDO\User\GDT_Username;
use GDO\Net\GDT_Url;
use GDO\DB\GDT_DeletedAt;
use GDO\Date\GDT_DateTime;
use GDO\UI\GDT_Message;
use GDO\Language\GDT_Language;
use GDO\Language\Trans;

/**
 * User activation table.
 * @author gizmore
 */
class GDO_UserActivation extends GDO
{
	public function gdoCached() { return false; }
	public function gdoColumns()
	{
		return array(
			GDT_AutoInc::make('ua_id'),
			GDT_Token::make('ua_token')->notNull(),
			GDT_CreatedAt::make('ua_time')->notNull(),
			GDT_DeletedAt::make('ua_deleted'),
		    GDT_DateTime::make('ua_email_confirmed'),
		    
		    GDT_Message::make('ua_message'),

			# We copy these fields to user table
	        GDT_Language::make('user_language')->initial(Trans::$ISO),
			GDT_Username::make('user_name')->notNull(),
			GDT_Password::make('user_password')->notNull(),
			GDT_Email::make('user_email'),
			GDT_IP::make('user_register_ip')->notNull(),
		    
		    GDT_Serialize::make('ua_data'),
		);
	}
	
	public function getID() { return $this->getVar('ua_id'); }
	public function getIP() { return $this->getVar('user_register_ip'); }
	public function getToken() { return $this->getVar('ua_token'); }
	public function getEmail() { return $this->getVar('user_email'); }
	public function getUsername() { return $this->getVar('user_name'); }
	public function getMessage() { return $this->getVar('ua_message'); }
	public function isConfirmed() { return $this->getVar('ua_email_confirmed') !== null; }
	public function isDeleted() { return $this->getVar('ua_deleted') !== null; }
	
	public function getHref() { return href('Register', 'Activate', "&id={$this->getID()}&token={$this->getToken()}&convert_guest=1"); }
	public function getUrl() { return GDT_Url::absolute($this->getHref()); }
	
	public function href_btn_activate() { return href('Register', 'AdminActivate', '&id='.$this->getID()); }
	
	public function displayNameLabel() { return $this->getVar('user_name'); }
	
}
