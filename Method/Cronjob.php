<?php
namespace GDO\Register\Method;

use GDO\Core\MethodCronjob;
use GDO\DB\Database;
use GDO\Date\Time;
use GDO\Register\Module_Register;
use GDO\Register\GDO_UserActivation;

final class Cronjob extends MethodCronjob
{
    public function run()
    {
        $module = Module_Register::instance();
        if (0 != ($timeout = $module->cfgEmailActivationTimeout()))
        {
            $cut = Time::getDate(time() - $timeout);
            GDO_UserActivation::table()->deleteWhere("ua_time < '$cut'")->exec();
            if ($affected = Database::instance()->affectedRows())
            {
                $this->logNotice("Deleted $affected old user activations.");
            }
        }
    }
}
