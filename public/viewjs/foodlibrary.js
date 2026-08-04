function FoodLibraryEscape(value)
{
	if (value === null || value === undefined)
	{
		return "";
	}

	return value.toString().escapeHTML();
}

function FoodLibraryFormatNumber(value)
{
	if (value === null || value === undefined || value === "")
	{
		return "";
	}

	var numberValue = Number(value);
	if (Number.isNaN(numberValue))
	{
		return FoodLibraryEscape(value);
	}

	return numberValue.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: Grocy.UserSettings.stock_decimal_places_amounts });
}

function FoodLibraryNutritionValue(food, key)
{
	if (!food.nutrition)
	{
		return "";
	}

	return FoodLibraryFormatNumber(food.nutrition[key]);
}

function FoodLibraryAliases(food)
{
	if (!Array.isArray(food.aliases) || food.aliases.length === 0)
	{
		return "";
	}

	return food.aliases.map(function(alias)
	{
		var escapedAlias = FoodLibraryEscape(alias);
		if (food.matched_alias && alias === food.matched_alias)
		{
			return '<span class="badge badge-info">' + escapedAlias + '</span>';
		}

		return '<span class="badge badge-secondary">' + escapedAlias + '</span>';
	}).join(" ");
}

function FoodLibraryNutritionBasis(food)
{
	if (!food.nutrition || food.nutrition.basis_amount === null || !food.nutrition.basis_unit)
	{
		return "";
	}

	return FoodLibraryFormatNumber(food.nutrition.basis_amount) + " " + FoodLibraryEscape(food.nutrition.basis_unit.name);
}

function FoodLibrarySource(food)
{
	if (!food.source)
	{
		return "";
	}

	var parts = [];
	if (food.source.provider)
	{
		parts.push(food.source.provider);
	}
	if (food.source.external_id)
	{
		parts.push(food.source.external_id);
	}
	if (food.source.category)
	{
		parts.push(food.source.category);
	}

	return FoodLibraryEscape(parts.join(" / "));
}

var foodLibraryTable = $("#food-library-table").DataTable({
	"processing": true,
	"serverSide": true,
	"paging": true,
	"searching": false,
	"pageLength": 20,
	"order": [[0, "asc"]],
	"ajax": function(data, callback)
	{
		var pageSize = data.length > 0 ? data.length : 20;
		var page = Math.floor(data.start / pageSize) + 1;
		var query = $("#food-library-search").val();
		var apiFunction = "eric/foods/search?query=" + encodeURIComponent(query) + "&page=" + encodeURIComponent(page) + "&page_size=" + encodeURIComponent(pageSize);

		Grocy.Api.Get(apiFunction, function(result)
		{
			callback({
				draw: data.draw,
				recordsTotal: result.pagination.totalCount,
				recordsFiltered: result.pagination.totalCount,
				data: result.foods
			});
		}, function(xhr)
		{
			console.error(xhr);
			callback({
				draw: data.draw,
				recordsTotal: 0,
				recordsFiltered: 0,
				data: []
			});
		});
	},
	"columns": [
		{
			data: "name",
			render: function(data, type, row)
			{
				if (type !== "display")
				{
					return data;
				}

				return '<a href="' + U("/product/" + encodeURIComponent(row.id.toString())) + '">' + FoodLibraryEscape(data) + '</a>';
			}
		},
		{
			data: null,
			orderable: false,
			render: function(data, type, row)
			{
				if (type !== "display")
				{
					return Array.isArray(row.aliases) ? row.aliases.join(", ") : "";
				}

				return FoodLibraryAliases(row);
			}
		},
		{
			data: null,
			render: function(data, type, row)
			{
				return FoodLibraryNutritionValue(row, "calories");
			}
		},
		{
			data: null,
			render: function(data, type, row)
			{
				return FoodLibraryNutritionValue(row, "protein");
			}
		},
		{
			data: null,
			render: function(data, type, row)
			{
				return FoodLibraryNutritionValue(row, "fat");
			}
		},
		{
			data: null,
			render: function(data, type, row)
			{
				return FoodLibraryNutritionValue(row, "carbohydrates");
			}
		},
		{
			data: null,
			render: function(data, type, row)
			{
				return FoodLibraryNutritionBasis(row);
			}
		},
		{
			data: null,
			render: function(data, type, row)
			{
				return FoodLibrarySource(row);
			}
		}
	],
	"columnDefs": [
		{ "type": "html", "targets": 0 },
		{ "type": "html-num-fmt", "targets": [2, 3, 4, 5] }
	].concat($.fn.dataTable.defaults.columnDefs)
});

$("#food-library-table tbody").removeClass("d-none");
foodLibraryTable.columns.adjust().draw();

$("#food-library-search").on("keyup change", Delay(function()
{
	foodLibraryTable.ajax.reload();
}, Grocy.FormFocusDelay));
