<?php
namespace GDO\Register\Test;

use PHPUnit\Framework\TestCase;
use GDO\Register\Method\Form;
use GDO\Tests\MethodTest;
use GDO\Register\Module_Register;
use GDO\Register\Method\Guest;

final class TestRegister extends TestCase
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
        $method = Guest::make();
        $response = MethodTest::make()->method($method)->parameters($parameters)->execute();
        
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
        $response = MethodTest::make()->method($method)->parameters($parameters)->execute();
        assert($method->gdoParameter('tos')->error);
    }
    
}
