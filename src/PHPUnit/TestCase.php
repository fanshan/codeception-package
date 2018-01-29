<?php

namespace Codeception\Module\PHPUnit;

use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Invokable\Invokable;
use ObjectivePHP\Message\Request\RequestInterface;

/**
 * Class TestCase
 *
 * @package Codeception\Module\PHPUnit
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var ApplicationInterface */
    protected $application;

    protected function setUp()
    {
        $this->initialize();
    }

    protected function initialize()
    {
        $this->application = $this->createApplication();
    }

    /**
     * @return ApplicationInterface
     */
    public function createApplication() : ApplicationInterface
    {
        $app = new Application();
        $app->setEnv('test');
        $app->loadConfig('app/config');

        return $app;
    }

    /**
     * @return ApplicationInterface
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param                        $class
     * @param RequestInterface       $request
     *
     * @throws \ObjectivePHP\Invokable\Exception
     * @throws \ObjectivePHP\Primitives\Exception
     * @throws \ObjectivePHP\ServicesFactory\Exception\ServiceNotFoundException
     *
     * @return mixed
     */
    public function execute($class, RequestInterface $request)
    {
        $action = new $class;

        $app = $this->getApplication();
        $app->setRequest($request);

        $action = Invokable::cast($action);
        $app->getServicesFactory()->injectDependencies($action->getCallable());

        return $action->getCallable()($app);
    }
}
