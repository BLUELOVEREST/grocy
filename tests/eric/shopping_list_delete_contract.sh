#!/usr/bin/env sh

set -eu

rg -Fq "'Cannot delete a non-empty shopping list', 409" controllers/Api/GenericEntityApiController.php

echo 'shopping list delete contract passed'
