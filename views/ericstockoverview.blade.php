@php require_frontend_packages(['datatables', 'animatecss']); @endphp

@extends('layout.default')

@section('title', $__t('Stock overview'))

@push('pageScripts')
<script src="{{ $U('/viewjs/purchase.js?v=', true) }}{{ $version }}"></script>
@endpush

@push('pageStyles')
<style>
	body.fixed-nav {
		padding-top: 0;
	}

	body.night-mode,
	body.night-mode .content-wrapper,
	html {
		background: #f5f7fa;
	}

	#mainNav {
		display: none;
	}

	.content-wrapper {
		background: #f5f7fa;
		margin-left: 0 !important;
		min-height: 100vh;
		padding-top: 0;
	}

	.content-wrapper > .container-fluid {
		padding-left: 0 !important;
		padding-right: 0 !important;
	}

	.content-wrapper .row.mb-3 {
		margin: 0 !important;
	}

	#page-content {
		padding: 0;
	}

	.eric-stock {
		--eric-bg: #f5f7fa;
		--eric-panel: #ffffff;
		--eric-subtle: #eef2f6;
		--eric-border: #e5e7eb;
		--eric-text: #1d1d1f;
		--eric-muted: #6b7280;
		--eric-primary: #2f80ed;
		--eric-green: #34c759;
		--eric-amber: #ff9f0a;
		--eric-danger: #ba1a1a;
		background: var(--eric-bg);
		color: var(--eric-text);
		display: flex;
		font-family: Roboto, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
		min-height: 100vh;
	}

	.eric-stock a {
		color: inherit;
	}

	.eric-stock-sidebar {
		background: #f2f4f7;
		border-right: 1px solid var(--eric-border);
		display: flex;
		flex: 0 0 240px;
		flex-direction: column;
		min-height: 100vh;
		width: 240px;
	}

	.eric-stock-brand {
		align-items: center;
		border-bottom: 1px solid var(--eric-border);
		color: var(--eric-primary);
		display: flex;
		font-size: 20px;
		font-weight: 600;
		gap: 8px;
		line-height: 28px;
		padding: 16px;
	}

	.eric-stock-nav {
		flex: 1;
		overflow-y: auto;
		padding: 16px 8px;
	}

	.eric-stock-nav-section {
		border-top: 1px solid var(--eric-border);
		margin-top: 16px;
		padding-top: 16px;
	}

	.eric-stock-nav-label {
		color: var(--eric-muted);
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.05em;
		line-height: 16px;
		margin: 0 0 8px;
		padding: 0 12px;
		text-transform: uppercase;
	}

	.eric-stock-nav-link {
		align-items: center;
		border-radius: 8px;
		color: #414753;
		display: flex;
		font-size: 14px;
		gap: 12px;
		line-height: 20px;
		margin-bottom: 4px;
		padding: 10px 12px;
		text-decoration: none;
		transition: background 120ms ease, color 120ms ease;
	}

	.eric-stock-nav-link:hover {
		background: #e0e3e6;
		color: var(--eric-text);
		text-decoration: none;
	}

	.eric-stock-nav-link.active {
		background: var(--eric-primary);
		color: #ffffff;
		font-weight: 500;
	}

	.eric-stock-main {
		flex: 1;
		min-width: 0;
		padding: 32px;
	}

	.eric-stock-header,
	.eric-stock-status-card,
	.eric-stock-filter-panel,
	.eric-stock-table-panel {
		background: var(--eric-panel);
		border: 1px solid var(--eric-border);
		border-radius: 12px;
		box-shadow: 0 1px 2px rgba(29, 29, 31, 0.04);
	}

	.eric-stock-header {
		align-items: flex-start;
		display: flex;
		justify-content: space-between;
		margin-bottom: 16px;
		padding: 20px 24px;
	}

	.eric-stock-title {
		font-size: 24px;
		font-weight: 600;
		line-height: 32px;
		margin: 0;
	}

	.eric-stock-subtitle {
		align-items: center;
		color: var(--eric-muted);
		display: flex;
		flex-wrap: wrap;
		font-size: 13px;
		gap: 8px;
		line-height: 18px;
		margin: 6px 0 0;
	}

	.eric-status-dot {
		background: var(--eric-green);
		border-radius: 999px;
		display: inline-block;
		height: 8px;
		width: 8px;
	}

	.eric-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
		justify-content: flex-end;
	}

	.eric-btn {
		align-items: center;
		background: var(--eric-panel);
		border: 1px solid var(--eric-border);
		border-radius: 8px;
		color: var(--eric-text);
		display: inline-flex;
		font-size: 13px;
		font-weight: 500;
		gap: 8px;
		min-height: 40px;
		padding: 9px 14px;
		text-decoration: none;
		transition: background 120ms ease, border-color 120ms ease, color 120ms ease;
	}

	.eric-btn:hover {
		background: var(--eric-subtle);
		text-decoration: none;
	}

	.eric-btn-primary {
		background: var(--eric-primary);
		border-color: var(--eric-primary);
		color: #ffffff;
	}

	.eric-btn-primary:hover {
		background: #176bd8;
		color: #ffffff;
	}

	.eric-stock-status-grid {
		display: grid;
		gap: 12px;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		margin-bottom: 16px;
	}

	.eric-stock-status-card {
		cursor: pointer;
		display: block;
		line-height: normal;
		min-height: 92px;
		padding: 16px;
		transition: border-color 120ms ease, transform 120ms ease;
	}

	.eric-stock-status-card:hover {
		border-color: #c1c6d5;
		transform: translateY(-1px);
	}

	.eric-stock-status-card strong {
		display: block;
		font-size: 24px;
		font-weight: 600;
		line-height: 32px;
	}

	.eric-stock-status-label {
		color: var(--eric-muted);
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.05em;
		line-height: 16px;
		text-transform: uppercase;
	}

	.eric-stock-status-note {
		color: var(--eric-muted);
		font-size: 13px;
		line-height: 18px;
		margin: 4px 0 0;
		min-height: 18px;
	}

	.eric-stock-filter-panel {
		align-items: center;
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
		margin-bottom: 16px;
		padding: 12px;
	}

	.eric-stock-filter-panel .input-group {
		margin: 0;
	}

	.eric-stock-filter-panel .input-group-text,
	.eric-stock-filter-panel .form-control,
	.eric-stock-filter-panel .custom-select {
		background-color: var(--eric-subtle);
		border: 0;
		border-radius: 8px;
		color: var(--eric-text);
		font-size: 13px;
		min-height: 40px;
	}

	.night-mode .eric-stock .eric-stock-filter-panel .input-group-text,
	.night-mode .eric-stock .eric-stock-filter-panel .form-control,
	.night-mode .eric-stock .eric-stock-filter-panel .custom-select,
	.night-mode .eric-stock .eric-stock-filter-panel select {
		background-color: var(--eric-subtle) !important;
		border-color: transparent !important;
		color: var(--eric-text) !important;
	}

	.eric-stock-filter-panel .input-group-text {
		color: var(--eric-muted);
	}

	.eric-stock-filter {
		flex: 1 1 220px;
		min-width: 180px;
	}

	.eric-stock-filter-actions {
		margin-left: auto;
	}

	.eric-stock-table-panel {
		border-radius: 12px;
		overflow: hidden;
		padding: 0;
	}

	.eric-stock-table-panel .dataTables_wrapper {
		padding: 16px;
	}

	.eric-stock-table-panel .dataTables_length,
	.eric-stock-table-panel .dataTables_filter {
		margin-bottom: 12px;
	}

	#stock-overview-table {
		border-collapse: separate !important;
		border-spacing: 0;
		margin: 0 !important;
		width: 100% !important;
	}

	#stock-overview-table thead th {
		background: #f7f9fc;
		border-bottom: 1px solid var(--eric-border);
		color: var(--eric-muted);
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.05em;
		line-height: 16px;
		padding: 12px 14px;
		text-transform: uppercase;
		vertical-align: middle;
	}

	#stock-overview-table tbody td {
		border-top: 1px solid var(--eric-border);
		color: var(--eric-text);
		font-size: 13px;
		line-height: 18px;
		padding: 12px 14px;
		vertical-align: middle;
	}

	#stock-overview-table.table-striped tbody tr:nth-of-type(odd) {
		background: #ffffff;
	}

	#stock-overview-table tbody tr:hover {
		background: var(--eric-subtle);
	}

	#stock-overview-table .btn {
		border-radius: 8px;
	}

	@media (max-width: 991.98px) {
		.eric-stock {
			display: block;
		}

		.eric-stock-sidebar {
			border-bottom: 1px solid var(--eric-border);
			border-right: 0;
			min-height: auto;
			width: 100%;
		}

		.eric-stock-nav {
			display: flex;
			gap: 8px;
			overflow-x: auto;
			padding: 8px;
		}

		.eric-stock-nav-section {
			border-top: 0;
			display: contents;
		}

		.eric-stock-nav-label {
			display: none;
		}

		.eric-stock-nav-link {
			flex: 0 0 auto;
			margin-bottom: 0;
			white-space: nowrap;
		}

		.eric-stock-main {
			padding: 16px;
		}

		.eric-stock-header {
			flex-direction: column;
			gap: 16px;
		}

		.eric-actions {
			justify-content: flex-start;
			width: 100%;
		}

		.eric-stock-status-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 575.98px) {
		.eric-stock-status-grid {
			grid-template-columns: 1fr;
		}
	}
