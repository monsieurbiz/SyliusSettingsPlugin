<p align="center">
    <a href="https://monsieurbiz.com" target="_blank">
        <img src="https://monsieurbiz.com/logo.png" width="250px" />
    </a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" width="200px" />
    </a>
</p>

<h1 align="center">Settings for Sylius</h1>

<!--
[![Settings Plugin license](https://img.shields.io/github/license/monsieurbiz/SyliusSettingsPlugin?public)](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/LICENSE)
![Tests](https://github.com/monsieurbiz/SyliusSettingsPlugin/workflows/CI/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/monsieurbiz/SyliusSettingsPlugin/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/monsieurbiz/SyliusSettingsPlugin/?branch=master)
-->

This plugin gives the ability to have Plugins oriented settings in your favorite e-commerce platform, Sylius.

![Screenshot of the admin panel in Settings section](/docs/images/screenshot01.png)

## Installation

**⚠️ This plugin is not released yet.**

⚙️ To Be Defined

A few steps to start:

- Edit the `config/bundles.php` (`MonsieurBiz\SyliusSettingsPlugin\MonsieurBizSyliusSettingsPlugin::class => ['all' => true],`).
- Copy the config (`cp -Rv vendor/monsieurbiz/sylius-settings-plugin/recipes/1.0-dev/config/ config/`).
- Continue to "[How it works](#how-it-works)".

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

## Testing

To Be Defined

<!-- See [TESTING.md](TESTING.md). -->

## Contributing

You can find a way to run the plugin without effort in the file [DEVELOPMENT.md](./DEVELOPMENT.md).

Then you can open an issue or a Pull Request if you want! 😘  
Thank you!

## License

This plugin is completely free and released under the [MIT License](https://github.com/monsieurbiz/SyliusSettingsPlugin/blob/master/LICENSE).
