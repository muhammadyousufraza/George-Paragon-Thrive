# Thrive Leads

Thrive Leads is a lead generation plugin. That means it's designed to help you build your mailing list and get more subscribers.

## Requirements
* NodeJS - [info here](https://nodejs.org/)

## Before checkout
The folder that you are using for checkout should be called `thrive-leads`.
This is because the folder name should be the same as the plugin main PHP file

## After checkout from git

We use node for installing dependencies in our current project
```bash
npm install
```

We need to make 1 symlink
1. [thrive-dashboard](https://github.com/ThriveThemes/thrive-dashboard) project under `thrive-dashboard` folder name
2. [tcb](https://github.com/ThriveThemes/tcb) project under `tcb` folder name


See `package.json` for running additional scripts

## For developing:
`npm run watch` for developing. This command watches every modification on asset files (*.js, *.scss) and generate the corresponding (*.js..min, *.css) files

For additional details please see `webpack.config.js` file

Make sure you have the following constants in `wp-config.php` file

```
define( 'WP_DEBUG', false );
define( 'TCB_TEMPLATE_DEBUG', true );
define( 'THRIVE_THEME_CLOUD_DEBUG', true );
define( 'TCB_CLOUD_DEBUG', true );
define( 'TL_CLOUD_DEBUG', true );
define( 'TVE_DEBUG', true );`
```