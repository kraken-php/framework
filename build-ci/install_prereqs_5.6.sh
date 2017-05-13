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

source ${TRAVIS_BUILD_DIR}/build-ci/install_php_common.sh
source ${TRAVIS_BUILD_DIR}/build-ci/install_php_5.sh

install_ssh2