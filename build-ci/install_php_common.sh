#!/usr/bin/env bash
#
#  Kraken Framework
#
#  Copyright (c) 2015-2017 Kraken Team (http://kraken-php.com)
#
#  This source file is subject to the MIT License that is bundled
#  with this package in the MIT license.

CURRENT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
TRAVIS_BUILD_DIR="${TRAVIS_BUILD_DIR:-$(dirname $(dirname $CURRENT_DIR))}"

pecl channel-update pecl.php.net || true
echo `whoami`":1234" | sudo chpasswd

enable_extension() {
    if [ -z $(php -m | grep "${1}") ] && [ -f "${TRAVIS_BUILD_DIR}/build-ci/ini/${1}.ini" ]; then
        phpenv config-add "${TRAVIS_BUILD_DIR}/build-ci/ini/${1}.ini"
    fi
}

install_extension() {
    INSTALLED=$(pecl list "${1}" | grep 'not installed')

    if [ -z "${INSTALLED}" ]; then
        printf "\n" | pecl upgrade "${1}" &> /dev/null
    else
        printf "\n" | pecl install "${1}" &> /dev/null
    fi

    enable_extension "${1}"
}