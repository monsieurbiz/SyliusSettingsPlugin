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

[![Settings Plugin license](https://img.shields.io/github/license/monsieurbiz/SyliusSettingsPlugin?public)](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/LICENSE)
[![Tests](https://img.shields.io/github/workflow/status/monsieurbiz/SyliusSettingsPlugin/Tests/master?label=tests&logo=github)](https://github.com/monsieurbiz/SyliusSettingsPlugin/actions?query=workflow%3ATests)
[![Packagist Version (including pre-releases)](https://img.shields.io/packagist/v/monsieurbiz/sylius-settings-plugin?include_prereleases)](https://packagist.org/packages/monsieurbiz/sylius-settings-plugin)

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

- The [configuration file](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/tests/Application/config/packages/monsieurbiz_settings_plugin_custom.yaml) to add your own settings.
- The [form with your own fields](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/tests/Application/src/Form/SettingsType.php).

Then you can get your settings using a twig function: `setting()`.  
Have a look at [this example](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/tests/Application/templates/views/message.html.twig).

You can also use the DI to get your Settings, as example with the settings in the test Application `app.default`:

```bash
$ ./bin/console debug:container | grep app.settings.default
  MonsieurBiz\SyliusSettingsPlugin\Settings\Settings $defaultSettings                    alias for "app.settings.default"
  MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface $defaultSettings           alias for "app.settings.default"
  app.settings.default                                                                   MonsieurBiz\SyliusSettingsPlugin\Settings\Settings
```

*Note:* the "Settings" menu won't appear until you have at least one setting.

## Contributing

You can find a way to run the plugin without effort in the file [DEVELOPMENT.md](./DEVELOPMENT.md).

Then you can open an issue or a Pull Request if you want! 😘  
Thank you!

## License

This plugin is completely free and released under the [MIT License](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/LICENSE).
