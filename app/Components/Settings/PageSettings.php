<?php

namespace App\Components\Settings;

use App\Models\Page;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Manager;

class PageSettings extends Manager
{
    protected $pageKey;

    /**
     * Initializes Page Settings
     *
     * @param Container $container Container instance
     * @param string $key Page key
     */
    public function __construct(Container $container, string $key)
    {
        parent::__construct($container);

        $this->pageKey = $key;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return 'eloquent';
    }

    /**
     * Creates eloquent driver
     *
     * @return Drivers\EloquentDriver
     */
    protected function createEloquentDriver()
    {
        $page = Page::firstWhere(['page' => $this->pageKey]);

        return new Drivers\EloquentDriver($page->metaData);
    }

    /**
     * Creates cache driver
     *
     * @return Drivers\CacheDriver
     */
    protected function createCacheDriver()
    {
        $eloquentDriverFactory = fn () => $this->createEloquentDriver();
        $repository = $this->container->make('cache.store');

        return new Drivers\CacheDriver($this->pageKey, $eloquentDriverFactory, $repository);
    }
}
