Grocy.ShoppingListItemFormInitialLoadDone = false;

$('.set-shopping-due-date').on('click', function()
{
	$('#due_date').val(moment().add($(this).data('offset'), 'days').format('YYYY-MM-DD'));
});

$('#set-shopping-due-weekend').on('click', function()
{
	var saturday = moment().day(6);
	if (saturday.isBefore(moment(), 'day')) saturday.add(7, 'days');
	$('#due_date').val(saturday.format('YYYY-MM-DD'));
});

$('#clear-shopping-due-date').on('click', function()
{
	$('#due_date').val('');
});

function GetSelectedShoppingListName()
{
	return $('#shopping_list_id').data('shopping-list-name') || $('#shopping_list_id option:selected').text();
}

$('#save-shoppinglist-button').on('click', function(e)
{
	e.preventDefault();

	if (!Grocy.FrontendHelpers.ValidateForm("shoppinglist-form", true))
	{
		return;
	}

	if ($(".combobox-menu-visible").length)
	{
		return;
	}

	var jsonData = $('#shoppinglist-form').serializeJSON();
	if (!jsonData.product_id && (!jsonData.free_text_name || jsonData.free_text_name.trim() === "") && (!jsonData.note || jsonData.note.trim() === ""))
	{
		$("#free_text_name").addClass("is-invalid");
		return;
	}
	else
	{
		$("#free_text_name").removeClass("is-invalid");
	}

	var displayAmount = Number.parseFloat(jsonData.display_amount);
	if (!jsonData.product_id)
	{
		jsonData.amount = jsonData.display_amount;
	}
	delete jsonData.display_amount;

	Grocy.FrontendHelpers.BeginUiBusy("shoppinglist-form");

	if (GetUriParam("flow") === "InplaceAddBarcodeToExistingProduct")
	{
		var jsonDataBarcode = {};
		jsonDataBarcode.barcode = GetUriParam("barcode");
		jsonDataBarcode.product_id = jsonData.product_id;

		Grocy.Api.Post('objects/product_barcodes', jsonDataBarcode,
			function(result)
			{
				$("#flow-info-InplaceAddBarcodeToExistingProduct").addClass("d-none");
				$('#barcode-lookup-disabled-hint').addClass('d-none');
				$('#barcode-lookup-hint').removeClass('d-none');
			},
			function(xhr)
			{
				Grocy.FrontendHelpers.EndUiBusy("shoppinglist-form");
				Grocy.FrontendHelpers.ShowGenericError('Error while saving, probably this item already exists', xhr.response);
			}
		);
	}

	if (GetUriParam("updateexistingproduct") !== undefined)
	{
		jsonData.product_amount = jsonData.amount;
		delete jsonData.amount;

		jsonData.list_id = jsonData.shopping_list_id;
		delete jsonData.shopping_list_id;

		Grocy.Api.Post('stock/shoppinglist/add-product', jsonData,
			function(result)
			{
				Grocy.EditObjectId = result.created_object_id;
				Grocy.Components.UserfieldsForm.Save();

				if (GetUriParam("embedded") !== undefined)
				{
					Grocy.Api.Get('stock/products/' + jsonData.product_id,
						function(productDetails)
						{
							if (GetUriParam("product") !== undefined)
							{
								window.parent.postMessage(WindowMessageBag("ShowSuccessMessage", __t("Added %1$s of %2$s to the shopping list \"%3$s\"", displayAmount.toLocaleString({ minimumFractionDigits: 0, maximumFractionDigits: Grocy.UserSettings.stock_decimal_places_amounts }) + " " + __n(displayAmount, $("#qu_id option:selected").text(), $("#qu_id option:selected").attr("data-qu-name-plural"), true), productDetails.product.name, GetSelectedShoppingListName())), Grocy.BaseUrl);
							}

							window.parent.postMessage(WindowMessageBag("ShoppingListChanged", $("#shopping_list_id").val().toString()), Grocy.BaseUrl);
							window.parent.postMessage(WindowMessageBag("CloseLastModal"), Grocy.BaseUrl);
						},
						function(xhr)
						{
							console.error(xhr);
						}
					);
				}
				else
				{
					window.location.href = U('/shoppinglist?list=' + $("#shopping_list_id").val().toString());
				}
			},
			function(xhr)
			{
				Grocy.FrontendHelpers.EndUiBusy("shoppinglist-form");
				console.error(xhr);
			}
		);
	}
	else if (Grocy.EditMode === 'create')
	{
		var createEndpoint = 'objects/shopping_list';
		var createPayload = jsonData;

		if (!jsonData.product_id && jsonData.free_text_name && jsonData.free_text_name.trim() !== "")
		{
			createEndpoint = 'stock/shoppinglist/add-free-text-item';
			createPayload = {
				free_text_name: jsonData.free_text_name,
				amount: jsonData.amount,
				qu_id: jsonData.qu_id,
				note: jsonData.note,
				due_date: jsonData.due_date,
				list_id: jsonData.shopping_list_id
			};
		}

		Grocy.Api.Post(createEndpoint, createPayload,
			function(result)
			{
				Grocy.EditObjectId = result.created_object_id;
				Grocy.Components.UserfieldsForm.Save();

				if (GetUriParam("embedded") !== undefined)
				{
					if (jsonData.product_id)
					{
						Grocy.Api.Get('stock/products/' + jsonData.product_id,
							function(productDetails)
							{
								if (GetUriParam("product") !== undefined)
								{
									window.parent.postMessage(WindowMessageBag("ShowSuccessMessage", __t("Added %1$s of %2$s to the shopping list \"%3$s\"", displayAmount.toLocaleString({ minimumFractionDigits: 0, maximumFractionDigits: Grocy.UserSettings.stock_decimal_places_amounts }) + " " + __n(displayAmount, $("#qu_id option:selected").text(), $("#qu_id option:selected").attr("data-qu-name-plural"), true), productDetails.product.name, GetSelectedShoppingListName())), Grocy.BaseUrl);
								}

								window.parent.postMessage(WindowMessageBag("ShoppingListChanged", $("#shopping_list_id").val().toString()), Grocy.BaseUrl);
								window.parent.postMessage(WindowMessageBag("CloseLastModal"), Grocy.BaseUrl);
							},
							function(xhr)
							{
								console.error(xhr);
							}
						);
					}
					else
					{
						window.parent.postMessage(WindowMessageBag("ShoppingListChanged", $("#shopping_list_id").val().toString()), Grocy.BaseUrl);
						window.parent.postMessage(WindowMessageBag("CloseLastModal"), Grocy.BaseUrl);
					}
				}
				else
				{
					window.location.href = U('/shoppinglist?list=' + $("#shopping_list_id").val().toString());
				}
			},
			function(xhr)
			{
				Grocy.FrontendHelpers.EndUiBusy("shoppinglist-form");
				console.error(xhr);
			}
		);
	}
	else
	{
		Grocy.Api.Put('objects/shopping_list/' + Grocy.EditObjectId, jsonData,
			function(result)
			{
				Grocy.Components.UserfieldsForm.Save();

				if (GetUriParam("embedded") !== undefined)
				{
					if (jsonData.product_id)
					{
						Grocy.Api.Get('stock/products/' + jsonData.product_id,
							function(productDetails)
							{
								if (GetUriParam("product") !== undefined)
								{
									window.parent.postMessage(WindowMessageBag("ShowSuccessMessage", __t("Added %1$s of %2$s to the shopping list \"%3$s\"", displayAmount.toLocaleString({ minimumFractionDigits: 0, maximumFractionDigits: Grocy.UserSettings.stock_decimal_places_amounts }) + " " + __n(displayAmount, $("#qu_id option:selected").text(), $("#qu_id option:selected").attr("data-qu-name-plural"), true), productDetails.product.name, GetSelectedShoppingListName())), Grocy.BaseUrl);
								}

								window.parent.postMessage(WindowMessageBag("ShoppingListChanged", $("#shopping_list_id").val().toString()), Grocy.BaseUrl);
								window.parent.postMessage(WindowMessageBag("CloseLastModal"), Grocy.BaseUrl);
							},
							function(xhr)
							{
								console.error(xhr);
							}
						);
					}
					else
					{
						window.parent.postMessage(WindowMessageBag("ShoppingListChanged", $("#shopping_list_id").val().toString()), Grocy.BaseUrl);
						window.parent.postMessage(WindowMessageBag("CloseLastModal"), Grocy.BaseUrl);
					}
				}
				else
				{
					window.location.href = U('/shoppinglist?list=' + $("#shopping_list_id").val().toString());
				}
			},
			function(xhr)
			{
				Grocy.FrontendHelpers.EndUiBusy("shoppinglist-form");
				console.error(xhr);
			}
		);
	}
});

