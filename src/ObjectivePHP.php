<?php

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\Framework;
use Codeception\Module\Connector\ObjectivePHP as ObjectivePHPConnector;
use Codeception\TestInterface;
use ObjectivePHP\Config\Config;

/**
 * Class ObjectivePHP
 *
 * @package Codeception\Module
 */
class ObjectivePHP extends Framework
{
    /**
     * @var \ObjectivePHP\Application\AbstractApplication
     */
    protected $application;

    /**
     * @var ObjectivePHPConnector
     */
    public $client;

    /**
     * {@inheritdoc}
     */
    public function _initialize()
    {
        $this->application = new $this->config['application_class'];

        $cwd = getcwd();
        chdir(Configuration::projectDir());

        if (file_exists('app/config')) {
            $this->application->loadConfig('app/config');
        } else {
            $this->application->setConfig(new Config());
        }

        chdir($cwd);

        $this->application->setEnv('test');
    }

    /**
     * {@inheritdoc}
     */
    public function _before(TestInterface $test)
    {
        $this->client = new ObjectivePHPConnector();
        $this->client->setApplication($this->application);
    }

    /**
     * {@inheritdoc}
     */
    public function _after(TestInterface $test)
    {
        //Close the session, if any are open
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::_after($test);
    }
}
