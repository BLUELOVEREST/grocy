var CurrentProductDetails;
var InitialProductAmountPickerLoadDone = false;

$('#save-shoppinglistitemstock-button').on('click', function (e)
{
	e.preventDefault();

	if (!Grocy.FrontendHelpers.ValidateForm("shoppinglistitemstock-form", true))
	{
		return;
	}

	if ($(".combobox-menu-visible").length)
	{
		return;
	}

	var jsonForm = $('#shoppinglistitemstock-form').serializeJSON();
	if (!jsonForm.product_id)
	{
		return;
	}

	var jsonData = {
		product_id: jsonForm.product_id,
		amount: jsonForm.amount,
		note: jsonForm.note
	};

	if (Grocy.FeatureFlags.GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING)
	{
		jsonData.price = Number.parseFloat(jsonForm.price * $("#qu_id option:selected").attr("data-qu-factor")).toFixed(Grocy.UserSettings.stock_decimal_places_prices_input);
	}
	else
	{
		jsonData.price = jsonForm.price;
	}

	if (Grocy.Components.DateTimePicker)
	{
		jsonData.best_before_date = Grocy.Components.DateTimePicker.GetValue();
	}

	if (Grocy.Components.DateTimePicker2)
	{
		jsonData.purchased_date = Grocy.Components.DateTimePicker2.GetValue();
	}

	if (Grocy.FeatureFlags.GROCY_FEATURE_FLAG_STOCK_LOCATION_TRACKING)
	{
		jsonData.location_id = Grocy.Components.LocationPicker.GetValue();
	}

	if (Grocy.FeatureFlags.GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING)
	{
		jsonData.shopping_location_id = Grocy.Components.ShoppingLocationPicker.GetValue();
	}

	Grocy.FrontendHelpers.BeginUiBusy("shoppinglistitemstock-form");

	Grocy.Api.Post('stock/shoppinglist/items/' + Grocy.ShoppingListItemId + '/add-to-stock', jsonData,
		function (result)
		{
			Grocy.EditObjectId = result.transaction_id;
			Grocy.Components.UserfieldsForm.Save(function ()
			{
				if (GetUriParam("embedded") !== undefined)
				{
					Grocy.GetTopmostWindow().postMessage(WindowMessageBag("BroadcastMessage", WindowMessageBag("ProductChanged", jsonForm.product_id)), Grocy.BaseUrl);
					window.parent.postMessage(WindowMessageBag("AfterItemAdded", Grocy.ShoppingListItemId), Grocy.BaseUrl);
					window.parent.postMessage(WindowMessageBag("ShowSuccessMessage", __t('Added shopping list item to stock')), Grocy.BaseUrl);
					window.parent.postMessage(WindowMessageBag("Ready"), Grocy.BaseUrl);
				}
				else
				{
					window.location.href = U('/shoppinglist');
				}
			});
		},
		function (xhr)
		{
			Grocy.FrontendHelpers.EndUiBusy("shoppinglistitemstock-form");
			console.error(xhr);
		}
	);
});

if (Grocy.Components.ProductPicker !== undefined)
{
	Grocy.Components.ProductPicker.GetPicker().on('change', function (e)
	{
		var productId = $(e.target).val();

		if (productId)
		{
			Grocy.Api.Get('stock/products/' + productId,
				function (productDetails)
				{
					CurrentProductDetails = productDetails;

					if (!InitialProductAmountPickerLoadDone)
					{
						Grocy.Components.ProductAmountPicker.Reload(productDetails.product.id, productDetails.quantity_unit_stock.id, true);
						InitialProductAmountPickerLoadDone = true;
					}
					else
					{
						Grocy.Components.ProductAmountPicker.Reload(productDetails.product.id, productDetails.quantity_unit_stock.id);
						Grocy.Components.ProductAmountPicker.SetQuantityUnit(productDetails.default_quantity_unit_purchase.id);
					}

					$(".input-group-productamountpicker").trigger("change");

					if (Grocy.FeatureFlags.GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING)
					{
						if (productDetails.last_shopping_location_id != null)
						{
							Grocy.Components.ShoppingLocationPicker.SetId(productDetails.last_shopping_location_id);
						}
						else
						{
							Grocy.Components.ShoppingLocationPicker.SetId(productDetails.default_shopping_location_id);
						}

						if (productDetails.last_price == null || productDetails.last_price == 0)
						{
							$("#price").val("");
						}
						else
						{
							$('#price').val((productDetails.last_price / Number.parseFloat($("#qu_id option:selected").attr("data-qu-factor"))).toFixed(Grocy.UserSettings.stock_decimal_places_prices_display));
						}
					}

					if (Grocy.FeatureFlags.GROCY_FEATURE_FLAG_STOCK_LOCATION_TRACKING)
					{
						Grocy.Components.LocationPicker.SetId(productDetails.location.id);
					}

					PrefillBestBeforeDate(productDetails.product, productDetails.location);
					Grocy.Components.ProductCard.Refresh(productId);
					Grocy.FrontendHelpers.ValidateForm('shoppinglistitemstock-form');
					RefreshLocaleNumberInput();
				},
				function (xhr)
				{
					console.error(xhr);
				}
			);
		}

		$("#product_id").trigger("input");
	});
}

