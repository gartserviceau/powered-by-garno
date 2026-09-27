#!/usr/bin/env bash
#
# The badge's version is written in three places: the WordPress plugin header,
# the PHP constant WordPress uses to bust caches, and the JavaScript. They must
# agree, and on a release they must also agree with the tag.
#
#   bin/check-version.sh            # the three agree
#   bin/check-version.sh 1.0.1      # ... and equal 1.0.1
set -euo pipefail

cd "$(dirname "$0")/.."

header=$(sed -nE 's/^ \* Version:[[:space:]]+([0-9.]+).*/\1/p' powered-by-garno.php)
constant=$(sed -nE "s/^const GARNO_BADGE_VERSION = '([0-9.]+)';/\1/p" powered-by-garno.php)
script=$(sed -nE "s/^[[:space:]]*const VERSION = '([0-9.]+)';/\1/p" powered-by-garno.js)
package=$(sed -nE 's/^[[:space:]]*"version": "([0-9.]+)".*/\1/p' package.json)

echo "plugin header: $header  constant: $constant  script: $script  package.json: $package"

if [ "$header" != "$constant" ] || [ "$header" != "$script" ] || [ "$header" != "$package" ]; then
  echo "::error::The version differs between the plugin header, the constant, the script and package.json."
  exit 1
fi

if [ -n "${1:-}" ] && [ "$1" != "$header" ]; then
  echo "::error::The tag says $1 but the code says $header."
  exit 1
fi