</style>
@endpush

@section('content')
<div class="eric-stock">
	<aside class="eric-stock-sidebar">
		<div class="eric-stock-brand">
			<i class="fa-solid fa-gauge-high"></i>
			<span>Grocy</span>
		</div>
		<nav class="eric-stock-nav">
			<a class="eric-stock-nav-link"
				href="{{ $U('/eric-dashboard') }}">
				<i class="fa-solid fa-fw fa-gauge-high"></i>
				<span>Dashboard</span>
			</a>
			<a class="eric-stock-nav-link active"
				href="{{ $U('/eric-stockoverview') }}">
				<i class="fa-solid fa-fw fa-box"></i>
				<span>Stock</span>
			</a>
			<a class="eric-stock-nav-link"
				href="{{ $U('/eric-shoppinglist') }}">
				<i class="fa-solid fa-fw fa-shopping-cart"></i>
				<span>Shopping List</span>
			</a>
			<a class="eric-stock-nav-link"
				href="{{ $U('/eric-foodlibrary') }}">
				<i class="fa-solid fa-fw fa-bowl-food"></i>
				<span>Food Library</span>
			</a>
			<a class="eric-stock-nav-link"
				href="{{ $U('/eric-recipes') }}">
				<i class="fa-solid fa-fw fa-pizza-slice"></i>
				<span>Recipes</span>
			</a>

			<div class="eric-stock-nav-section">
				<p class="eric-stock-nav-label">Quick Actions</p>
				<a class="eric-stock-nav-link"
					href="{{ $U('/purchase') }}">
					<i class="fa-solid fa-fw fa-cart-plus"></i>
					<span>入库</span>
				</a>
				<a class="eric-stock-nav-link"
					href="{{ $U('/consume') }}">
					<i class="fa-solid fa-fw fa-utensils"></i>
					<span>出库</span>
				</a>
				<a class="eric-stock-nav-link"
					href="{{ $U('/inventory') }}">
					<i class="fa-solid fa-fw fa-clipboard-check"></i>
					<span>盘点</span>
				</a>
			</div>

			<div class="eric-stock-nav-section">
				<a class="eric-stock-nav-link"
					href="{{ $U('/products') }}">
					<i class="fa-solid fa-fw fa-table"></i>
					<span>Products</span>
				</a>
				<a class="eric-stock-nav-link"
					href="{{ $U('/stocksettings') }}">
					<i class="fa-solid fa-fw fa-gear"></i>
					<span>Settings</span>
				</a>
			</div>
		</nav>
	</aside>

	<main class="eric-stock-main">
		<header class="eric-stock-header">
			<div>
				<h1 class="eric-stock-title">{{ $__t('Stock overview') }}</h1>
				<p class="eric-stock-subtitle">
					<span class="eric-status-dot"></span>
					<span id="info-current-stock"></span>
					<span>/</span>
					<span>{{ $__t('Current stock, due dates and low stock alerts') }}</span>
				</p>
			</div>
			<div class="eric-actions">
				<a class="eric-btn"
					href="{{ $U('/stockjournal') }}">
					<i class="fa-solid fa-book"></i>
					{{ $__t('Journal') }}
				</a>
				<a class="eric-btn"
					href="{{ $U('/stockentries') }}">
					<i class="fa-solid fa-list"></i>
					{{ $__t('Stock entries') }}
				</a>
				@if(GROCY_FEATURE_FLAG_STOCK_LOCATION_TRACKING || GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING)
				<div class="dropdown">
					<a class="eric-btn dropdown-toggle"
						href="#"
						data-toggle="dropdown">
						<i class="fa-solid fa-chart-line"></i>
						{{ $__t('Reports') }}
					</a>
					<div class="dropdown-menu dropdown-menu-right">
						@if(GROCY_FEATURE_FLAG_STOCK_LOCATION_TRACKING)
						<a class="dropdown-item"
							href="{{ $U('/locationcontentsheet') }}">{{ $__t('Location Content Sheet') }}</a>
						@endif
						@if(GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING)
						<a class="dropdown-item"
							href="{{ $U('/stockreports/spendings') }}">{{ $__t('Spendings') }}</a>
						@endif
					</div>
				</div>
				@endif
				<a class="eric-btn"
					href="{{ $U('/inventory') }}">
					<i class="fa-solid fa-clipboard-check"></i>
					{{ $__t('Inventory') }}
				</a>
				<a class="eric-btn eric-btn-primary"
					href="{{ $U('/purchase') }}">
					<i class="fa-solid fa-cart-plus"></i>
					{{ $__t('Purchase') }}
				</a>
			</div>
		</header>

		<div class="eric-stock-status-grid">
			@if (GROCY_FEATURE_FLAG_STOCK_BEST_BEFORE_DATE_TRACKING)
			<div id="info-expired-products"
				data-status-filter="expired"
				class="eric-stock-status-card status-filter-message">
				<span class="eric-stock-status-label">{{ $__t('Expired') }}</span>
				<strong>0</strong>
				<p class="eric-stock-status-note">{{ $__t('Click to filter') }}</p>
			</div>
			<div id="info-overdue-products"
				data-status-filter="overdue"
				class="eric-stock-status-card status-filter-message">
				<span class="eric-stock-status-label">{{ $__t('Overdue') }}</span>
				<strong>0</strong>
				<p class="eric-stock-status-note">{{ $__t('Click to filter') }}</p>
			</div>
			<div id="info-duesoon-products"
				data-next-x-days="{{ $nextXDays }}"
				data-status-filter="duesoon"
				class="eric-stock-status-card status-filter-message">
				<span class="eric-stock-status-label">{{ $__t('Due soon') }}</span>
				<strong>0</strong>
				<p class="eric-stock-status-note">{{ $__t('within the next %s days', $nextXDays) }}</p>
			</div>
			@endif
			<div id="info-missing-products"
				data-status-filter="belowminstockamount"
				class="eric-stock-status-card status-filter-message">
				<span class="eric-stock-status-label">{{ $__t('Below min. stock amount') }}</span>
				<strong>0</strong>
				<p class="eric-stock-status-note">{{ $__t('Click to filter') }}</p>
			</div>
		</div>

		<div class="eric-stock-filter-panel"
			id="table-filter-row">
			<div class="eric-stock-filter">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text"><i class="fa-solid fa-search"></i></span>
			</div>
			<input type="text"
				id="search"
				class="form-control"
				placeholder="{{ $__t('Search') }}">
		</div>
	</div>
	@if(GROCY_FEATURE_FLAG_STOCK_LOCATION_TRACKING)
	<div class="eric-stock-filter">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text"><i class="fa-solid fa-filter"></i>&nbsp;{{ $__t('Location') }}</span>
			</div>
			<select class="custom-control custom-select"
				id="location-filter">
				<option value="all">{{ $__t('All') }}</option>
				@foreach($locations as $location)
				<option value="{{ $location->name }}">{{ $location->name }}</option>
				@endforeach
			</select>
		</div>
	</div>
	@endif
	<div class="eric-stock-filter">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text"><i class="fa-solid fa-filter"></i>&nbsp;{{ $__t('Product group') }}</span>
			</div>
			<select class="custom-control custom-select"
				id="product-group-filter">
				<option value="all">{{ $__t('All') }}</option>
				@foreach($productGroups as $productGroup)
				<option value="{{ $productGroup->name }}">{{ $productGroup->name }}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="eric-stock-filter">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text"><i class="fa-solid fa-filter"></i>&nbsp;{{ $__t('Status') }}</span>
			</div>
			<select class="custom-control custom-select"
				id="status-filter">
				<option class="bg-white"
					value="all">{{ $__t('All') }}</option>
				@if (GROCY_FEATURE_FLAG_STOCK_BEST_BEFORE_DATE_TRACKING)
				<option value="duesoon">{{ $__t('Due soon') }}</option>
				<option value="overdue">{{ $__t('Overdue') }}</option>
				<option value="expired">{{ $__t('Expired') }}</option>
				@endif
				<option value="belowminstockamount">{{ $__t('Below min. stock amount') }}</option>
				<option value="instockX">{{ $__t('In stock products') }}</option>
			</select>
		</div>
	</div>
	<div class="eric-stock-filter-actions">
		<button id="clear-filter-button"
			class="eric-btn"
			data-toggle="tooltip"
			title="{{ $__t('Clear filter') }}">
			<i class="fa-solid fa-filter-circle-xmark"></i>
			{{ $__t('Clear filter') }}
		</button>
	</div>