Grocy.Components.ProductPicker.GetPicker().on('change', function(e)
{
	var productId = $(e.target).val();

	if (productId)
	{
		Grocy.Components.ProductAmountPicker.AllowAnyQuEnabled = false;

		Grocy.Api.Get('stock/products/' + productId,
			function(productDetails)
			{
				if (!Grocy.ShoppingListItemFormInitialLoadDone)
				{
					Grocy.Components.ProductAmountPicker.Reload(productDetails.product.id, productDetails.quantity_unit_stock.id, true);
				}
				else
				{
					Grocy.Components.ProductAmountPicker.Reload(productDetails.product.id, productDetails.quantity_unit_stock.id);
					Grocy.Components.ProductAmountPicker.SetQuantityUnit(productDetails.default_quantity_unit_purchase.id);
				}

				if (!$("#display_amount").val())
				{
					$("#display_amount").val(1);
					$("#display_amount").trigger("change");
				}

				setTimeout(function()
				{
					$('#display_amount').focus();
				}, Grocy.FormFocusDelay);
				Grocy.FrontendHelpers.ValidateForm('shoppinglist-form');
				Grocy.ShoppingListItemFormInitialLoadDone = true;
			},
			function(xhr)
			{
				console.error(xhr);
			}
		);
	}
	else
	{
		Grocy.Components.ProductAmountPicker.AllowAnyQu(true);
	}

	$("#note").trigger("input");
	$("#product_id").trigger("input");
});

