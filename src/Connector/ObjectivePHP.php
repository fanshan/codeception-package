<?php

namespace ObjectivePHP\Package\Codeception\Connector;

use ObjectivePHP\Application\ApplicationInterface;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\UploadedFile;

/**
 * Class ObjectivePHP
 *
 * @package Codeception\Module\Connector
 */
class ObjectivePHP extends Client
{
    /**
     * @var ApplicationInterface
     */
    protected $application;

    /**
     * @param Request $request
     *
     * @return Response
     */
    protected function doRequest($request)
    {
        $_GET = $_POST = [];

        $inputStream = fopen('php://memory', 'r+');
        $content = $request->getContent();
        if ($content !== null) {
            fwrite($inputStream, $content);
            rewind($inputStream);
        }

        $queryParams = [];
        $postParams = [];
        $queryString = parse_url($request->getUri(), PHP_URL_QUERY);
        if ($queryString != '') {
            parse_str($queryString, $queryParams);
            $_GET = $queryParams;
        }

        if ($request->getMethod() === 'POST') {
            $_POST = $postParams = $request->getParameters();
        }

        $_SERVER['REQUEST_URI'] = parse_url($request->getUri(), PHP_URL_PATH);
        $_SERVER['REQUEST_METHOD'] = $request->getMethod();
        $_SERVER['QUERY_STRING'] = parse_url($request->getUri(), PHP_URL_QUERY);

        $serverParams = $request->getServer();
        if (!isset($serverParams['SCRIPT_NAME'])) {
            //required by WhoopsErrorHandler
            $_SERVER['SCRIPT_NAME'] = $serverParams['SCRIPT_NAME'] = 'Codeception';
        }

        ob_start();
        $this->application->run();
        codecept_debug(sprintf('[contents] %s', ob_get_contents()));
        ob_get_clean();

        $this->request = (
            new ServerRequest(
                $serverParams,
                $this->convertFiles($request->getFiles()),
                $request->getUri(),
                $request->getMethod(),
                $inputStream,
                $this->extractHeaders($request)
            )
        )->withCookieParams($request->getCookies())
            ->withQueryParams($queryParams)
            ->withParsedBody($postParams);

        return new Response(
            $this->application->getResponse()->getBody(),
            $this->application->getResponse()->getStatusCode(),
            $this->application->getResponse()->getHeaders()
        );
    }

    protected function convertFiles(array $files)
    {
        $fileObjects = [];
        foreach ($files as $fieldName => $file) {
            if ($file instanceof UploadedFile) {
                $fileObjects[$fieldName] = $file;
            } elseif (!isset($file['tmp_name']) && !isset($file['name'])) {
                $fileObjects[$fieldName] = $this->convertFiles($file);
            } else {
                $fileObjects[$fieldName] = new UploadedFile(
                    $file['tmp_name'],
                    $file['size'],
                    $file['error'],
                    $file['name'],
                    $file['type']
                );
            }
        }
        return $fileObjects;
    }

    protected function extractHeaders(BrowserKitRequest $request)
    {
        $headers = [];
        $server = $request->getServer();

        $contentHeaders = array('Content-Length' => true, 'Content-Md5' => true, 'Content-Type' => true);
        foreach ($server as $header => $val) {
            $header = implode('-', array_map('ucfirst', explode('-', strtolower(str_replace('_', '-', $header)))));

            if (strpos($header, 'Http-') === 0) {
                $headers[substr($header, 5)] = $val;
            } elseif (isset($contentHeaders[$header])) {
                $headers[$header] = $val;
            }
        }

        return $headers;
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
}
