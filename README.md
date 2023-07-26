<p align="center">
    <a href="https://monsieurbiz.com" target="_blank">
        <img src="https://monsieurbiz.com/logo.png" width="250px" alt="Monsieur Biz logo" />
    </a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a href="https://monsieurbiz.com/agence-web-experte-sylius" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" width="200px" alt="Sylius logo" />
    </a>
    <br/>
    <img src="https://monsieurbiz.com/assets/images/sylius_badge_extension-artisan.png" width="100" alt="Monsieur Biz is a Sylius Extension Artisan partner">
</p>

<h1 align="center">Settings for Sylius</h1>

[![Settings Plugin license](https://img.shields.io/github/license/monsieurbiz/SyliusSettingsPlugin?public)](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/LICENSE.txt)
[![Tests](https://github.com/monsieurbiz/SyliusSettingsPlugin/actions/workflows/tests.yaml/badge.svg?branch=master&event=push)](https://github.com/monsieurbiz/SyliusSettingsPlugin/actions/workflows/tests.yaml)
[![Security](https://github.com/monsieurbiz/SyliusSettingsPlugin/actions/workflows/security.yaml/badge.svg?branch=master&event=push)](https://github.com/monsieurbiz/SyliusSettingsPlugin/actions/workflows/security.yaml)

This plugin gives you the ability to have Plugins oriented settings in your favorite e-commerce platform, Sylius.

![Screenshot of the admin panel in Settings section](/docs/images/screenshot01.png)

## Installation

Install the plugin via composer:

```bash
composer require monsieurbiz/sylius-settings-plugin
```

<details><summary>For the installation without flex, follow these additional steps</summary>
<p>

Change your `config/bundles.php` file to add this line for the plugin declaration:
```php
<?php

return [
    //..
    MonsieurBiz\SyliusSettingsPlugin\MonsieurBizSyliusSettingsPlugin::class => ['all' => true],
];  
```

Copy the plugin configuration files in your `config` folder: 
```bash  
cp -Rv vendor/monsieurbiz/sylius-settings-plugin/recipes/1.0-dev/config/ config
```

</p>
</details>  

Update your database:

```bash 
bin/console doctrine:migration:migrate
```

Continue to "[How it works](#how-it-works)" to add your first setting for your store.

*Note:* you may encounter an error during the installation via composer if you let it run the scripts.  
Copy the configuration files and rerun the `composer require`, it should work. This is due to the use of other plugins in the DI.
The configuration is then required to run any console command.

## How it works

As a good start you can have a look at:

- The [configuration file](dist/config/packages/monsieurbiz_settings_plugin_custom.yaml) to add your own settings.
- The [form with your own fields](dist/src/Form/SettingsType.php).

Then you can get your settings using a twig function: `setting()`.  
Have a look at [this example](dist/templates/views/message.html.twig).

You can also use the DI to get your Settings, as example with the settings in the test Application `app.default`:

```bash
$ ./bin/console debug:container | grep app.settings.default
  MonsieurBiz\SyliusSettingsPlugin\Settings\Settings $defaultSettings                    alias for "app.settings.default"
  MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface $defaultSettings           alias for "app.settings.default"
  app.settings.default                                                                   MonsieurBiz\SyliusSettingsPlugin\Settings\Settings
```

*Note:* the "Settings" menu won't appear until you have at least one setting.

### Fetch settings

```php
use MonsieurBiz\SyliusSettingsPlugin\Provider\SettingsProviderInterface;
//...

    private SettingsProviderInterface $settingsProvider;

    public function __construct(SettingsProviderInterface $settingsProvider)
    {
            $this->settingsProvider = $settingsProvider;
    }
    
    public function myAwesomeMethod()
    {
        return $this->settingsProvider->getSettingValue('app.default', 'demo_message')
    }
```

### Use fixtures

We've implemented a fixtures loader to help you to create your settings if you need to have different settings for your
tests or project (by channel, by locale…).

You need to create a yaml file with your fixtures, like explained in the documentation of Sylius.  
You can find our own example in the source code, section `sylius_fixtures`: [configuration file](dist/config/packages/monsieurbiz_settings_plugin_custom.yaml).

It's also possible to run test fixtures with a local suite in development: `make sylius.fixtures.local`.

By default, a fixture will replace the value of a setting if it already exists. 
If you want to keep a value as it is in the database when running this fixture, you can use the flag `ignore_if_exists: true` for each element that you want to be kept.

### Use CLI

You can use a CLI command to set a value for a setting directly from the console:
`$ ./bin/console monsieurbiz:settings:set {alias} {path} {value} --channel="FASHION_WEB" --locale="en_US" --type="text"`


Examples:
```bash
$ ./bin/console monsieurbiz:settings:set app.default demo_message 'fashion message' --channel="FASHION_WEB" --locale="en_US"
$ ./bin/console monsieurbiz:settings:set app.default demo_json '{"foo":"baz"}' --channel="FASHION_WEB" --locale="en_US" --type="json"
$ ./bin/console monsieurbiz:settings:set app.default demo_datetime '2023-07-24 01:02:03' --channel="FASHION_WEB" --locale="en_US" --type="datetime"
$ ./bin/console monsieurbiz:settings:set app.default enabled 0
```
The options channel and locale can be omitted if you want to set the value for a global scope.
If a value exists for the given scope the type can be omitted as it will be the same as the existing one unless you want to change the type.
For a new value you need to specify the type.

⚠️ When specifying the type, be sure to know what you are doing as it should be coherent with the Form Type of the field.

## Contributing

You can find a way to run the plugin without effort in the file [DEVELOPMENT.md](./DEVELOPMENT.md).

Then you can open an issue or a Pull Request if you want! 😘  
Thank you!

## License

This plugin is completely free and released under the [MIT License](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/LICENSE).
