function redirectAfterProductSave(productId, location)
{
	if (Grocy.ProductEditFormRedirectUri == "reload")
	{
		window.location.reload();
		return;
	}

	var returnTo = GetUriParam('returnto');
	if (GetUriParam("closeAfterCreation") !== undefined)
	{
		window.close();
	}
	else if (returnTo !== undefined)
	{
		if (GetUriParam("flow") !== undefined)
		{
			window.location.href = U(returnTo) + '&product-name=' + encodeURIComponent($('#name').val());
		}
		else
		{
			window.location.href = U(returnTo);
		}
	}
	else
	{
		window.location.href = U(location + productId);
	}
}

function collectProductPropertyTemplateDefinitions()
{
	var definitions = [];
	$('#product-property-template-table tbody tr').each(function(index, row)
	{
		var currentRow = $(row);
		var label = currentRow.find('.product-property-label').val().trim();
		var name = currentRow.find('.product-property-name').val().trim();

		if (label === '' && name === '')
		{
			return;
		}

		definitions.push({
			id: currentRow.attr('data-property-definition-id') || undefined,
			name: name,
			label: label,
			type: currentRow.find('.product-property-type').val(),
			unit: currentRow.find('.product-property-unit').val(),
			options: currentRow.find('.product-property-options').val(),
			input_required: currentRow.find('.product-property-required').prop('checked'),
			sort_number: (index + 1) * 10
		});
	});

	return definitions;
}

function collectProductPropertyValues()
{
	var values = [];
	$('#product-property-values-container .product-property-value').each(function(index, input)
	{
		var currentInput = $(input);
		var value = currentInput.attr('type') === 'checkbox' ? (currentInput.prop('checked') ? '1' : '0') : currentInput.val();
		values.push({
			property_definition_id: currentInput.attr('data-property-definition-id'),
			value: value
		});
	});

	return values;
}

function saveProductPropertyMetadata(productId, jsonData, success, error)
{
	if (jsonData.parent_product_id)
	{
		Grocy.Api.Put('product-properties/' + productId, { values: collectProductPropertyValues() }, success, error);
		return;
	}

	Grocy.Api.Put('product-property-templates/' + productId, { definitions: collectProductPropertyTemplateDefinitions() }, success, error);
}

var foodNutritionLoadComplete = Grocy.EditMode !== 'edit' && !(Grocy.EditMode == 'create' && GetUriParam("copy-of") != undefined);

function refreshFoodNutritionSaveState()
{
	$('.save-product-button').prop('disabled', !foodNutritionLoadComplete);
}

function setFoodNutritionLoadComplete(isComplete)
{
	foodNutritionLoadComplete = isComplete;
	refreshFoodNutritionSaveState();
}

function collectFoodNutritionPayload()
{
	return {
		is_food: $('#is_food').prop('checked'),
		basis_amount: $('#nutrition_basis_amount').val(),
		basis_qu_id: $('#nutrition_basis_qu_id').val(),
		calories: $('#nutrition_calories').val(),
		protein: $('#nutrition_protein').val(),
		fat: $('#nutrition_fat').val(),
		carbohydrates: $('#nutrition_carbohydrates').val(),
		stock_to_basis_factor: $('#stock-to-basis-conversion-fields').hasClass('d-none') ? null : $('#stock_to_basis_factor').val()
	};
}

function saveFoodNutrition(productId, success, error)
{
	Grocy.Api.Put('food-nutrition/' + productId, collectFoodNutritionPayload(), success, error);
}

