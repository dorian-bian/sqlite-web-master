<?php 
/**********************************************************************/
define('P_PATH', dirname(dirname(__FILE__)));
define('P_TEMP', P_PATH.'/tmp'); 
define('V_PATH', '.');

define('THEME', 'modern'); // Another theme is 'main'.
/** TRUE: It'll show details if an error occurred. */
define('DEBUG',  TRUE);

define('CONTENT_TEXT_SIZE', 256);
define('CONTENT_PAGE_SIZE', 16);

/** 0: build,  1: compile,  2 template-tags */
define('TEMPLATE_MODE', 0);

/** TRUE: Disable importing data from physical path. Default is TRUE. */
define('IMPORT_STRICT_MODE', TRUE);

/** TRUE: Add sec-token=... to url. */ 
define('SEC_PATH', TRUE);

/** If you checked 'Remember Me' when you are logging in, the system will 
    remember you for an extra 30 days (since your last time using this system).*/
define('SEC_LAST', time() + 604800); 
/**********************************************************************/
/** user/pass config:  use 'index.php?i=pass' to generate it */
define('SEC_USER', 'admin');
define('SEC_SALT', '13D54C5D');
define('SEC_PASS', '9745c76f7cc303ddde38909a09894e4b');
/**********************************************************************/
/** The database files. At least has one. */
$_DATABASES = array(
    'docr' => array('path' => '/opt/docr/srv/data.sq3'),
);

/** The databases in these directories are manageable. */
$_DB_GROUPS = array(
   # 'managed' => array('path'=> P_PATH.'/opt/', 'tail'=>'.sqlite'),
);
/**********************************************************************/
?>
