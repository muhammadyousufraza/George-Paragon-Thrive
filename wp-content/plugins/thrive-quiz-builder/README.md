# Thrive Quiz Builder

Thrive Quiz Builder not only gives you the ability to create extremely complex quizzes with branching logic, it also makes it extremely easy to visualize what your quiz looks like and how it flows in our quiz builder window.

## Requirements
* NodeJS - [info here](https://nodejs.org/)

We need to make 2 symlinks:
1. [thrive-dashboard](https://github.com/ThriveThemes/thrive-dashboard) project under `thrive-dashboard` folder name
2. [tcb](https://github.com/ThriveThemes/tcb) project under `tcb` folder name

## Instalation
* Checkout from git into `/wp-content/plugins` folder
* from terminal execute `npm install` in the main project folder `thrive-quiz-builder`
* `cd graph-editor` and execute `npm install`
* `cd image-editor` and execute `npm install`

## Other
* `npm run watch` for compiling javascript and style files and listening to changes. Should be executed in all 3 folders `thrive-quiz-builder`, `graph-editor`, `image-editor`. For additional details please see `webpack.config.js` file

See `package.json` for running additional scripts

Make sure you have the following constants in `wp-config.php` file

```
define( 'WP_DEBUG', true );
define( 'TCB_TEMPLATE_DEBUG', true );
define( 'THRIVE_THEME_CLOUD_DEBUG', true );
define( 'TCB_CLOUD_DEBUG', true );
define( 'TL_CLOUD_DEBUG', true );
define( 'TVE_DEBUG', true );`
```
