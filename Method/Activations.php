<?php
namespace GDO\Register\Method;

use GDO\Core\MethodAdmin;
use GDO\Register\GDO_UserActivation;
use GDO\Table\MethodQueryTable;
use GDO\UI\GDT_Button;
use GDO\Register\Module_Register;

/**
 * Open user activations for staff.
 * @author gizmore
 * @version 6.10
 * @since 6.06
 */
final class Activations extends MethodQueryTable
{
	use MethodAdmin;
	
	public function beforeExecute()
	{
	    $this->renderNavBar();
	    Module_Register::instance()->renderAdminBar();
	}
	
	public function gdoTable()
	{
	    return GDO_UserActivation::table();
	}
	
	public function getQuery()
	{
		return GDO_UserActivation::table()->select();
	}
	
	public function gdoHeaders()
	{
		$gdo = $this->gdoTable();
		return [
		    GDT_Button::make('btn_activate'),
		    $gdo->gdoColumn('ua_time'),
			$gdo->gdoColumn('user_name'),
			$gdo->gdoColumn('user_register_ip'),
			$gdo->gdoColumn('user_email'),
		    $gdo->gdoColumn('ua_email_confirmed'),
		];
	}
	
	public function getTitleLangKey() { return 'link_activations'; }

}
