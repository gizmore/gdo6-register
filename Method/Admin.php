<?php
namespace GDO\Register\Method;

use GDO\Core\Method;
use GDO\Core\MethodAdmin;
use GDO\Core\GDT_Response;
use GDO\UI\GDT_Bar;
use GDO\UI\GDT_Link;

/**
 * Show a menu of admin options for the register module.
 * @author gizmore
 */
final class Admin extends Method
{
	use MethodAdmin;
	public function execute()
	{
		# Admin menu
		$menu = GDT_Bar::make()->horizontal();
		$menu->addField(GDT_Link::make('link_register_activations')->href(href('Register', 'Activations')));
		$response = GDT_Response::makeWith($menu);
		
		# Tabs first
		$tabs = $this->renderNavBar('Register');
		return $tabs->add($response);
	}

}
