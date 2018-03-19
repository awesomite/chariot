#!/usr/bin/env bash

set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

if php -r "var_export(get_loaded_extensions(true));" | grep --quiet -i xdebug; then
  cd ${DIR}/..
  travis_retry wget https://github.com/infection/infection/releases/download/0.8.1/infection.phar -q
  travis_retry wget https://github.com/infection/infection/releases/download/0.8.1/infection.phar.pubkey -q
  chmod +x infection.phar
  travis_retry ./infection.phar self-update
  ./infection.phar
else
  echo "Xdebug is not installed"
fi
