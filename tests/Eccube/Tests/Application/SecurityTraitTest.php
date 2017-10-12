<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Eccube\Tests\Application;

use Eccube\Application;
use Eccube\Tests\EccubeTestCase;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\User;

/**
 * SecurityTrait test cases.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @requires PHP 5.4
 */
class SecurityTraitTest extends EccubeTestCase
{
    public function testUser()
    {
        // FIXME 正しい EncodePassword が実装されたら有効にする
        $this->markTestIncomplete('EncodePassword is not implemented.');
        $request = Request::create('/');

        $app = $this->createApplication(array(
            'fabien' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
        ));
        $app->get('/', function () { return 'foo'; });
        $app->handle($request);
        $this->assertNull($app->user());

        $request->headers->set('PHP_AUTH_USER', 'fabien');
        $request->headers->set('PHP_AUTH_PW', 'foo');
        $app->handle($request);
        $this->assertInstanceOf('Symfony\Component\Security\Core\User\UserInterface', $app->user());
        $this->assertEquals('fabien', $app->user()->getUsername());
    }

    public function testUserWithNoToken()
    {
        $request = Request::create('/');

        $app = $this->createApplication();
        $app->get('/', function () { return 'foo'; });
        $app->handle($request);
        $this->assertNull($app->user());
    }

    public function testUserWithInvalidUser()
    {
        $request = Request::create('/');

        $app = $this->createApplication();
        $app->boot();
        $app['security.token_storage']->setToken(new UsernamePasswordToken('foo', 'foo', 'foo'));

        $app->get('/', function () { return 'foo'; });
        $app->handle($request);
        $this->assertNull($app->user());
    }

    public function testEncodePassword()
    {
        // FIXME 正しい EncodePassword が実装されたら有効にする
        $this->markTestIncomplete('EncodePassword is not implemented.');
        $app = $this->createApplication(array(
            'fabien' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
        ));

        $user = new User('foo', 'bar');
        $this->assertEquals('5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==', $app->encodePassword($user, 'foo'));
    }

    public function createApplication($users = array())
    {
        $app = Application::getInstance([
            'eccube.autoloader' => $GLOBALS['eccube.autoloader']
        ]);
        $app['debug'] = true;
        if (!$app->offsetExists('config')) {
            // ログの内容をERRORレベルでしか出力しないように設定を上書き
            $app->extend('config', function ($config, $app) {
            $config['log']['log_level'] = 'ERROR';
            $config['log']['action_level'] = 'ERROR';
            $config['log']['passthru_level'] = 'ERROR';

            $channel = $config['log']['channel'];
            foreach (array('monolog', 'front', 'admin') as $key) {
                $channel[$key]['log_level'] = 'ERROR';
                $channel[$key]['action_level'] = 'ERROR';
                $channel[$key]['passthru_level'] = 'ERROR';
            }
            $config['log']['channel'] = $channel;

            return $config;
                });
            $app->initLogger();
        }
        $app->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'default' => array(
                    'http' => true,
                    'users' => $users,
                ),
            ),
        ));

        $app->initialize();
        return $app;
    }
}
