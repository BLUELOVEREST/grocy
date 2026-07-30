#!/usr/bin/env sh

set -eu

rg -Fq "RenderPage(\$response, 'shoppinglists'" controllers/StockController.php
test -f views/shoppinglists.blade.php
test -f public/viewjs/shoppinglists.js
rg -Fq 'shopping-list-card-link' views/shoppinglists.blade.php
rg -Fq '.night-mode .shopping-list-card-link' public/css/grocy_night_mode.css
rg -Fq 'selectedShoppingListId' views/shoppinglistitemform.blade.php
if rg -n 'value="1"|selectedShoppingListId == 1' views/shoppinglist.blade.php views/shoppinglistitemform.blade.php; then
	echo 'Web shopping flow must not assume list ID 1' >&2
	exit 1
fi

echo 'shopping list Web-flow contract passed'
