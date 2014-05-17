<?php
#-----------------------------------------------------------------------
require_once 'etc/config.php';

require_once 'lib/client.php';
require_once 'lib/database.php';
require_once 'lib/functions.php';
require_once 'lib/template.php';

require_once 'app/console.php';
# setup ----------------------------------------------------------------
define('START_TIME', microtime(TRUE));
define('IS_POST', $_SERVER['REQUEST_METHOD']=='POST');

Template::$site['root'] = V_PATH;
Template::$site['theme'] = THEME;
Template::$site['debug'] = DEBUG;
Template::$site['is_post'] = IS_POST;
Template::$mode = TEMPLATE_MODE;

Console::$debug = DEBUG;
Console::instance()->run();
# ----------------------------------------------------------------------
?>
