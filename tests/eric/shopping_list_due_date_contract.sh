#!/usr/bin/env sh

set -eu

rg -q 'ADD due_date DATE' migrations/0260.sql
rg -q "NormalizeShoppingListDueDate" services/StockService.php
rg -Fq "'due_date' => \$dueDate" services/StockService.php
rg -q 'ValidateShoppingListPayload' controllers/Api/GenericEntityApiController.php
rg -q 'name="due_date"' views/shoppinglistitemform.blade.php
rg -q 'due_date: jsonData.due_date' public/viewjs/shoppinglistitemform.js
rg -Fq 'set-shopping-due-weekend' public/viewjs/shoppinglistitemform.js
rg -Fq "__t('Overdue')" views/shoppinglist.blade.php

echo 'shopping list due-date contract passed'
