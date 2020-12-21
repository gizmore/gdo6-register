<?php
namespace GDO\Register\Test;

use GDO\Register\Method\Form;
use GDO\Tests\MethodTest;
use GDO\Tests\TestCase;
use GDO\Register\Module_Register;
use GDO\Register\Method\Guest;
use GDO\User\GDO_User;
use GDO\Core\Module_Core;
use function PHPUnit\Framework\assertEquals;
use GDO\Core\GDT_Response;
use function PHPUnit\Framework\assertNotNull;

final class RegisterTest extends TestCase
{
    public function testSuccess()
    {
        # Config for easy registration
        $module = Module_Register::instance();
        $module->saveConfigValue('signup_password_retype', false);
        $module->saveConfigValue('email_activation', false);
        $module->saveConfigValue('admin_activation', false);
        $module->saveConfigValue('activation_login', false);
        $module->saveConfigValue('force_tos', false);
        $method = Form::make();
        $parameters = [
            'user_name' => 'Peter1',
            'user_password' => '11111111',
        ];
        $response = MethodTest::make()->method($method)->parameters($parameters)->execute();
        assert($response->code === 200);
    }
    
    public function testGuest()
    {
        GDO_User::$CURRENT = GDO_User::ghost();
        
        $method = Guest::make();
        $parameters = ['user_guest_name' => 'Casper'];
        MethodTest::make()->method($method)->parameters($parameters)->execute();
        assertEquals(GDT_Response::$CODE, 200);
        
        MethodTest::$USERS[] = $user = GDO_User::$CURRENT;
        assertEquals('Casper', $user->getGuestName(), 'Check if guest register was success.');
        
        GDO_User::$CURRENT = Module_Core::instance()->cfgSystemUser();
        assertEquals('system', $user->getType(), 'Check if system user is still there.');
    }
    
    public function testTOSFailed()
    {
        $module = Module_Register::instance();
        $module->saveConfigValue('force_tos', true);
        $method = Form::make();
        $parameters = [
            'user_name' => 'Peter2',
            'user_password' => '11111111',
        ];
        MethodTest::make()->method($method)->parameters($parameters)->execute();
        assertNotNull($method->gdoParameter('tos')->error, 'Check if ToS checkbox prevents signup.');
    }
    
}
