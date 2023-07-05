<?php

namespace JustSomeCode\FlysystemVault;

use Vault\Client;
use Laminas\Diactoros\Uri;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\RequestFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use AlexTartan\GuzzlePsr18Adapter\Client as Psr18AdapterClient;
use Vault\AuthenticationStrategies\TokenAuthenticationStrategy;
use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;
use Vault\AuthenticationStrategies\UserPassAuthenticationStrategy;

class StorageServiceProvider extends ServiceProvider
{
    public function register()
    {
        //==============================================================================================================
        // Publish config
        //==============================================================================================================
        $this->publishes([__DIR__.'/../config/vault.php' => config_path('vault.php')], 'config');

        //==============================================================================================================
        // Bind the http client to talk to Vault
        //==============================================================================================================
        $this->app->singleton('vaultClient', function($app)
        {
            $client = new Client(
                new Uri($app['config']->get('vault.uri')),
                new Psr18AdapterClient,
                new RequestFactory(),
                new StreamFactory(),
                Log::getLogger()
            );

            if($app['config']->get('vault.use_namespace'))
            {
                $client->setNamespace($app['config']->get('vault.namespace'));
            }

            $auth_strategy = $app['config']->get('vault.auth_strategy');

            $strategy = match($auth_strategy) {
                // "token" is the default so it's omitted here as 'token' and default is used
                default => new TokenAuthenticationStrategy($app['config']->get('vault.auth_strategies.token.token')),
                'userpass' => new UserPassAuthenticationStrategy($app['config']->get('vault.auth_strategies.userpass.username'), $app['config']->get('vault.auth_strategies.userpass.password')),
                'approle' => new AppRoleAuthenticationStrategy($app['config']->get('vault.auth_strategies.approle.id'), $app['config']->get('vault.auth_strategies.approle.secret')),
            };

            $authenticated = $client->setAuthenticationStrategy($strategy)->authenticate();

            if(!$authenticated)
            {
                throw new \Exception("Failed authenticating with Vault service");
            }

            return $client;
        });
    }

    public function boot()
    {
        Storage::extend('vault', function($app, $config)
        {
            $adapter = new VaultAdapter($app['vaultClient']);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}