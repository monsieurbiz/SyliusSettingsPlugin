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
![Tests](https://img.shields.io/github/workflow/status/monsieurbiz/SyliusSettingsPlugin/Tests/master?label=tests&logo=github)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/quality/g/monsieurbiz/SyliusSettingsPlugin/master?logo=scrutinizer)](https://scrutinizer-ci.com/g/monsieurbiz/SyliusSettingsPlugin/?branch=master)
[![Packagist Version (including pre-releases)](https://img.shields.io/packagist/v/monsieurbiz/sylius-settings-plugin?include_prereleases)](https://packagist.org/packages/monsieurbiz/sylius-settings-plugin)

This plugin gives you the ability to have Plugins oriented settings in your favorite e-commerce platform, Sylius.

![Screenshot of the admin panel in Settings section](/docs/images/screenshot01.png)

## Installation

**⚠️ This plugin is not released yet.**

⚙️ To Be Defined

A few steps to start:

- Require the plugin via composer (`composer require monsieurbiz/sylius-settings-plugin="@rc" --no-scripts`).
- Edit the `config/bundles.php` (`MonsieurBiz\SyliusSettingsPlugin\MonsieurBizSyliusSettingsPlugin::class => ['all' => true],`).
- Copy the config (`cp -Rv vendor/monsieurbiz/sylius-settings-plugin/recipes/1.0-dev/config/ config/`).
- Run the diff in your migrations (`./bin/console doctrine:migration:diff`).
- Execute the migrations (`./bin/console doctrine:migration:migrate`).
- Continue to "[How it works](#how-it-works)".

Note: you may encounter an error during the installation via composer if you let it run the scripts.  
Copy the configuration files and rerun the `composer require`, it should work. This is due to the use of other plugins in the DI.
The configuration is then required to run any console command.

<!--

**Beware!**

> This installation instruction assumes that you're using Symfony Flex.

1. Require the plugin using composer

    ```bash
    composer require monsieurbiz/sylius-settings-plugin
    ```

2. Generate & Run Doctrine migrations

    ```
    ./bin/console doctrine:migration:diff
    ./bin/console doctrine:migration:migrate
    ```
-->

## How it works

⚙️ To Be Defined.

As a good start you can have a look to:

- The [configuration file](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/tests/Application/config/packages/monsieurbiz_settings_plugin_custom.yaml) to add your own settings.
- The [form with your own fields](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/tests/Application/src/Form/SettingsType.php).

Then you can get your settings using a twig function: `setting()`.  
Have a look to [this example](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/tests/Application/templates/views/message.html.twig).

You can also use the DI to get your Settings, as example with the settings in the test Application `app.default`:

```bash
$ ./bin/console debug:container | grep app.settings.default
  MonsieurBiz\SyliusSettingsPlugin\Settings\Settings $defaultSettings                    alias for "app.settings.default"
  MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface $defaultSettings           alias for "app.settings.default"
  app.settings.default                                                                   MonsieurBiz\SyliusSettingsPlugin\Settings\Settings
```

Note: the "Settings" menu won't appear until you have at least one setting.

## Testing

To Be Defined

<!-- See [TESTING.md](TESTING.md). -->

## Contributing

You can find a way to run the plugin without effort in the file [DEVELOPMENT.md](./DEVELOPMENT.md).

Then you can open an issue or a Pull Request if you want! 😘  
Thank you!

## License

This plugin is completely free and released under the [MIT License](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/LICENSE).