function saveProductPicture(result, location, jsonData)
{
	var productId = Grocy.EditObjectId || result.created_object_id;
	Grocy.EditObjectId = productId; // Grocy.EditObjectId is not yet set when adding a product

	saveFoodNutrition(productId, function()
	{
		saveProductPropertyMetadata(productId, jsonData, function()
		{
			Grocy.Components.UserfieldsForm.Save(() =>
			{
				if (jsonData.hasOwnProperty("picture_file_name") && !Grocy.DeleteProductPictureOnSave)
				{
					Grocy.Api.UploadFile($("#product-picture")[0].files[0], 'productpictures', jsonData.picture_file_name,
						() => redirectAfterProductSave(productId, location),
						(xhr) =>
						{
							Grocy.FrontendHelpers.EndUiBusy("product-form");
							Grocy.FrontendHelpers.ShowGenericError('Error while saving, probably this item already exists', xhr.response);
						}
					);
				}
				else
				{
					redirectAfterProductSave(productId, location);
				}
			});
		}, function(xhr)
		{
			Grocy.FrontendHelpers.EndUiBusy("product-form");
			Grocy.FrontendHelpers.ShowGenericError('Error while saving product properties', xhr.response);
		});
	}, function(xhr)
	{
		Grocy.FrontendHelpers.EndUiBusy("product-form");
		Grocy.FrontendHelpers.ShowGenericError('Error while saving food nutrition', xhr.response);
	});
}

function removeFoodNutritionFieldsFromProductData(jsonData)
{
	[
		'protein',
		'fat',
		'carbohydrates',
		'nutrition_basis_amount',
		'nutrition_basis_qu_id',
		'nutrition_calories',
		'nutrition_protein',
		'nutrition_fat',
		'nutrition_carbohydrates',
		'stock_to_basis_factor'
	].forEach(function(field)
	{
		if (jsonData.hasOwnProperty(field))
		{
			delete jsonData[field];
		}
	});
}

function populateFoodNutritionFields(result, fallbackProduct)
{
	fallbackProduct = fallbackProduct || {};
	$('#is_food').prop('checked', BoolVal(result.is_food));

	if (result.nutrition != null)
	{
		$('#nutrition_basis_amount').val(result.nutrition.basis_amount);
		$('#nutrition_basis_qu_id').val(result.nutrition.basis_qu_id);
		$('#nutrition_calories').val(result.nutrition.calories);
		$('#nutrition_protein').val(result.nutrition.protein);
		$('#nutrition_fat').val(result.nutrition.fat);
		$('#nutrition_carbohydrates').val(result.nutrition.carbohydrates);
	}
	else
	{
		if (fallbackProduct.basis_amount != null)
		{
			$('#nutrition_basis_amount').val(fallbackProduct.basis_amount);
		}
		if (fallbackProduct.basis_qu_id != null)
		{
			$('#nutrition_basis_qu_id').val(fallbackProduct.basis_qu_id);
		}
		if (fallbackProduct.calories != null)
		{
			$('#nutrition_calories').val(fallbackProduct.calories);
		}
		if (fallbackProduct.protein != null)
		{
			$('#nutrition_protein').val(fallbackProduct.protein);
		}
		if (fallbackProduct.fat != null)
		{
			$('#nutrition_fat').val(fallbackProduct.fat);
		}
		if (fallbackProduct.carbohydrates != null)
		{
			$('#nutrition_carbohydrates').val(fallbackProduct.carbohydrates);
		}
	}

	if (result.stock_to_basis_conversion != null)
	{
		$('#stock_to_basis_factor').val(result.stock_to_basis_conversion.factor);
	}

	refreshNutritionFieldsVisibility();
	Grocy.FrontendHelpers.ValidateForm('product-form');
}

function loadFoodNutrition(productId, fallbackProduct)
{
	setFoodNutritionLoadComplete(false);
	Grocy.Api.Get('food-nutrition/' + productId, function(result)
	{
		populateFoodNutritionFields(result, fallbackProduct);
		setFoodNutritionLoadComplete(true);
	}, function(xhr)
	{
		if (fallbackProduct !== undefined)
		{
			populateFoodNutritionFields({ is_food: fallbackProduct.is_food, nutrition: null, stock_to_basis_conversion: null }, fallbackProduct);
			setFoodNutritionLoadComplete(true);
			return;
		}

		Grocy.FrontendHelpers.ShowGenericError('Error while loading food nutrition; saving is disabled to prevent overwriting existing nutrition data', xhr.response);
		console.error(xhr);
		refreshFoodNutritionSaveState();
	});
}

function selectedQuantityUnitText(selector)
{
	var selectedOption = $(selector + ' option:selected');
	return selectedOption.val() ? selectedOption.text() : '';
}

