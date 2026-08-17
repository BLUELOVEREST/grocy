function EricDashboardCreateElement(tagName, className, text)
{
	var element = document.createElement(tagName);
	if (className)
	{
		element.className = className;
	}
	if (text !== undefined && text !== null)
	{
		element.textContent = text;
	}

	return element;
}

function EricDashboardRenderProducts(products, query)
{
	var resultsContainer = document.getElementById("eric-dashboard-search-results");
	var label = document.getElementById("eric-dashboard-search-label");
	resultsContainer.innerHTML = "";

	if (query)
	{
		label.textContent = products.length + " results for \"" + query + "\"";
	}
	else
	{
		label.textContent = "Quick Results";
	}

	if (products.length === 0)
	{
		resultsContainer.appendChild(EricDashboardCreateElement("p", "eric-empty", "No matching products."));
		return;
	}

	products.slice(0, 8).forEach(function(product)
	{
		var row = EricDashboardCreateElement("a", "eric-result-row");
		row.href = U("/product/" + encodeURIComponent(product.id));

		var left = EricDashboardCreateElement("div", "d-flex align-items-center", null);
		left.style.gap = "16px";

		var icon = EricDashboardCreateElement("span", "eric-result-icon");
		var iconInner = EricDashboardCreateElement("i", product.isFood ? "fa-solid fa-bowl-food" : "fa-solid fa-box");
		icon.appendChild(iconInner);

		var main = EricDashboardCreateElement("div", "eric-result-main");
		main.appendChild(EricDashboardCreateElement("p", "eric-result-title", product.name));
		main.appendChild(EricDashboardCreateElement("p", "eric-result-meta", product.isFood ? "Food product" : "Product"));

		left.appendChild(icon);
		left.appendChild(main);

		var action = EricDashboardCreateElement("span", "eric-btn", "详情");
		row.appendChild(left);
		row.appendChild(action);
		resultsContainer.appendChild(row);
	});
}

function EricDashboardRunProductSearch()
{
	var input = document.getElementById("eric-dashboard-search");
	var query = input.value.trim().toLowerCase();
	var products = Array.isArray(Grocy.EricDashboardProducts) ? Grocy.EricDashboardProducts : [];

	if (query === "")
	{
		EricDashboardRenderProducts(products.slice(0, 4), "");
		return;
	}

	var filteredProducts = products.filter(function(product)
	{
		return product.name && product.name.toLowerCase().indexOf(query) !== -1;
	});

	EricDashboardRenderProducts(filteredProducts, input.value.trim());
}

document.getElementById("eric-dashboard-search-button").addEventListener("click", function()
{
	EricDashboardRunProductSearch();
});

document.getElementById("eric-dashboard-search").addEventListener("keydown", function(event)
{
	if (event.key === "Enter")
	{
		event.preventDefault();
		EricDashboardRunProductSearch();
	}
});

EricDashboardRunProductSearch();