function PrefillBestBeforeDate(product, location)
{
	if (!Grocy.FeatureFlags.GROCY_FEATURE_FLAG_STOCK_BEST_BEFORE_DATE_TRACKING)
	{
		return;
	}

	if (location == null)
	{
		location = {};
	}

	var shortcutValue = $("#datetimepicker-shortcut").attr("data-datetimepicker-shortcut-value");
	var dueDateCurrent = Grocy.Components.DateTimePicker.GetValue();
	var dueDateDefault = null;
	var dueDateFreezer = null;

	if (product.default_best_before_days != 0)
	{
		dueDateDefault = moment().add(product.default_best_before_days, 'days').format('YYYY-MM-DD');

		if (product.default_best_before_days == -1)
		{
			dueDateDefault = shortcutValue;
		}
	}

	if (Grocy.FeatureFlags.GROCY_FEATURE_FLAG_STOCK_PRODUCT_FREEZING && BoolVal(location.is_freezer) && product.default_best_before_days_after_freezing != 0)
	{
		dueDateFreezer = moment().add(product.default_best_before_days_after_freezing, 'days').format('YYYY-MM-DD');

		if (product.default_best_before_days_after_freezing == -1)
		{
			dueDateFreezer = shortcutValue;
		}
	}

	if (dueDateDefault && !dueDateCurrent)
	{
		if (!$("#datetimepicker-shortcut").is(":checked") && dueDateDefault == shortcutValue)
		{
			$("#datetimepicker-shortcut").click();
		}
		else
		{
			Grocy.Components.DateTimePicker.SetValue(dueDateDefault);
		}
	}

	if (dueDateFreezer && (!dueDateCurrent || dueDateCurrent == dueDateDefault))
	{
		if (!$("#datetimepicker-shortcut").is(":checked") && dueDateFreezer == shortcutValue)
		{
			$("#datetimepicker-shortcut").click();
		}
		else
		{
			Grocy.Components.DateTimePicker.SetValue(dueDateFreezer);
		}
	}
}

function RefreshPriceHint()
{
	if (!Grocy.FeatureFlags.GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING)
	{
		return;
	}

	if ($('#amount').val() == 0 || $('#price').val() == 0)
	{
		$('#price-hint').text("");
		return;
	}

	if ($("#qu_id").attr("data-destination-qu-name") != $("#qu_id option:selected").text())
	{
		var price = Number.parseFloat($('#price').val() * $("#qu_id option:selected").attr("data-qu-factor")).toFixed(Grocy.UserSettings.stock_decimal_places_prices_display);
		$('#price-hint').text(__t('means %1$s per %2$s', price.toLocaleString(undefined, { style: "currency", currency: Grocy.Currency, minimumFractionDigits: Grocy.UserSettings.stock_decimal_places_prices_display, maximumFractionDigits: Grocy.UserSettings.stock_decimal_places_prices_display }), $("#qu_id").attr("data-destination-qu-name")));
	}
	else
	{
		$('#price-hint').text("");
	}
};

if (Grocy.Components.LocationPicker !== undefined)
{
	Grocy.Components.LocationPicker.GetPicker().on('change', function ()
	{
		if (Grocy.FeatureFlags.GROCY_FEATURE_FLAG_STOCK_PRODUCT_FREEZING && CurrentProductDetails)
		{
			Grocy.Api.Get('objects/locations/' + Grocy.Components.LocationPicker.GetValue(),
				function (location)
				{
					PrefillBestBeforeDate(CurrentProductDetails.product, location);
				},
				function (xhr)
				{ }
			);
		}
	});
}

$(".input-group-productamountpicker").trigger("change");
Grocy.FrontendHelpers.ValidateForm('shoppinglistitemstock-form');

if (Grocy.ShoppingListItemProductId)
{
	Grocy.Components.ProductPicker.GetPicker().trigger('change');
}
else if (Grocy.ShoppingListItemFreeTextName)
{
	Grocy.Components.ProductPicker.SetValue(Grocy.ShoppingListItemFreeTextName);
}

$('#display_amount').on('focus', function ()
{
	$(this).select();
});

$('#price').on('focus', function ()
{
	$(this).select();
});

$('#price').on('keyup', function ()
{
	RefreshPriceHint();
});

$('#display_amount').on('change', function ()
{
	RefreshPriceHint();
	Grocy.FrontendHelpers.ValidateForm('shoppinglistitemstock-form');
});

$('#qu_id').on('change', function ()
{
	RefreshPriceHint();
});

$('#shoppinglistitemstock-form input').keyup(function ()
{
	Grocy.FrontendHelpers.ValidateForm('shoppinglistitemstock-form');
});

$('#shoppinglistitemstock-form input').keydown(function (event)
{
	if (event.keyCode === 13) // Enter
	{
		event.preventDefault();

		if (!Grocy.FrontendHelpers.ValidateForm('shoppinglistitemstock-form'))
		{
			return false;
		}
		else
		{
			$('#save-shoppinglistitemstock-button').click();
		}
	}
});

if (Grocy.Components.DateTimePicker)
{
	Grocy.Components.DateTimePicker.GetInputElement().on('change', function ()
	{
		Grocy.FrontendHelpers.ValidateForm('shoppinglistitemstock-form');
	});

	Grocy.Components.DateTimePicker.GetInputElement().on('keypress', function ()
	{
		Grocy.FrontendHelpers.ValidateForm('shoppinglistitemstock-form');
	});
}

if (Grocy.Components.DateTimePicker2)
{
	Grocy.Components.DateTimePicker2.GetInputElement().on('change', function ()
	{
		Grocy.FrontendHelpers.ValidateForm('shoppinglistitemstock-form');
	});

	Grocy.Components.DateTimePicker2.GetInputElement().on('keypress', function ()
	{
		Grocy.FrontendHelpers.ValidateForm('shoppinglistitemstock-form');
	});

	Grocy.Components.DateTimePicker2.GetInputElement().trigger("input");
}

Grocy.Components.UserfieldsForm.Load();
