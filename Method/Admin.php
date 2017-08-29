<?php
namespace GDO\Register\Method;

use GDO\Admin\MethodAdmin;
use GDO\Register\UserActivation;
use GDO\Table\MethodQueryTable;
use GDO\UI\GDT_Button;

final class Admin extends MethodQueryTable
{
	use MethodAdmin;
	public function execute()
	{
		$response = parent::execute();
		$tabs = $this->renderNavBar('Register');
		return $tabs->add($response);
	}

	public function getQuery()
	{
		return UserActivation::table()->select('*');
	}
	
	public function getHeaders()
	{
		$gdo = UserActivation::table();
		return array(
			GDT_Button::make('btn_activate'),
			$gdo->gdoColumn('ua_time'),
			$gdo->gdoColumn('user_name'),
			$gdo->gdoColumn('user_register_ip'),
			$gdo->gdoColumn('user_email'),
		);
	}
}
