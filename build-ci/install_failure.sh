#!/usr/bin/env bash
#
#  Kraken Framework
#
#  Copyright (c) 2015-2017 Kraken Team (http://kraken-php.com)
#
#  This source file is subject to the MIT License that is bundled
#  with this package in the MIT license.

shopt -s nullglob
export LC_ALL=C

for i in /tmp/core_*.*; do
	if [ -f "$i" -a "$(file "$i" | grep -o 'core file')" ]; then
		gdb -q $(phpenv which php) "$i" <<EOF
set pagination 0
backtrace full
info registers
x/16i \$pc
thread apply all backtrace
quit
EOF
	fi
done

$(phpenv which php) -m
$(phpenv which php) -i