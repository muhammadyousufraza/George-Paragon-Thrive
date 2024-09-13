# Thrive Product Manager (TPM)

Helps users to install Thrive Products that they have access to.

## Requirements
* NodeJS - [info here](https://nodejs.org/)

## After checkout from git

We use node for installing dependencies in our current project
```bash
npm install
```

We need to make 1 symlinks:
1. [thrive-dashboard](https://github.com/ThriveThemes/thrive-dashboard) project under `thrive-dashboard` folder name

See `package.json` for running additional scripts

## For developers
`npm run watch` for developing. This command watches every modification on asset files (*.js, *.scss) and generate the corresponding (*.js..min, *.css) files

For additional details please see `webpack.config.js` file

Make sure you have the following constants in `wp-config.php` file

```
define( 'WP_DEBUG', true );
define( 'TPM_DEBUG', true );
define( 'TVE_DEBUG', true );`
```
