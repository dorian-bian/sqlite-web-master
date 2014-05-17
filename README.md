# SQLite Web Master
SQLite Web Master is a full-featured database management system for sqlite3.

![ScreenShot](/doc/res/preview.jpg)
    
##. Features
1. Manage schemas.
    * Create/Update/Delete: table - view - trigger - index
2. Manage contents.
    * In-place edit.
    * blob editor.
3. Import/Export Contents.
    Format: xml - csv - sql - mdb(win) - xls(win)
4. Manage database files.
        
##. Requirements
1. PHP version 5.3.3 or higher, and Sqlite3 extension must be loaded.
2. <project>/tmp directory MUST be writable. 
    * You can change it(P_TEMP) in etc/config.php
3. If you want to import MDB/XLS files:
    1. Under MS Windows XP(or later) Platform.
    2. dotnet_com extension must be loaded.
    3. MADE must be installed. (http://www.microsoft.com/en-us/download/details.aspx?id=13255)

##. Installation
1. Unzip and upload all files (Of course except this file.)
2. Open index.php?i=pass in your browser, and generate user/pass/salt.the default user/pass is admin/admin.
3. Edit etc/config.php, override the user/pass/salt parts.
4. Config databases ($_DATABASES - files, $_DB_GROUPS - folders)
    1. If the file doesn't exist, it will create it as new database.
    2. For security reason, the databases should NOT be in any place where 
        they can be accessed directly from your browser.
5. Open index.php in your browser to use it.

##. License
    SQLite Web Master is licensed under the Mozilla Public License Version 2.0 . (See LICENSE)

##. Other Licenses
* Glyphicons Free Icons
    website: http://glyphicons.com/
    license: CC BY 3.0 (http://creativecommons.org/licenses/by/3.0/)
* Codemirror 
    website: http://codemirror.net/
    license: MIT-style (http://codemirror.net/LICENSE)
* JQuery
    website: http://www.jquery.com/
    license: MIT-LICENSE (http://en.wikipedia.org/wiki/MIT_License)


