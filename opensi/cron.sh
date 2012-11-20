#!/bin/sh
# location of the php binary
CRONSCRIPT=cron.php
PHP_BIN=`which php`

# Absolute path to prestashop installation
INSTALLDIR=`echo $0 | sed 's/cron\.sh//g'`

# Prepend the intallation path if not given an absolute path
if [ "$INSTALLDIR" != "" ];then
	if ! ps auxwww | grep "$INSTALLDIR""$CRONSCRIPT" | grep -v grep 1>/dev/null 2>/dev/null ; then
		$PHP_BIN "$INSTALLDIR""$CRONSCRIPT" &
	fi
else
	if ! ps auxwww | grep " $CRONSCRIPT" | grep -v grep | grep -v cron.sh 1>/dev/null 2>/dev/null ; then
		$PHP_BIN $CRONSCRIPT &
	fi
fi