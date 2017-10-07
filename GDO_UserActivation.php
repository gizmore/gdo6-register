<?php
namespace GDO\Register;

use GDO\Core\GDO;
use GDO\DB\GDT_AutoInc;
use GDO\DB\GDT_CreatedAt;
use GDO\Mail\GDT_Email;
use GDO\Net\GDT_IP;
use GDO\User\GDT_Password;
use GDO\DB\GDT_Token;
use GDO\User\GDT_Username;
use GDO\Net\GDT_Url;

class GDO_UserActivation extends GDO
{
	public function gdoCached() { return false; }
	public function gdoColumns()
	{
		return array(
			GDT_AutoInc::make('ua_id'),
			GDT_Token::make('ua_token')->notNull(),
			GDT_CreatedAt::make('ua_time')->notNull(),

			# We copy these fields to user table
			GDT_Username::make('user_name')->notNull(),
			GDT_Password::make('user_password')->notNull(),
			GDT_Email::make('user_email'),
			GDT_IP::make('user_register_ip')->notNull(),
		);
	}
	
	public function getID() { return $this->getVar('ua_id'); }
	public function getToken() { return $this->getVar('ua_token'); }
	public function getEmail() { return $this->getVar('user_email'); }
	public function getUsername() { return $this->getVar('user_name'); }
	
	public function getHref() { return href('Register', 'Activate', "&id={$this->getID()}&token={$this->getToken()}"); }
	public function getUrl() { return GDT_Url::absolute($this->getHref()); }
	
	public function href_btn_activate() { return href('Register', 'AdminActivate', '&id='.$this->getID()); }
	
	public function displayNameLabel() { return $this->getVar('user_name'); }
	
}