function refreshNutritionUnitLabels()
{
	var basisAmount = $('#nutrition_basis_amount').val();
	var basisUnit = selectedQuantityUnitText('#nutrition_basis_qu_id');
	var stockUnit = selectedQuantityUnitText('#qu_id_stock');

	$('#nutrition-basis-description').text((basisAmount || '?') + ' ' + (basisUnit || '?'));
	$('#nutrition_energy_qu_info').text(Grocy.EnergyUnit);
	$('#nutrition_protein_qu_info').text('g');
	$('#nutrition_fat_qu_info').text('g');
	$('#nutrition_carbohydrates_qu_info').text('g');
	$('#stock_to_basis_factor_qu_info').text(basisUnit && stockUnit ? basisUnit + ' / ' + stockUnit : '');
	$('#stock-to-basis-stock-unit').text(stockUnit);
	$('#stock-to-basis-factor-label').text($('#stock_to_basis_factor').val() || '?');
	$('#stock-to-basis-basis-unit').text(basisUnit);
}

function refreshNutritionFieldsVisibility()
{
	var isFood = $('#is_food').prop('checked');
	var stockQuId = $('#qu_id_stock').val();
	var basisQuId = $('#nutrition_basis_qu_id').val();
	var showStockToBasisConversion = isFood && stockQuId && basisQuId && stockQuId != basisQuId;

	$('#product-nutrition-fields').toggleClass('d-none', !isFood);
	$('#stock-to-basis-conversion-fields').toggleClass('d-none', !showStockToBasisConversion);
	$('#nutrition_basis_amount, #nutrition_basis_qu_id').prop('required', isFood);
	refreshNutritionUnitLabels();
}

function refreshNutritionFormState()
{
	refreshNutritionFieldsVisibility();
	Grocy.FrontendHelpers.ValidateForm('product-form');
}


$('.save-product-button').on('click', function(e)
{
	e.preventDefault();

	if (!foodNutritionLoadComplete)
	{
		Grocy.FrontendHelpers.ShowGenericError('Food nutrition is still loading; please wait before saving', '');
		return;
	}

	if (!Grocy.FrontendHelpers.ValidateForm("product-form", true))
	{
		return;
	}

	var jsonData = $('#product-form').serializeJSON();
	jsonData.is_food = $("#is_food").prop("checked") ? "1" : "0";
	removeFoodNutritionFieldsFromProductData(jsonData);
	var parentProductId = jsonData.product_id;
	delete jsonData.product_id;
	jsonData.parent_product_id = parentProductId;
	Grocy.FrontendHelpers.BeginUiBusy("product-form");

	if ($("#product-picture")[0].files.length > 0)
	{
		jsonData.picture_file_name = RandomString() + CleanFileName($("#product-picture")[0].files[0].name);
	}

	const location = $(e.currentTarget).attr('data-location') == 'return' ? '/products?product=' : '/product/';

	if (Grocy.EditMode == 'create')
	{
		Grocy.Api.Post('objects/products', jsonData,
			(result) => saveProductPicture(result, location, jsonData),
			(xhr) =>
			{
				Grocy.FrontendHelpers.EndUiBusy("product-form");
				Grocy.FrontendHelpers.ShowGenericError('Error while saving, probably this item already exists', xhr.response);
			});
		return;
	}

	if (Grocy.DeleteProductPictureOnSave)
	{
		jsonData.picture_file_name = null;

		Grocy.Api.DeleteFile(Grocy.ProductPictureFileName, 'productpictures',
			function(result)
			{
				// Nothing to do
			},
			function(xhr)
			{
				Grocy.FrontendHelpers.EndUiBusy("product-form");
				Grocy.FrontendHelpers.ShowGenericError('Error while saving, probably this item already exists', xhr.response);
			}
		);
	}

	Grocy.Api.Put('objects/products/' + Grocy.EditObjectId, jsonData,
		(result) => saveProductPicture(result, location, jsonData),
		function(xhr)
		{
			Grocy.FrontendHelpers.EndUiBusy("product-form");
			console.error(xhr);
		}
	);
});

