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
        # register only works as ghost
        $this->userGhost();
        
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
        assertEquals(200, $response->code, "Check if registration works");
    }
    
    public function testGuest()
    {
        # Another attempt which will not work.
        $this->userGhost();
        
        $method = Guest::make();
        $parameters = ['user_guest_name' => 'Casper'];
        MethodTest::make()->method($method)->parameters($parameters)->execute();
        assertEquals(GDT_Response::$CODE, 200);
        
        MethodTest::$USERS[] = $user = GDO_User::$CURRENT;
        assertEquals('Casper', $user->getGuestName(), 'Check if guest register was success.');
        
        $user = Module_Core::instance()->cfgSystemUser();
        assertEquals('system', $user->getType(), 'Check if system user is still there.');
    }
    
    public function testTOSFailed()
    {
        # Another attempt which will not work.
        $this->userGhost();
        
        $module = Module_Register::instance();
        $module->saveConfigValue('force_tos', true);
        $method = Form::make();
        $parameters = [
            'user_name' => 'Peter2',
            'user_password' => '11111111',
            'tos' => '2', # 2 is undetermined.
        ];
        MethodTest::make()->method($method)->parameters($parameters)->execute();
        assertNotNull($method->getForm()->getField('tos')->error, 'Check if ToS checkbox prevents signup.');
    }
    
}