</div>

<div class="eric-stock-table-panel">
		<table id="stock-overview-table"
			class="table table-sm table-striped nowrap w-100">
			<thead>
				<tr>
					<th class="border-right"><a class="text-muted change-table-columns-visibility-button"
							data-toggle="tooltip"
							title="{{ $__t('Table options') }}"
							data-table-selector="#stock-overview-table"
							href="#"><i class="fa-solid fa-eye"></i></a>
					</th>
					<th>{{ $__t('Product') }}</th>
					<th class="allow-grouping">{{ $__t('Product group') }}</th>
					<th>{{ $__t('Amount') }}</th>
					<th class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif">{{ $__t('Value') }}</th>
					<th class="@if(!GROCY_FEATURE_FLAG_STOCK_BEST_BEFORE_DATE_TRACKING) d-none @endif allow-grouping">{{ $__t('Next due date') }}</th>
					<th class="d-none">Hidden location</th>
					<th class="d-none">Hidden status</th>
					<th class="d-none">Hidden product group</th>
					<th>{{ $__t('Calories') }} ({{ $__t('Per stock quantity unit') }})</th>
					<th>{{ $__t('Calories') }}</th>
					<th class="allow-grouping">{{ $__t('Last purchased') }}</th>
					<th class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif">{{ $__t('Last price') }}</th>
					<th class="allow-grouping">{{ $__t('Min. stock amount') }}</th>
					<th>{{ $__t('Product description') }}</th>
					<th class="allow-grouping">{{ $__t('Parent product') }}</th>
					<th class="allow-grouping">{{ $__t('Default location') }}</th>
					<th>{{ $__t('Product picture') }}</th>
					<th class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif">{{ $__t('Average price') }}</th>
					<th class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif allow-grouping">{{ $__t('Default store') }}</th>

					@include('components.userfields_thead', array(
					'userfields' => $userfields
					))

				</tr>
			</thead>
			<tbody class="d-none">
				@foreach($currentStock as $currentStockEntry)
				<tr id="product-{{ $currentStockEntry->product_id }}-row"
					class="@if(GROCY_FEATURE_FLAG_STOCK_BEST_BEFORE_DATE_TRACKING && $currentStockEntry->best_before_date < date('Y-m-d 23:59:59', strtotime('-1 days')) && $currentStockEntry->amount > 0) @if($currentStockEntry->due_type == 1) table-secondary @else table-danger @endif @elseif(GROCY_FEATURE_FLAG_STOCK_BEST_BEFORE_DATE_TRACKING && $currentStockEntry->best_before_date < date('Y-m-d 23:59:59', strtotime('+' . $nextXDays . ' days')) && $currentStockEntry->amount > 0) table-warning @elseif ($currentStockEntry->product_missing) table-info @endif">
					<td class="fit-content border-right">
						<a class="permission-STOCK_CONSUME btn btn-success btn-sm product-consume-button @if($currentStockEntry->amount_aggregated < $currentStockEntry->quick_consume_amount || $currentStockEntry->enable_tare_weight_handling == 1) disabled @endif"
							href="#"
							data-toggle="tooltip"
							data-placement="left"
							title="{{ $__t('Consume %1$s of %2$s', $currentStockEntry->quick_consume_amount_qu_consume . ' ' . $currentStockEntry->qu_consume_name, $currentStockEntry->product_name) }}"
							data-product-id="{{ $currentStockEntry->product_id }}"
							data-product-name="{{ $currentStockEntry->product_name }}"
							data-product-qu-name="{{ $currentStockEntry->qu_stock_name }}"
							data-consume-amount="{{ $currentStockEntry->quick_consume_amount }}">
							<i class="fa-solid fa-utensils"></i> <span class="locale-number locale-number-quantity-amount">{{ $currentStockEntry->quick_consume_amount_qu_consume }}</span>
						</a>
						<a id="product-{{ $currentStockEntry->product_id }}-consume-all-button"
							class="permission-STOCK_CONSUME btn btn-danger btn-sm product-consume-button @if($currentStockEntry->amount_aggregated == 0) disabled @endif"
							href="#"
							data-toggle="tooltip"
							data-placement="right"
							title="{{ $__t('Consume all %s which are currently in stock', $currentStockEntry->product_name) }}"
							data-product-id="{{ $currentStockEntry->product_id }}"
							data-product-name="{{ $currentStockEntry->product_name }}"
							data-product-qu-name="{{ $currentStockEntry->qu_stock_name }}"
							data-consume-amount="@if($currentStockEntry->enable_tare_weight_handling == 1){{$currentStockEntry->tare_weight}}@else{{$currentStockEntry->amount}}@endif"
							data-original-total-stock-amount="{{$currentStockEntry->amount}}">
							<i class="fa-solid fa-utensils"></i> {{ $__t('All') }}
						</a>
						@if(GROCY_FEATURE_FLAG_STOCK_PRODUCT_OPENED_TRACKING)
						<a class="btn btn-success btn-sm product-open-button @if($currentStockEntry->amount_aggregated < $currentStockEntry->quick_open_amount || $currentStockEntry->amount_aggregated == $currentStockEntry->amount_opened_aggregated || $currentStockEntry->enable_tare_weight_handling == 1 || $currentStockEntry->disable_open == 1) disabled @endif"
							href="#"
							data-toggle="tooltip"
							data-placement="left"
							title="{{ $__t('Mark %1$s of %2$s as open', $currentStockEntry->quick_open_amount_qu_consume . ' ' . $currentStockEntry->qu_consume_name, $currentStockEntry->product_name) }}"
							data-product-id="{{ $currentStockEntry->product_id }}"
							data-product-name="{{ $currentStockEntry->product_name }}"
							data-product-qu-name="{{ $currentStockEntry->qu_stock_name }}"
							data-open-amount="{{ $currentStockEntry->quick_open_amount }}">
							<i class="fa-solid fa-box-open"></i> <span class="locale-number locale-number-quantity-amount">{{ $currentStockEntry->quick_open_amount_qu_consume }}</span>
						</a>
						@endif
						<div class="dropdown d-inline-block">
							<button class="btn btn-sm btn-light text-secondary"
								type="button"
								data-toggle="dropdown">
								<i class="fa-solid fa-ellipsis-v"></i>
							</button>
							<div class="table-inline-menu dropdown-menu dropdown-menu-right">
								@if(GROCY_FEATURE_FLAG_SHOPPINGLIST)
								<a class="dropdown-item show-as-dialog-link permission-SHOPPINGLIST_ITEMS_ADD"
									type="button"
									href="{{ $U('/shoppinglistitem/new?embedded&updateexistingproduct&product=' . $currentStockEntry->product_id ) }}">
									<span class="dropdown-item-icon"><i class="fa-solid fa-shopping-cart"></i></span> <span class="dropdown-item-text">{{ $__t('Add to shopping list') }}</span>
								</a>
								<div class="dropdown-divider"></div>
								@endif
								<a class="dropdown-item show-as-dialog-link permission-STOCK_PURCHASE"
									type="button"
									href="{{ $U('/purchase?embedded&product=' . $currentStockEntry->product_id ) }}">
									<span class="dropdown-item-icon"><i class="fa-solid fa-cart-plus"></i></span> <span class="dropdown-item-text">{{ $__t('Purchase') }}</span>
								</a>
								<a class="dropdown-item show-as-dialog-link permission-STOCK_CONSUME @if($currentStockEntry->amount_aggregated <= 0) disabled @endif"
									type="button"
									href="{{ $U('/consume?embedded&product=' . $currentStockEntry->product_id ) }}">
									<span class="dropdown-item-icon"><i class="fa-solid fa-utensils"></i></span> <span class="dropdown-item-text">{{ $__t('Consume') }}</span>
								</a>
								@if(GROCY_FEATURE_FLAG_STOCK_LOCATION_TRACKING)
								<a class="dropdown-item show-as-dialog-link permission-STOCK_TRANSFER @if($currentStockEntry->amount <= 0) disabled @endif"
									type="button"
									href="{{ $U('/transfer?embedded&product=' . $currentStockEntry->product_id) }}">
									<span class="dropdown-item-icon"><i class="fa-solid fa-exchange-alt"></i></span> <span class="dropdown-item-text">{{ $__t('Transfer') }}</span>
								</a>
								@endif
								<a class="dropdown-item show-as-dialog-link permission-STOCK_INVENTORY"
									type="button"
									href="{{ $U('/inventory?embedded&product=' . $currentStockEntry->product_id ) }}">
									<span class="dropdown-item-icon"><i class="fa-solid fa-list"></i></span> <span class="dropdown-item-text">{{ $__t('Inventory') }}</span>
								</a>
								@if(GROCY_FEATURE_FLAG_RECIPES)
								<div class="dropdown-divider"></div>
								<a class="dropdown-item"
									type="button"
									href="{{ $U('/eric-recipes?search=') }}{{ $currentStockEntry->product_name }}">
									<span class="dropdown-item-text">{{ $__t('Search for recipes containing this product') }}</span>
								</a>
								@endif
								<div class="dropdown-divider"></div>
								<a class="dropdown-item productcard-trigger"
									data-product-id="{{ $currentStockEntry->product_id }}"
									type="button"
									href="#">
									<span class="dropdown-item-text">{{ $__t('Product overview') }}</span>
								</a>
								<a class="dropdown-item show-as-dialog-link"
									type="button"
									href="{{ $U('/stockentries?embedded&product=') }}{{ $currentStockEntry->product_id }}"
									data-dialog-type="table"
									data-product-id="{{ $currentStockEntry->product_id }}">
									<span class="dropdown-item-text">{{ $__t('Stock entries') }}</span>
								</a>
								<a class="dropdown-item show-as-dialog-link"
									type="button"
									href="{{ $U('/stockjournal?embedded&product=') }}{{ $currentStockEntry->product_id }}"
									data-dialog-type="table">
									<span class="dropdown-item-text">{{ $__t('Stock journal') }}</span>
								</a>
								<a class="dropdown-item show-as-dialog-link"
									type="button"
									href="{{ $U('/stockjournal/summary?embedded&product_id=') }}{{ $currentStockEntry->product_id }}"
									data-dialog-type="table">
									<span class="dropdown-item-text">{{ $__t('Stock journal summary') }}</span>
								</a>
								<a class="dropdown-item permission-MASTER_DATA_EDIT link-return"
									type="button"
									data-href="{{ $U('/product/') }}{{ $currentStockEntry->product_id }}">
									<span class="dropdown-item-text">{{ $__t('Edit product') }}</span>
								</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item"
									type="button"
									href="{{ $U('/product/' . $currentStockEntry->product_id . '/grocycode?download=true') }}">
									{!! str_replace('Grocycode', '<span class="ls-n1">Grocycode</span>', $__t('Download %s Grocycode', $__t('Product'))) !!}
								</a>
								@if(GROCY_FEATURE_FLAG_LABEL_PRINTER)
								<a class="dropdown-item product-grocycode-label-print"
									data-product-id="{{ $currentStockEntry->product_id }}"
									type="button"
									href="#">
									{!! str_replace('Grocycode', '<span class="ls-n1">Grocycode</span>', $__t('Print %s Grocycode on label printer', $__t('Product'))) !!}
								</a>
								@endif
							</div>
						</div>
					</td>
					<td class="productcard-trigger cursor-link"
						data-product-id="{{ $currentStockEntry->product_id }}">
						{{ $currentStockEntry->product_name }}
						<span class="d-none">{{ $currentStockEntry->product_barcodes }}</span>
					</td>
					<td>
						@if($currentStockEntry->product_group_name !== null){{ $currentStockEntry->product_group_name }}@endif
					</td>
					<td>
						<span class="custom-sort d-none">@if($currentStockEntry->product_no_own_stock == 1){{ $currentStockEntry->amount_aggregated }}@else{{ $currentStockEntry->amount }}@endif</span>
						<span class="@if($currentStockEntry->product_no_own_stock == 1) d-none @endif">
							<span id="product-{{ $currentStockEntry->product_id }}-amount"
								class="locale-number locale-number-quantity-amount">{{ $currentStockEntry->amount }}</span> <span id="product-{{ $currentStockEntry->product_id }}-qu-name">{{ $__n($currentStockEntry->amount, $currentStockEntry->qu_stock_name, $currentStockEntry->qu_stock_name_plural) }}</span>
							<span id="product-{{ $currentStockEntry->product_id }}-opened-amount"
								class="small font-italic">@if($currentStockEntry->amount_opened > 0){{ $__t('%s opened', $currentStockEntry->amount_opened) }}@endif</span>
						</span>
						@if($currentStockEntry->is_aggregated_amount == 1)
						<span class="@if($currentStockEntry->product_no_own_stock == 0) pl-1 @endif text-secondary">
							<i class="fa-solid fa-custom-sigma-sign"></i> <span id="product-{{ $currentStockEntry->product_id }}-amount-aggregated"
								class="locale-number locale-number-quantity-amount">{{ $currentStockEntry->amount_aggregated }}</span> {{ $__n($currentStockEntry->amount_aggregated, $currentStockEntry->qu_stock_name, $currentStockEntry->qu_stock_name_plural, true) }}
							@if($currentStockEntry->amount_opened_aggregated > 0)
							<span id="product-{{ $currentStockEntry->product_id }}-opened-amount-aggregated"
								class="small font-italic">
								{!! $__t('%s opened', '<span class="locale-number locale-number-quantity-amount">' . $currentStockEntry->amount_opened_aggregated . '</span>') !!}
							</span>
							@endif
						</span>
						@endif
						@if(boolval($userSettings['show_icon_on_stock_overview_page_when_product_is_on_shopping_list']))
						@if($currentStockEntry->on_shopping_list)
						<span class="text-muted cursor-normal"
							data-toggle="tooltip"
							title="{{ $__t('This product is currently on a shopping list') }}">
							<i class="fa-solid fa-shopping-cart"></i>
						</span>
						@endif
						@endif
					</td>
					<td>
						<span class="custom-sort d-none">{{$currentStockEntry->value}}</span>
						<span id="product-{{ $currentStockEntry->product_id }}-value"
							class="locale-number locale-number-currency">{{ $currentStockEntry->value }}</span>
					</td>
					<td class="@if(!GROCY_FEATURE_FLAG_STOCK_BEST_BEFORE_DATE_TRACKING) d-none @endif">
						<span id="product-{{ $currentStockEntry->product_id }}-next-due-date">{{ $currentStockEntry->best_before_date }}</span>
						<time id="product-{{ $currentStockEntry->product_id }}-next-due-date-timeago"
							class="timeago timeago-contextual"
							@if(!empty($currentStockEntry->best_before_date)) datetime="{{ $currentStockEntry->best_before_date }} 23:59:59" @endif></time>
					</td>
					<td class="d-none">
						@foreach(FindAllObjectsInArrayByPropertyValue($currentStockLocations, 'product_id', $currentStockEntry->product_id) as $locationsForProduct)
						xx{{ FindObjectInArrayByPropertyValue($locations, 'id', $locationsForProduct->location_id)->name }}xx
						@endforeach
					</td>
					<td class="d-none">
						@if($currentStockEntry->best_before_date < date('Y-m-d
							23:59:59',
							strtotime('-'
							. '1'
							. ' days'
							))
							&&
							$currentStockEntry->amount > 0) @if($currentStockEntry->due_type == 1) overdue @else expired @endif @elseif($currentStockEntry->best_before_date < date('Y-m-d
								23:59:59',
								strtotime('+'
								.
								$nextXDays
								. ' days'
								))
								&&
								$currentStockEntry->amount > 0) duesoon @endif
								@if($currentStockEntry->amount_aggregated > 0) instockX @endif
								@if ($currentStockEntry->product_missing) belowminstockamount @endif
					</td>
					<td class="d-none">
						xx{{ $currentStockEntry->product_group_name }}xx
					</td>
					<td>
						<span class="locale-number locale-number-quantity-amount">{{ $currentStockEntry->product_calories }}</span>
					</td>
					<td>
						<span class="locale-number locale-number-quantity-amount">{{ $currentStockEntry->calories }}</span>
					</td>
					<td>
						{{ $currentStockEntry->last_purchased }}
						<time class="timeago timeago-contextual"
							datetime="{{ $currentStockEntry->last_purchased }}"></time>
					</td>
					<td class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif">
						<span class="custom-sort d-none">{{$currentStockEntry->last_price}}</span>
						@if(!empty($currentStockEntry->last_price))
						<span data-toggle="tooltip"
							data-trigger="hover click"
							data-html="true"
							title="{!! $__t('%1$s per %2$s', '<span class=\'locale-number locale-number-currency\'>' . $currentStockEntry->last_price . '</span>', $currentStockEntry->qu_stock_name) !!}">
							{!! $__t('%1$s per %2$s', '<span class="locale-number locale-number-currency">' . $currentStockEntry->last_price * $currentStockEntry->product_qu_factor_price_to_stock . '</span>', $currentStockEntry->qu_price_name) !!}
						</span>
						@endif
					</td>
					<td>
						<span class="locale-number locale-number-quantity-amount">{{ $currentStockEntry->min_stock_amount }}</span>
					</td>
					<td>
						{!! $currentStockEntry->product_description !!}
					</td>
					<td class="productcard-trigger cursor-link"
						data-product-id="{{ $currentStockEntry->parent_product_id }}">
						{{ $currentStockEntry->parent_product_name }}
					</td>
					<td>
						{{ $currentStockEntry->product_default_location_name }}
					</td>
					<td>
						@if(!empty($currentStockEntry->product_picture_file_name))
						<img src="{{ $U('/api/files/productpictures/' . base64_encode($currentStockEntry->product_picture_file_name) . '?force_serve_as=picture&best_fit_width=64&best_fit_height=64') }}"
							loading="lazy">
						@endif
					</td>
					<td class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif">
						<span class="custom-sort d-none">{{$currentStockEntry->average_price}}</span>
						@if(!empty($currentStockEntry->average_price))
						<span data-toggle="tooltip"
							data-trigger="hover click"
							data-html="true"
							title="{!! $__t('%1$s per %2$s', '<span class=\'locale-number locale-number-currency\'>' . $currentStockEntry->average_price . '</span>', $currentStockEntry->qu_stock_name) !!}">
							{!! $__t('%1$s per %2$s', '<span class="locale-number locale-number-currency">' . $currentStockEntry->average_price * $currentStockEntry->product_qu_factor_price_to_stock . '</span>', $currentStockEntry->qu_price_name) !!}
						</span>
						@endif
					</td>
					<td>
						@if($currentStockEntry->default_store_name !== null){{ $currentStockEntry->default_store_name }}@endif
					</td>

					@include('components.userfields_tbody', array(
					'userfields' => $userfields,
					'userfieldValues' => FindAllObjectsInArrayByPropertyValue($userfieldValues, 'object_id', $currentStockEntry->product_id)
					))

				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	</main>
</div>

@include('components.productcard', [
'asModal' => true
])
@stop