if (GetUriParam("flow") == "InplaceNewProductWithName")
{
	$('#name').val(GetUriParam("name"));
}

if (GetUriParam("flow") !== undefined || GetUriParam("returnto") !== undefined)
{
	$("#save-hint").addClass("d-none");
	$(".save-product-button[data-location='return']").addClass("d-none");
}

$('.input-group-qu').on('change', function(e)
{
	$("#tare_weight_qu_info").text($("#qu_id_stock option:selected").text());
	$("#quick_consume_qu_info").text($("#qu_id_stock option:selected").text());
	$("#quick_open_qu_info").text($("#qu_id_stock option:selected").text());
	refreshNutritionFieldsVisibility();

	Grocy.FrontendHelpers.ValidateForm('product-form');
});

$("#is_food").on("change", function()
{
	refreshNutritionFormState();
});

$("#nutrition_basis_amount, #stock_to_basis_factor").on("change keyup", function()
{
	refreshNutritionFormState();
});

$("#nutrition_basis_qu_id").on("change", function()
{
	$("#stock_to_basis_factor").val("");
	refreshNutritionFormState();
});

refreshFoodNutritionSaveState();

if (Grocy.EditMode === 'edit')
{
	loadFoodNutrition(Grocy.EditObjectId, Grocy.ProductFoodNutritionFallback);
}
else
{
	refreshNutritionFieldsVisibility();
}

$('#product-form input').keyup(function(event)
{
	Grocy.FrontendHelpers.ValidateForm('product-form');
	$(".input-group-qu").trigger("change");
	$("#product-form select").trigger("select");

	if (!Grocy.FrontendHelpers.ValidateForm('product-form'))
	{
		$("#qu-conversion-add-button").addClass("disabled");
		$("#barcode-add-button").addClass("disabled");
	}
	else
	{
		$("#qu-conversion-add-button").removeClass("disabled");
	}
});

$('#location_id').change(function(event)
{
	Grocy.FrontendHelpers.ValidateForm('product-form');
});

$('#product-form input').keydown(function(event)
{
	if (event.keyCode === 13) // Enter
	{
		event.preventDefault();

		if (!Grocy.FrontendHelpers.ValidateForm('product-form'))
		{
			return false;
		}
		else
		{
			$('.default-submit-button').click();
		}
	}
});

$("#enable_tare_weight_handling").on("click", function()
{
	if (this.checked)
	{
		$("#tare_weight").removeAttr("disabled");
	}
	else
	{
		$("#tare_weight").attr("disabled", "");
	}

	Grocy.FrontendHelpers.ValidateForm("product-form");
});

$("#product-picture").on("change", function(e)
{
	$("#product-picture-label").removeClass("d-none");
	$("#product-picture-label-none").addClass("d-none");
	$("#delete-current-product-picture-on-save-hint").addClass("d-none");
	$("#current-product-picture").addClass("d-none");
	Grocy.DeleteProductPictureOnSave = false;
});

Grocy.DeleteProductPictureOnSave = false;
$("#delete-current-product-picture-button").on("click", function(e)
{
	Grocy.DeleteProductPictureOnSave = true;
	$("#current-product-picture").addClass("d-none");
	$("#delete-current-product-picture-on-save-hint").removeClass("d-none");
	$("#product-picture-label").addClass("d-none");
	$("#product-picture-label-none").removeClass("d-none");
});

var quConversionsTable = $('#qu-conversions-table-products').DataTable({
	'order': [[1, 'asc']],
	'columnDefs': [
		{ 'orderable': false, 'targets': 0 },
		{ 'searchable': false, "targets": 0 }
	].concat($.fn.dataTable.defaults.columnDefs)
});
$('#qu-conversions-table-products tbody').removeClass("d-none");
quConversionsTable.columns.adjust().draw();

var barcodeTable = $('#barcode-table').DataTable({
	'order': [[1, 'asc']],
	"orderFixed": [[1, 'asc']],
	'columnDefs': [
		{ 'orderable': false, 'targets': 0 },
		{ 'searchable': false, "targets": 0 },
		{ 'visible': false, 'targets': 5 },
		{ 'visible': false, 'targets': 6 }
	].concat($.fn.dataTable.defaults.columnDefs)
});
$('#barcode-table tbody').removeClass("d-none");
barcodeTable.columns.adjust().draw();

