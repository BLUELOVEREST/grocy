#!/usr/bin/env sh

set -eu

files='services controllers public/viewjs views config-dist.php'

if rg -n '\$listId\s*=\s*1|\$listId\s*=\s*1\)|\$listId\s*=\s*1,' $files; then
	echo 'Shopping-list code must not default to list ID 1' >&2
	exit 1
fi

if rg -n 'DEFAULT_SHOPPING_LIST_ID|list=1' $files; then
	echo 'Shopping-list code must not use a default-list constant' >&2
	exit 1
fi

if rg -n "shopping_list\(\)->createRow\(\[" services/RecipesService.php >/dev/null && ! rg -Fq "'shopping_list_id' => \$listId" services/RecipesService.php; then
	echo 'Recipe shopping items must have an explicit list ID' >&2
	exit 1
fi

echo 'shopping list no-default contract passed'
