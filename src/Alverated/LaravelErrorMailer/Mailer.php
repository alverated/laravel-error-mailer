<?php

namespace Alverated\LaravelErrorMailer;

use Dotenv\Dotenv;
use Illuminate\Container\Container;
use Illuminate\Config\Repository as Config;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem as Filesystem;
use Illuminate\Mail\Mailer as LaravelMailer;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\Mail\TransportManager;

class Mailer {

    protected $basePath;
    protected $viewFactory;
    protected $args;
    protected $app;
    protected $data;
    protected $options;
    protected $config;
    protected $vendorPath;
    protected $confFile = 'laravel-error-mailer';

    public function __construct($args, $options = [], $loadSetup = true, $send = true, $basePath = '', $vendorPath = '')
    {
        $this->setBasePath($basePath);
        $this->loadVendor($vendorPath);
        $this->loadEnv($this->basePath);
        $this->setArgs($args);
        $this->defaultOptions();

        if(!empty($options))
            $this->updateOptions($options);

        if($loadSetup)
            $this->setup();

        if($send)
            $this->send();
    }

    public function defaultOptions()
    {
        $this->options = $this->getLaravelConfig($this->confFile);
    }

    public function setup()
    {
        $this->setApp();
        $this->setConfig();
        $this->configView();
        $this->loadData();
    }

    public function setBasePath($basePath = '')
    {
        $this->basePath = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))));
        if(!empty($basePath)) {
            $this->basePath = $basePath;
        }
    }

    public function setVendorPath($vendorPath)
    {
        $this->vendorPath = $vendorPath;
    }

    public function loadVendor($vendorPath)
    {
        if(!empty($vendorPath))
            $this->setVendorPath($vendorPath);

        $this->vendorPath = $this->basePath . '/vendor';
        require $this->vendorPath . '/autoload.php';
    }

    public function loadEnv($path)
    {
        $env = new Dotenv($path);
        $env->load();
    }

    public function setApp()
    {
        $this->app = new Container();
    }

    public function updateOptions($options)
    {
        foreach ($options as $key => $option)
            $this->options[$key] = $option;
    }

    public function configView()
    {
        $engineResolver = new EngineResolver;
        $engineResolver->register('php', function () {
            return new PhpEngine();
        });

        $this->app->singleton('blade.compiler', function ($app) {
            $cachePath = $this->basePath . "/storage/framework/views";
            return new BladeCompiler(new Filesystem, $cachePath);
        });

        $engineResolver->register('blade', function () {
            return new CompilerEngine($this->app['blade.compiler']);
        });

        $fileViewFinder = new FileViewFinder(new Filesystem, [], null);

        $this->viewFactory = new Factory($engineResolver, $fileViewFinder, new Dispatcher);
        $fileViewFinder->addLocation($this->basePath . "/resources/views");
    }

    public function loadData()
    {
        $request = $this->getArgs();
        $this->data['blade'] = $this->options['template'];
        $this->data['tempData'] = $request['tempData'];
        $this->data['recipients'] = $this->options['recipients'];
        $this->data['subject'] = $this->options['subject'];
        $this->data['name'] = null;
        $this->data['replytoname'] = $this->options['reply_to']['name'];
        $this->data['replytoemail'] = $this->options['reply_to']['email'];
        $this->data['fromname'] = $this->options['from']['name'];
        $this->data['fromemail'] = $this->options['from']['email'];
    }

    public function setArgs($args)
    {
        $this->args = $args;
    }

    public function setConfig()
    {
        $this->app['config'] = new Config();
        $this->app['config']['mail'] = $this->getLaravelConfig('mail');
        $this->app['config']['services'] = $this->getLaravelConfig('services');
    }

    public function getLaravelConfig($name, $default = null)
    {
        $file = $this->basePath . '/config/'.$name.'.php';
        if($file)
            return require $file;
        else
            return $default;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getConfig()
    {
        return $this->app['config'];
    }

    public function newMailer()
    {
        $config = $this->getConfig();
        $transportManager = new TransportManager($this->app);
        $transport = $transportManager->driver($config['mail']['driver']);
        $swiftMailer = new \Swift_Mailer($transport);
        return new LaravelMailer($this->viewFactory, $swiftMailer);
    }

    public function send()
    {
        $mailer = $this->newMailer();
        $data = $this->data;

        $mailer->send($data['blade'], $data['tempData'], function ($message) use ($data) {
            $cc = [];
            $emails = $data['recipients'];
            if (is_array($emails)) {
                $to = isset($emails[0]) ? $emails[0] : '';
                $cc = (count($emails) > 1) ? array_slice($emails, 1) : array();
            } else {
                $to = $data['recipients'];
            }

            $message->to($to, $data['name'])
                ->from($data['fromemail'], $data['fromname'])
                ->replyTo($data['replytoemail'], $data['replytoname'])
                ->subject($data['subject']);

            if (count($cc) > 0) {
                foreach ($cc as $ccemail)
                    $message->cc($ccemail);
            }
        });
    }
}

$args = isset($argv) ? $argv : null;
$request = json_decode(urldecode($args[1]), true);
new Mailer($request);