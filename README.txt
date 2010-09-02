h1. Usage:

Some examples for the use of the cli-script:

- Go to htdocs/typo3/

Display options:
$ ./cli_dispatch.phpsh toolboxutf8

Display system informations:
$ ./cli_dispatch.phpsh toolboxutf8 info

Display system informations with informations to each table:
$ ./cli_dispatch.phpsh toolboxutf8 info -v

Display system informations with informations to each table and each field:
$ ./cli_dispatch.phpsh toolboxutf8 info -vv

Alter table structure (interactive mode); first you will see a selectionof all avaiable charsets:
$ ./cli_dispatch.phpsh toolboxutf8 alter 
$ ./cli_dispatch.phpsh toolboxutf8 alter -f (db updates!) 

Alter table structure (non-interactive mode); you have to pass the charset number. 
$ ./cli_dispatch.phpsh toolboxutf8 alter -a 1
$ ./cli_dispatch.phpsh toolboxutf8 alter -a 1 -f (db updates!) 


Convert data to UTF8 (dry-run: just output the queries) :
$ ./cli_dispatch.phpsh toolboxutf8 run

Convert data to UTF8 (db updates!):
$ ./cli_dispatch.phpsh toolboxutf8 run -f




The output can be redirected to stdout with:
$ ./cli_dispatch.phpsh toolboxutf8 info -v > info.txt
$ ./cli_dispatch.phpsh toolboxutf8 alter -v > alter.txt
$ ./cli_dispatch.phpsh toolboxutf8 run -v > run.txt


Windows only:
chcp 65001


h2. TODO:

* Refactor cli script. Externalise all logic to API-Class for use in reports AND cli-script
* Refactor reports/status classes. Use mvc to seperate logic and view.



h1. Links (some are german):

h2. Ongoing utf8 discussions in the community:

* http://bugs.typo3.org/view.php?id=6098 
* http://bugs.typo3.org/view.php?id=3900
* http://bugs.typo3.org/view.php?id=7942
* http://lists.typo3.org/pipermail/typo3-project-v4/2010-May/000187.html 
* http://www.niekom.de/public/mysteries-charset.pdf

 

h2. MySQL:

* http://forums.mysql.com/read.php?103,28072,28144#msg-28144
* http://dev.mysql.com/doc/refman/5.1/en/charset-connection.html
* http://www.buildblog.de/2009/01/02/mysql-server-auf-utf-8-umstellen/
* http://dev.mysql.com/tech-resources/articles/4.1/unicode.html

h2. TYPO3:

* http://typo3.org/documentation/document-library/core-documentation/doc_l10nguide/1.1.0/view/1/2/#id2528880
* http://wiki.typo3.org/index.php/UTF-8_support
* http://xavier.perseguers.ch/en/tutorials/typo3/configuration/utf-8.html
* http://buzz.typo3.org/article/charset-issues-between-iso-8859-1-and-utf-8-on-ubuntu/
* http://bugs.typo3.org/view.php?id=13431&nbn=4
* http://www.bruchmann-web.de/en/support/typo3/tipps-und-tricks/utf-8-in-typo3/


h3. Migration/Update:

* http://www.exanto.de/typo3-und-utf-8.html#comment-29255
* http://blog.stefan-macke.com/2006/11/28/konvertieren-einer-typo3-installation-nach-utf-8/
* http://www.typo3erweiterungen.de/detail/article/39/typo3-auf-utf-8-umstellen/
* http://www.a-vista-studios.de/avs/blog/208/typo3/datenbank-konvertierung-auf-utf-8/anleitung-und-manual/
* http://blog.markusgiesen.de/2007/07/29/typo3-mysql-datenbank-auf-utf-8-umstellen/


h3. Case studies

* http://www.typo3.net/forum/list/list_post//59723/?page=1#pid222351
* http://www.typo3.net/forum/list/list_post//79433/?page=1&sword=renderCharset%20%3D%20utf-8
* http://bugs.typo3.org/view.php?id=2856
* http://lists.typo3.org/pipermail/typo3-english/2010-June/069178.html


h2. UTF-8:

* http://www.rfc-editor.org/rfc/rfc3629.txt
* http://stackoverflow.com/questions/1031645/how-to-detect-utf8-in-plain-c/1031683#1031683
* http://www.cl.cam.ac.uk/~mgk25/unicode.html
* http://www.phpwact.org/php/i18n/charsets
* http://www.phpwact.org/php/i18n/utf-8
* http://www.cs.tut.fi/~jkorpela/chars.html
* http://site.icu-project.org/
* http://pycheesecake.org/wiki/ZenOfUnicode
* http://w3.org/International/questions/qa-forms-utf-8.html
* http://www.w3.org/International/O-charset.en.php


h2. PHP:

* http://www.php.net/~derick/meeting-notes.html#unicode
* http://php.net/manual/en/function.mb-detect-encoding.php
* http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
* http://www.php.net/manual/de/function.mysql-set-charset.php


h2. APACHE:

* http://www.askapache.com/htaccess/setting-charset-in-htaccess.html
* http://www.w3.org/International/questions/qa-htaccess-charset#extension
* http://www.askapache.com/htaccess/using-http-headers-with-htaccess.html
* http://nadeausoftware.com/articles/2007/06/php_tip_how_get_web_page_content_type
* http://bugs.centos.org/view.php?id=3187

h2. SECURITY:

* http://www.suspekt.org/2008/09/18/slides-from-my-lesser-known-security-problems-in-php-applications-talk-at-zendcon/
* http://shiflett.org/blog/2006/jan/addslashes-versus-mysql-real-escape-string
* http://ilia.ws/archives/103-mysql_real_escape_string-versus-Prepared-Statements.html
* http://cognifty.com/blog.entry/id=6/addslashes_dont_call_it_a_comeback.html
* http://framework.zend.com/issues/browse/ZF-1541?focusedCommentId=28702#action_28702
* http://bugs.mysql.com/bug.php?id=8378