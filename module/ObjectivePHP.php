<?php

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\Framework;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Package\Codeception\Connector\ObjectivePHP as Connector;
use Codeception\TestInterface;
use ObjectivePHP\Config\Config;

/**
 * Class ObjectivePHP
 *
 * @package Codeception\Module
 */
class ObjectivePHP extends Framework
{
    protected $requiredFields = ['application_class', 'config_path'];

    /**
     * @var ApplicationInterface
     */
    protected $application;

    /**
     * @var Connector
     */
    public $client;

    /**
     * {@inheritdoc}
     */
    public function _initialize()
    {
        $this->setApplication(new $this->config['application_class']);

        $cwd = getcwd();
        chdir(Configuration::projectDir());

        if (file_exists($this->config['config_path'])) {
            $this->getApplication()->loadConfig($this->config['config_path']);
        } else {
            $this->getApplication()->setConfig(new Config());
        }

        chdir($cwd);

        $this->getApplication()->setEnv('test');
    }

    /**
     * {@inheritdoc}
     */
    public function _before(TestInterface $test)
    {
        $this->setClient(new Connector());
        $this->getClient()->setApplication($this->getApplication());

        parent::_before($test);
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

    /**
     * Get Application
     *
     * @return ApplicationInterface
     */
    public function getApplication(): ApplicationInterface
    {
        return $this->application;
    }

    /**
     * Set Application
     *
     * @param ApplicationInterface $application
     *
     * @return $this
     */
    public function setApplication(ApplicationInterface $application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get Client
     *
     * @return Connector
     */
    public function getClient(): Connector
    {
        return $this->client;
    }

    /**
     * Set Client
     *
     * @param Connector $client
     *
     * @return $this
     */
    public function setClient(Connector $client)
    {
        $this->client = $client;

        return $this;
    }
}