function normalizeProductPropertyName(value)
{
	var normalized = value.toLowerCase().replace(/[^a-z0-9_]+/g, '_').replace(/^_+|_+$/g, '');
	if (normalized === '' || /^[0-9]/.test(normalized))
	{
		normalized = 'property_' + normalized;
	}

	return normalized;
}

function createProductPropertyTemplateRow(definition)
{
	definition = definition || {};
	var row = $('<tr class="product-property-template-row"></tr>');
	if (definition.id !== undefined && definition.id !== null)
	{
		row.attr('data-property-definition-id', definition.id);
	}

	row.append($('<td></td>').append($('<input type="text" class="form-control form-control-sm product-property-name">').val(definition.name || '')));
	row.append($('<td></td>').append($('<input type="text" class="form-control form-control-sm product-property-label">').val(definition.label || '')));

	var typeSelect = $('<select class="custom-control custom-select custom-select-sm product-property-type"></select>');
	[
		{ value: 'text', label: 'Text' },
		{ value: 'number', label: 'Number' },
		{ value: 'select', label: 'Select list' },
		{ value: 'checkbox', label: 'Checkbox' }
	].forEach(function(option)
	{
		typeSelect.append($('<option></option>').attr('value', option.value).text(__t(option.label)));
	});
	typeSelect.val(definition.type || 'text');
	row.append($('<td></td>').append(typeSelect));

	row.append($('<td></td>').append($('<input type="text" class="form-control form-control-sm product-property-unit">').val(definition.unit || '')));
	row.append($('<td></td>').append($('<textarea class="form-control form-control-sm product-property-options" rows="1"></textarea>').val(definition.options || '')));
	row.append($('<td class="text-center"></td>').append($('<input type="checkbox" class="product-property-required">').prop('checked', BoolVal(definition.input_required))));
	row.append($('<td class="text-right"></td>').append($('<button type="button" class="btn btn-sm btn-danger product-property-delete-row-button"><i class="fa-solid fa-trash"></i></button>')));

	return row;
}

function renderProductPropertyTemplate(definitions)
{
	var tableBody = $('#product-property-template-table tbody');
	tableBody.empty();

	definitions.forEach(function(definition)
	{
		tableBody.append(createProductPropertyTemplateRow(definition));
	});
}

function renderProductPropertyValues(definitions)
{
	var container = $('#product-property-values-container');
	container.empty();

	if (definitions.length === 0)
	{
		container.append($('<p class="text-muted mb-0"></p>').text(__t('No property template is defined for the selected parent product')));
		return;
	}

	definitions.forEach(function(definition)
	{
		var group = $('<div class="form-group"></div>');
		var labelText = definition.label + (definition.unit ? ' (' + definition.unit + ')' : '');
		group.append($('<label></label>').text(labelText));

		var input;
		if (definition.type === 'select')
		{
			input = $('<select class="custom-control custom-select product-property-value"></select>');
			input.append($('<option></option>'));
			(definition.options || '').split(/\r?\n/).forEach(function(option)
			{
				option = option.trim();
				if (option !== '')
				{
					input.append($('<option></option>').attr('value', option).text(option));
				}
			});
			input.val(definition.value || '');
		}
		else if (definition.type === 'checkbox')
		{
			input = $('<input type="checkbox" class="product-property-value">').prop('checked', BoolVal(definition.value));
			group = $('<div class="form-group custom-control custom-checkbox"></div>');
			input.addClass('form-check-input custom-control-input').attr('id', 'product-property-value-' + definition.id);
			group.append(input);
			group.append($('<label class="form-check-label custom-control-label"></label>').attr('for', 'product-property-value-' + definition.id).text(labelText));
		}
		else
		{
			input = $('<input class="form-control product-property-value">').attr('type', definition.type === 'number' ? 'number' : 'text').val(definition.value || '');
		}

		input.attr('data-property-definition-id', definition.id);
		if (BoolVal(definition.input_required))
		{
			input.attr('required', 'required');
		}

		if (definition.type !== 'checkbox')
		{
			group.append(input);
		}

		container.append(group);
	});
}

