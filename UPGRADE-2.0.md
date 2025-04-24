# UPGRADE FROM `1.3` TO `2.0`

**Replace the icon library**

For the admin section, there's very little to change. You'll need to replace your icon codes in your configuration files with a code from [UX icons](https://ux.symfony.com/icons).

In the example of [configuration file](dist/config/packages/monsieurbiz_settings_plugin_custom.yaml), we have made this modification:

```diff
-icon: bullseye
+icon: bi:bullseye
```