Grocy.FrontendHelpers.ValidateForm('shoppinglist-form');

if (Grocy.EditMode === "edit")
{
	Grocy.Components.ProductPicker.GetPicker().trigger('change');

	if (!Grocy.Components.ProductPicker.GetValue())
	{
		Grocy.Components.ProductAmountPicker.AllowAnyQu(true);
	}
}

if (Grocy.EditMode == "create")
{
	Grocy.ShoppingListItemFormInitialLoadDone = true;
	Grocy.Components.ProductAmountPicker.AllowAnyQu();
}

$('#display_amount').on('focus', function(e)
{
	$(this).select();
});

$('#shoppinglist-form input').keyup(function(event)
{
	Grocy.FrontendHelpers.ValidateForm('shoppinglist-form');
});

$('#shoppinglist-form input').keydown(function(event)
{
	if (event.keyCode === 13) // Enter
	{
		event.preventDefault();

		if (!Grocy.FrontendHelpers.ValidateForm('shoppinglist-form'))
		{
			return false;
		}
		else
		{
			$('#save-shoppinglist-button').click();
		}
	}
});

if (GetUriParam("list") !== undefined)
{
	$("#shopping_list_id").val(GetUriParam("list"));
}

if (GetUriParam("amount") !== undefined)
{
	$("#display_amount").val(Number.parseFloat(GetUriParam("amount")));
	RefreshLocaleNumberInput();
	$(".input-group-productamountpicker").trigger("change");
	Grocy.FrontendHelpers.ValidateForm('shoppinglist-form');
}

if (!Grocy.Components.ProductPicker.InAnyFlow())
{
	if (GetUriParam("product") !== undefined || Grocy.EditMode == "edit")
	{
		if (GetUriParam("updateexistingproduct") != null)
		{
			Grocy.Components.ProductPicker.GetPicker().trigger('change');
		}

		setTimeout(function()
		{
			$("#display_amount").focus();
		}, Grocy.FormFocusDelay);
	}
	else
	{
		setTimeout(function()
		{
			Grocy.Components.ProductPicker.GetInputElement().focus();
		}, Grocy.FormFocusDelay);
	}
}
else
{
	Grocy.Components.ProductPicker.GetPicker().trigger('change');

	if (Grocy.Components.ProductPicker.InProductModifyWorkflow())
	{
		setTimeout(function()
		{
			Grocy.Components.ProductPicker.GetInputElement().focus();
		}, Grocy.FormFocusDelay);
	}
}

var itemIdentityFields = $("#product_id,#free_text_name,#note");
function UpdateShoppingListItemRequiredFields()
{
	var hasAnyItemIdentity = false;
	itemIdentityFields.each(function()
	{
		if ($(this).val() && $(this).val().trim() !== "")
		{
			hasAnyItemIdentity = true;
		}
	});

	// The product association is optional for free-form shopping list items.
	$("#product_id,#note").prop('required', false);
	$("#free_text_name").prop('required', !hasAnyItemIdentity);
	Grocy.FrontendHelpers.ValidateForm('shoppinglist-form');
}
itemIdentityFields.on('input change', UpdateShoppingListItemRequiredFields);
UpdateShoppingListItemRequiredFields();

if (GetUriParam("product-name") != null)
{
	Grocy.Components.ProductPicker.GetPicker().trigger('change');
}

Grocy.Components.UserfieldsForm.Load();
