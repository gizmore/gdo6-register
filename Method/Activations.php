<?php
namespace GDO\Register\Method;

use GDO\Core\MethodAdmin;
use GDO\Register\GDO_UserActivation;
use GDO\Table\MethodQueryTable;
use GDO\UI\GDT_Button;
use GDO\Register\Module_Register;

final class Activations extends MethodQueryTable
{
	use MethodAdmin;
	
	public function beforeExecute()
	{
	    $this->renderNavBar();
	    Module_Register::instance()->renderAdminBar();
	}
	
	public function getQuery()
	{
		return GDO_UserActivation::table()->select();
	}
	
	public function getHeaders()
	{
		$gdo = GDO_UserActivation::table();
		return array(
		    GDT_Button::make('btn_activate'),
		    $gdo->gdoColumn('ua_time'),
			$gdo->gdoColumn('user_name'),
			$gdo->gdoColumn('user_register_ip'),
			$gdo->gdoColumn('user_email'),
		    $gdo->gdoColumn('ua_email_confirmed'),
		);
	}
	
}
