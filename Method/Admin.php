<?php
namespace GDO\Register\Method;

use GDO\Core\Method;
use GDO\Core\MethodAdmin;
use GDO\Register\Module_Register;

/**
 * Show a menu of admin options for the register module.
 * @author gizmore
 */
final class Admin extends Method
{
	use MethodAdmin;
	
	public function beforeExecute()
	{
	    $this->renderNavBar('Register');
	    Module_Register::instance()->renderAdminBar();
	}
	
    public function execute()
    {
        # Intentionally left clear atm.
    }
	
}
