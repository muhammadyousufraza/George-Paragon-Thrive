# Thrive Apprentice

Thrive Apprentice gives you the most flexible drag and drop WordPress course building solution on the market — alongside a complete online business building toolkit!

## Requirements
* Composer - [info here](https://getcomposer.org/download/)
* NodeJS - [info here](https://nodejs.org/)

## After checkout from git

We use composer for autoload setup
```bash
composer install
```

We use node for installing dependencies in our current project
```bash
npm install
```

We need to make 3 symlinks:
1. [thrive-dashboard](https://github.com/ThriveThemes/thrive-dashboard) project under `thrive-dashboard` folder name
2. [tcb](https://github.com/ThriveThemes/tcb) project under `tcb` folder name
3. [thrive-theme](https://github.com/ThriveThemes/thrive-theme) project under `builder` folder name



## Other
* `composer dump-autoload` for regenerating the autoload files

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

![TA unit tests](https://github.com/ThriveThemes/thrive-apprentice/workflows/TA%20unit%20tests/badge.svg)
