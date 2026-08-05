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

function FoodLibraryIsExternalCandidate(food)
{
	return food && food.imported === false && food.source && food.source.provider && food.source.external_id;
}

function FoodLibraryImportExternalCandidate(provider, externalId)
{
	Grocy.Api.Post("eric/foods/import-from-source", { provider: provider, external_id: externalId }, function()
	{
		foodLibraryTable.ajax.reload(null, false);
	}, function(xhr)
	{
		console.error(xhr);
		window.alert(__t("Error while importing the selected food"));
	});
}

function FoodLibraryParseAliases(value)
{
	return value.split(/[\r\n,]+/).map(function(alias)
	{
		return alias.trim();
	}).filter(function(alias, index, aliases)
	{
		return alias !== "" && aliases.indexOf(alias) === index;
	});
}

function FoodLibraryEditAliases(food)
{
	$("#food-library-aliases-product-id").val(food.id);
	$("#food-library-aliases-input").val(Array.isArray(food.aliases) ? food.aliases.join("\n") : "");
	$("#food-library-aliases-modal").modal("show");
}

function FoodLibrarySaveAliases()
{
	var productId = $("#food-library-aliases-product-id").val();
	var aliases = FoodLibraryParseAliases($("#food-library-aliases-input").val());
	Grocy.Api.Put("eric/foods/" + encodeURIComponent(productId) + "/aliases", { aliases: aliases }, function()
	{
		$("#food-library-aliases-modal").modal("hide");
		foodLibraryTable.ajax.reload(null, false);
	}, function(xhr)
	{
		console.error(xhr);
		window.alert(__t("Error while saving aliases"));
	});
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

function FoodLibraryRenderName(data, type, row)
{
	if (type !== "display")
	{
		return data;
	}

	if (FoodLibraryIsExternalCandidate(row))
	{
		return FoodLibraryEscape(data) + ' <span class="badge badge-info">Boohee</span>';
	}

	return '<a href="' + U("/product/" + encodeURIComponent(row.id.toString())) + '">' + FoodLibraryEscape(data) + '</a>';
}

function FoodLibraryRenderExternalAction(row, type)
{
	if (type !== "display" || !FoodLibraryIsExternalCandidate(row))
	{
		return "";
	}

	return '<button type="button" class="btn btn-sm btn-success food-library-import-external" data-provider="' + FoodLibraryEscape(row.source.provider) + '" data-external-id="' + FoodLibraryEscape(row.source.external_id) + '">' + __t("Add") + '</button>';
}

function FoodLibraryRenderLocalAction(row, type)
{
	if (type !== "display" || FoodLibraryIsExternalCandidate(row))
	{
		return "";
	}

	return '<button type="button" class="btn btn-sm btn-outline-secondary food-library-edit-aliases" data-product-id="' + FoodLibraryEscape(row.id) + '">' + __t("Aliases") + '</button>';
}

function FoodLibraryRenderAction(row, type)
{
	if (FoodLibraryIsExternalCandidate(row))
	{
		return FoodLibraryRenderExternalAction(row, type);
	}

	return FoodLibraryRenderLocalAction(row, type);
}

var FoodLibraryColumns = [
	{
		data: "name",
		render: function(data, type, row)
		{
			return FoodLibraryRenderName(data, type, row);
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
	},
	{
		data: null,
		orderable: false,
		render: function(data, type, row)
		{
			return FoodLibraryRenderAction(row, type);
		}
	}
];

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
	"columns": FoodLibraryColumns,
	"columnDefs": [
		{ "type": "html", "targets": 0 },
		{ "type": "html-num-fmt", "targets": [2, 3, 4, 5] }
	].concat($.fn.dataTable.defaults.columnDefs)
});

$("#food-library-table tbody").removeClass("d-none");
foodLibraryTable.columns.adjust().draw();

function FoodLibraryRunSearch()
{
	foodLibraryTable.ajax.reload();
}

$("#food-library-search").on("keydown", function(event)
{
	if (event.key === "Enter")
	{
		event.preventDefault();
		FoodLibraryRunSearch();
	}
});

$("#food-library-search-button").on("click", function()
{
	FoodLibraryRunSearch();
});

$("#food-library-table").on("click", ".food-library-import-external", function()
{
	FoodLibraryImportExternalCandidate($(this).attr("data-provider"), $(this).attr("data-external-id"));
});

$("#food-library-table").on("click", ".food-library-edit-aliases", function()
{
	var productId = Number($(this).attr("data-product-id"));
	var food = foodLibraryTable.rows().data().toArray().find(function(row)
	{
		return Number(row.id) === productId;
	});
	if (food)
	{
		FoodLibraryEditAliases(food);
	}
});

$("#food-library-aliases-save").on("click", function()
{
	FoodLibrarySaveAliases();
});
