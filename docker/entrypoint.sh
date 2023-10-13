#!/bin/sh
rm -rf /run/apache2/*
/usr/sbin/sshd -D &
/usr/sbin/php-fpm82 -D -R ; /usr/sbin/httpd ; nginx -g "daemon off;"