function loadProductPropertyUi(parentProductId)
{
	if (parentProductId)
	{
		$('#product-property-template-section').addClass('d-none');
		$('#product-property-values-section').removeClass('d-none');

		if (Grocy.EditMode === 'edit' && parentProductId == Grocy.InitialParentProductId)
		{
			Grocy.Api.Get('product-properties/' + Grocy.EditObjectId, function(result)
			{
				renderProductPropertyValues(result.definitions || []);
			}, function(xhr)
			{
				console.error(xhr);
			});
		}
		else
		{
			Grocy.Api.Get('product-property-templates/' + parentProductId, function(definitions)
			{
				renderProductPropertyValues(definitions || []);
			}, function(xhr)
			{
				console.error(xhr);
			});
		}
		return;
	}

	$('#product-property-values-section').addClass('d-none');
	$('#product-property-template-section').removeClass('d-none');

	if (Grocy.EditMode === 'edit')
	{
		Grocy.Api.Get('product-property-templates/' + Grocy.EditObjectId, function(definitions)
		{
			renderProductPropertyTemplate(definitions || []);
		}, function(xhr)
		{
			console.error(xhr);
		});
	}
}

$('#add-product-property-definition-row').on('click', function(e)
{
	e.preventDefault();
	$('#product-property-template-table tbody').append(createProductPropertyTemplateRow());
});

$(document).on('click', '.product-property-delete-row-button', function(e)
{
	e.preventDefault();
	$(e.currentTarget).closest('tr').remove();
});

$(document).on('blur', '.product-property-label', function(e)
{
	var row = $(e.currentTarget).closest('tr');
	var nameInput = row.find('.product-property-name');
	if (nameInput.val().trim() === '')
	{
		nameInput.val(normalizeProductPropertyName($(e.currentTarget).val()));
	}
});

Grocy.Components.UserfieldsForm.Load();
$("#name").trigger("keyup");
$('.input-group-qu').trigger('change');
Grocy.FrontendHelpers.ValidateForm('product-form');
setTimeout(function()
{
	$('#name').focus();
}, Grocy.FormFocusDelay);

$(document).on('click', '.product-grocycode-label-print', function(e)
{
	e.preventDefault();

	var productId = $(e.currentTarget).attr('data-product-id');
	Grocy.Api.Get('stock/products/' + productId + '/printlabel', function(labelData)
	{
		if (Grocy.Webhooks.labelprinter !== undefined)
		{
			Grocy.FrontendHelpers.RunWebhook(Grocy.Webhooks.labelprinter, labelData);
		}
	});
});

$(document).on('click', '.qu-conversion-delete-button', function(e)
{
	var objectId = $(e.currentTarget).attr('data-qu-conversion-id');

	bootbox.confirm({
		message: __t('Are you sure you want to remove this conversion?'),
		closeButton: false,
		buttons: {
			confirm: {
				label: __t('Yes'),
				className: 'btn-success'
			},
			cancel: {
				label: __t('No'),
				className: 'btn-danger'
			}
		},
		callback: function(result)
		{
			if (result === true)
			{
				Grocy.Api.Delete('objects/quantity_unit_conversions/' + objectId, {},
					function(result)
					{
						Grocy.ProductEditFormRedirectUri = "reload";
						$('#save-product-button').click();
					},
					function(xhr)
					{
						console.error(xhr);
					}
				);
			}
		}
	});
});

$(document).on('click', '.barcode-delete-button', function(e)
{
	var objectId = $(e.currentTarget).attr('data-barcode-id');

	bootbox.confirm({
		message: __t('Are you sure you want to remove this barcode?'),
		closeButton: false,
		buttons: {
			confirm: {
				label: __t('Yes'),
				className: 'btn-success'
			},
			cancel: {
				label: __t('No'),
				className: 'btn-danger'
			}
		},
		callback: function(result)
		{
			if (result === true)
			{
				Grocy.Api.Delete('objects/product_barcodes/' + objectId, {},
					function(result)
					{
						Grocy.ProductEditFormRedirectUri = "reload";
						$('#save-product-button').click();
					},
					function(xhr)
					{
						console.error(xhr);
					}
				);
			}
		}
	});
});

