<?php
namespace GDO\Register\Method;

use GDO\Core\Method;

final class TOS extends Method
{
	public function execute()
	{
		return $this->templatePHP('tos.php');
	}
}