<?php

namespace Kaylyu\Alipay\Kernel;

use Kaylyu\Alipay\Kernel\Providers\ConfigServiceProvider;
use Kaylyu\Alipay\Kernel\Providers\HttpClientServiceProvider;
use Kaylyu\Alipay\Kernel\Providers\LogServiceProvider;
use Kaylyu\Alipay\Kernel\Providers\RequestServiceProvider;
use Pimple\Container;

/**
 * Class ServiceContainer.
 *
 * @property \Kaylyu\Alipay\Kernel\Config $config
 * @property \Symfony\Component\HttpFoundation\Request $request
 * @property \GuzzleHttp\Client $http_client
 * @property \Monolog\Logger $logger
 */
class ServiceContainer extends Container
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $defaultConfig = [];

    /**
     * @var array
     */
    protected $userConfig = [];

    /**
     * Constructor.
     *
     * @param array $config
     * @param array $prepends
     * @param string|null $id
     */
    public function __construct(array $config = [], array $prepends = [], string $id = null)
    {
        $this->registerProviders($this->getProviders());

        parent::__construct($prepends);

        $this->userConfig = $config;

        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id ?? $this->id = md5(json_encode($this->userConfig));
    }

    /**
     * 当面付
     * @return array
     */
    public function getF2fpay() : array
    {
        return $this->config->get('f2fpay');
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $base = [
            // http://docs.guzzlephp.org/en/stable/request-options.html
            'http' => [
                'timeout' => 5.0,
                'base_uri' => '',
            ],
        ];

        return array_replace_recursive($base, $this->defaultConfig, $this->userConfig);
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return array_merge([
            ConfigServiceProvider::class,
            LogServiceProvider::class,
            RequestServiceProvider::class,
            HttpClientServiceProvider::class,
        ], $this->providers);
    }

    /**
     * @param string $id
     * @param mixed $value
     */
    public function rebind($id, $value)
    {
        $this->offsetUnset($id);
        $this->offsetSet($id, $value);
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * @param array $providers
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            parent::register(new $provider());
        }
    }
}