var quIdStockBefore = $("#qu_id_stock").val();
$('#qu_id_stock').change(function(e)
{
	// Preset qu_id_purchase / qu_id_consume / qu_id_price by qu_id_stock if unset or identical

	var quIdStock = $('#qu_id_stock');
	var quIdPurchase = $('#qu_id_purchase');
	var quIdConsume = $('#qu_id_consume');
	var quIdPrice = $('#qu_id_price');

	if (quIdStockBefore != quIdStock.val())
	{
		$("#stock_to_basis_factor").val("");
	}

	if (quIdPurchase[0].selectedIndex === 0 && quIdStock[0].selectedIndex !== 0 || quIdStockBefore == quIdPurchase.val())
	{
		quIdPurchase[0].selectedIndex = quIdStock[0].selectedIndex;
	}

	if (quIdConsume[0].selectedIndex === 0 && quIdStock[0].selectedIndex !== 0 || quIdStockBefore == quIdConsume.val())
	{
		quIdConsume[0].selectedIndex = quIdStock[0].selectedIndex;
	}

	if (quIdPrice[0].selectedIndex === 0 && quIdStock[0].selectedIndex !== 0 || quIdStockBefore == quIdPrice.val())
	{
		quIdPrice[0].selectedIndex = quIdStock[0].selectedIndex;
	}

	quIdStockBefore = quIdStock.val();

	refreshNutritionFormState();
	Grocy.FrontendHelpers.ValidateForm('product-form');
});

$(window).on("message", function(e)
{
	var data = e.originalEvent.data;

	if (data.Message === "ProductBarcodesChanged" || data.Message === "ProductQUConversionChanged")
	{
		window.location.reload();
	}
});

