#!/usr/bin/env bash

cat /var/www/html/config/env.php | sed -e "s/'YY_TEST_BROWSER' => '.*'/'YY_TEST_BROWSER' => '$1'/" > /tmp/env \
  && cp /tmp/env /var/www/html/config/env.php