if (Grocy.EditMode == "create" && GetUriParam("copy-of") != undefined)
{
	Grocy.Api.Get('objects/products/' + GetUriParam("copy-of"),
		function(sourceProduct)
		{
			if (sourceProduct.parent_product_id != null)
			{
				Grocy.Components.ProductPicker.SetId(sourceProduct.parent_product_id);
			}
			if (sourceProduct.description)
			{
				$("#description").summernote("pasteHTML", sourceProduct.description);
			}
			$("#location_id").val(sourceProduct.location_id);
			if (sourceProduct.shopping_location_id != null)
			{
				Grocy.Components.ShoppingLocationPicker.SetId(sourceProduct.shopping_location_id);
			}
			$("#min_stock_amount").val(sourceProduct.min_stock_amount);
			if (BoolVal(sourceProduct.cumulate_min_stock_amount_of_sub_products))
			{
				$("#cumulate_min_stock_amount_of_sub_products").prop("checked", true);
			}
			$("#default_best_before_days").val(sourceProduct.default_best_before_days);
			$("#default_best_before_days_after_open").val(sourceProduct.default_best_before_days_after_open);
			if (sourceProduct.product_group_id != null)
			{
				$("#product_group_id").val(sourceProduct.product_group_id);
			}
			$("#qu_id_stock").val(sourceProduct.qu_id_stock);
			$("#qu_id_purchase").val(sourceProduct.qu_id_purchase);
			if (BoolVal(sourceProduct.enable_tare_weight_handling))
			{
				$("#enable_tare_weight_handling").prop("checked", true);
			}
			$("#tare_weight").val(sourceProduct.tare_weight);
			if (BoolVal(sourceProduct.not_check_stock_fulfillment_for_recipes))
			{
				$("#not_check_stock_fulfillment_for_recipes").prop("checked", true);
			}
			if (BoolVal(sourceProduct.is_food))
			{
				$("#is_food").prop("checked", true);
			}
			loadFoodNutrition(GetUriParam("copy-of"), {
				is_food: sourceProduct.is_food,
				basis_amount: 1,
				basis_qu_id: sourceProduct.qu_id_stock,
				calories: sourceProduct.calories,
				protein: sourceProduct.protein,
				fat: sourceProduct.fat,
				carbohydrates: sourceProduct.carbohydrates
			});
			$("#default_best_before_days_after_freezing").val(sourceProduct.default_best_before_days_after_freezing);
			$("#default_best_before_days_after_thawing").val(sourceProduct.default_best_before_days_after_thawing);
			$("#quick_consume_amount").val(sourceProduct.quick_consume_amount);
			$("#quick_open_amount").val(sourceProduct.quick_open_amount);
			$("#default_consume_location_id").val(sourceProduct.default_consume_location_id);
			if (BoolVal(sourceProduct.no_own_stock))
			{
				$("#no_own_stock").prop("checked", true);
			}
			if (BoolVal(sourceProduct.hide_on_stock_overview))
			{
				$("#hide_on_stock_overview").prop("checked", true);
			}
			if (BoolVal(sourceProduct.auto_reprint_stock_label))
			{
				$("#auto_reprint_stock_label").prop("checked", true);
			}
			$("#default_stock_label_type").val(sourceProduct.default_stock_label_type);
			if (BoolVal(sourceProduct.move_on_open))
			{
				$("#move_on_open").prop("checked", true);
			}
			if (BoolVal(sourceProduct.treat_opened_as_out_of_stock))
			{
				$("#treat_opened_as_out_of_stock").prop("checked", true);
			}

			Grocy.FrontendHelpers.ValidateForm('product-form');
		},
		function(xhr)
		{
			Grocy.FrontendHelpers.ShowGenericError('Error while loading source product; saving is disabled to prevent overwriting copied nutrition data', xhr.response);
			console.error(xhr);
		}
	);
}
else if (Grocy.EditMode === 'create')
{
	if (Grocy.UserSettings.product_presets_location_id.toString() !== '-1')
	{
		$("#location_id").val(Grocy.UserSettings.product_presets_location_id);
	}

	if (Grocy.UserSettings.product_presets_product_group_id.toString() !== '-1')
	{
		$("#product_group_id").val(Grocy.UserSettings.product_presets_product_group_id);
	}

	if (Grocy.UserSettings.product_presets_qu_id.toString() !== '-1')
	{
		$("select.input-group-qu").val(Grocy.UserSettings.product_presets_qu_id);
	}

	if (Grocy.UserSettings.product_presets_default_due_days.toString() !== '0')
	{
		$("#default_best_before_days").val(Grocy.UserSettings.product_presets_default_due_days);
	}

	if (Grocy.FeatureFlags.GROCY_FEATURE_FLAG_STOCK_PRODUCT_OPENED_TRACKING)
	{
		$("#treat_opened_as_out_of_stock").prop("checked", BoolVal(Grocy.UserSettings.product_presets_treat_opened_as_out_of_stock));
	}

	if (Grocy.FeatureFlags.GROCY_FEATURE_FLAG_LABEL_PRINTER)
	{
		$("#default_stock_label_type").val(Grocy.UserSettings.product_presets_default_stock_label_type);
	}
}

Grocy.InitialParentProductId = Grocy.Components.ProductPicker.GetPicker().val();

Grocy.Components.ProductPicker.GetPicker().on('change', function(e)
{
	var parentProductId = $(e.target).val();
	loadProductPropertyUi(parentProductId);

	if (parentProductId)
	{
		Grocy.Api.Get('objects/products/' + parentProductId,
			function(parentProduct)
			{
				if (BoolVal(parentProduct.cumulate_min_stock_amount_of_sub_products))
				{

					$("#min_stock_amount").attr("disabled", "");
				}
				else
				{
					$('#min_stock_amount').removeAttr("disabled");
				}
			},
			function(xhr)
			{
				console.error(xhr);
			}
		);
	}
	else
	{
		$('#min_stock_amount').removeAttr("disabled");
	}
});

Grocy.FrontendHelpers.ValidateForm("product-form");
Grocy.Components.ProductPicker.GetPicker().trigger("change");

if (Grocy.EditMode == "edit")
{
	$(".save-product-button").toggleClass("default-submit-button");
}
