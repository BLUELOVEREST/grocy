@php require_frontend_packages(['datatables', 'summernote', 'animatecss', 'bwipjs']); @endphp

@extends('layout.default')

@section('title', $__t('Shopping list'))

@push('pageStyles')
<style>
	#shopping-list-print-shadow-table_wrapper .dataTable>thead>tr>th[class*="sort"]:before,
	#shopping-list-print-shadow-table_wrapper .dataTable>thead>tr>th[class*="sort"]:after {
		content: "" !important;
	}

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

	.eric-shopping {
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

	.eric-shopping a {
		color: inherit;
	}

	.eric-shopping-sidebar {
		background: #f2f4f7;
		border-right: 1px solid var(--eric-border);
		display: flex;
		flex: 0 0 240px;
		flex-direction: column;
		min-height: 100vh;
		width: 240px;
	}

	.eric-shopping-brand {
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

	.eric-shopping-nav {
		flex: 1;
		overflow-y: auto;
		padding: 16px 8px;
	}

	.eric-shopping-nav-section {
		border-top: 1px solid var(--eric-border);
		margin-top: 16px;
		padding-top: 16px;
	}

	.eric-shopping-nav-label {
		color: var(--eric-muted);
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.05em;
		line-height: 16px;
		margin: 0 0 8px;
		padding: 0 12px;
		text-transform: uppercase;
	}

	.eric-shopping-nav-link {
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

	.eric-shopping-nav-link:hover {
		background: #e0e3e6;
		color: var(--eric-text);
		text-decoration: none;
	}

	.eric-shopping-nav-link.active {
		background: var(--eric-primary);
		color: #ffffff;
		font-weight: 500;
	}

	.eric-shopping-main {
		flex: 1;
		min-width: 0;
		padding: 32px;
	}

	.eric-shopping-header,
	.eric-shopping-status-card,
	.eric-shopping-filter-panel,
	.eric-shopping-table-panel,
	.eric-shopping-side-panel,
	.eric-shopping-notes-panel {
		background: var(--eric-panel);
		border: 1px solid var(--eric-border);
		border-radius: 12px;
		box-shadow: 0 1px 2px rgba(29, 29, 31, 0.04);
	}

	.eric-shopping-header {
		align-items: flex-start;
		display: flex;
		justify-content: space-between;
		margin-bottom: 16px;
		padding: 20px 24px;
	}

	.eric-shopping-title {
		font-size: 24px;
		font-weight: 600;
		line-height: 32px;
		margin: 0;
	}

	.eric-shopping-subtitle {
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

	.eric-shopping-list-select {
		background-color: var(--eric-subtle) !important;
		border: 0 !important;
		border-radius: 8px;
		color: var(--eric-text) !important;
		font-size: 13px;
		font-weight: 600;
		min-height: 40px;
		min-width: 180px;
	}

	.eric-shopping-status-grid {
		display: grid;
		gap: 12px;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		margin-bottom: 16px;
	}

	.eric-shopping-status-card {
		display: block;
		line-height: normal;
		min-height: 92px;
		padding: 16px;
	}

	.eric-shopping-status-card.status-filter-message {
		cursor: pointer;
	}

	.eric-shopping-status-card strong {
		display: block;
		font-size: 24px;
		font-weight: 600;
		line-height: 32px;
	}

	.eric-shopping-status-label {
		color: var(--eric-muted);
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.05em;
		line-height: 16px;
		text-transform: uppercase;
	}

	.eric-shopping-status-note {
		color: var(--eric-muted);
		font-size: 13px;
		line-height: 18px;
		margin: 4px 0 0;
	}

	.eric-shopping-filter-panel {
		align-items: center;
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
		margin-bottom: 16px;
		padding: 12px;
	}

	.eric-shopping-filter-panel .input-group {
		margin: 0;
	}

	.eric-shopping-filter-panel .input-group-text,
	.eric-shopping-filter-panel .form-control,
	.eric-shopping-filter-panel .custom-select {
		background-color: var(--eric-subtle);
		border: 0;
		border-radius: 8px;
		color: var(--eric-text);
		font-size: 13px;
		min-height: 40px;
	}

	.eric-shopping-filter-panel .input-group-text {
		color: var(--eric-muted);
	}

	.eric-shopping .dropdown-menu,
	body.night-mode .eric-shopping .dropdown-menu {
		background: #ffffff !important;
		border: 1px solid var(--eric-border);
		border-radius: 10px;
		box-shadow: 0 12px 32px rgba(29, 29, 31, 0.12);
		color: var(--eric-text) !important;
		padding: 6px;
	}

	.eric-shopping .dropdown-item,
	body.night-mode .eric-shopping .dropdown-item,
	body.night-mode .eric-shopping .dropdown-item-text {
		background: transparent !important;
		border-radius: 7px;
		color: var(--eric-text) !important;
		font-size: 13px;
		line-height: 20px;
		padding: 8px 10px;
	}

	.eric-shopping .dropdown-item:hover,
	.eric-shopping .dropdown-item:focus,
	body.night-mode .eric-shopping .dropdown-item:hover,
	body.night-mode .eric-shopping .dropdown-item:focus {
		background: var(--eric-subtle) !important;
		color: var(--eric-text) !important;
	}

	.eric-shopping .dropdown-item.text-danger,
	body.night-mode .eric-shopping .dropdown-item.text-danger {
		color: var(--eric-danger) !important;
	}

	.eric-shopping .dropdown-divider,
	body.night-mode .eric-shopping .dropdown-divider {
		border-top-color: var(--eric-border);
		margin: 6px 0;
	}

	.night-mode .eric-shopping .eric-shopping-filter-panel .input-group-text,
	.night-mode .eric-shopping .eric-shopping-filter-panel .form-control,
	.night-mode .eric-shopping .eric-shopping-filter-panel .custom-select,
	.night-mode .eric-shopping .eric-shopping-filter-panel select,
	.night-mode .eric-shopping .eric-shopping-list-select {
		background-color: var(--eric-subtle) !important;
		border-color: transparent !important;
		color: var(--eric-text) !important;
	}

	.eric-shopping-filter {
		flex: 1 1 220px;
		min-width: 180px;
	}

	.eric-shopping-filter-actions {
		margin-left: auto;
	}

	.eric-shopping-content {
		display: grid;
		gap: 16px;
		grid-template-columns: minmax(0, 1fr);
	}

	.eric-shopping-content.with-calendar {
		grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr);
	}

	.eric-shopping-table-panel,
	.eric-shopping-side-panel,
	.eric-shopping-notes-panel {
		overflow: hidden;
		padding: 16px;
	}

	.eric-shopping-table-panel {
		padding: 0;
	}

	.eric-shopping-table-panel .dataTables_wrapper {
		padding: 16px;
	}

	#shoppinglist-table {
		background: #ffffff !important;
		border-collapse: separate !important;
		border-spacing: 0;
		color: var(--eric-text) !important;
		margin: 0 !important;
		width: 100% !important;
	}

	#shoppinglist-table thead th {
		background: #f7f9fc !important;
		border-bottom: 1px solid var(--eric-border) !important;
		color: var(--eric-muted) !important;
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.05em;
		line-height: 16px;
		padding: 12px 14px;
		text-transform: uppercase;
		vertical-align: middle;
	}

	#shoppinglist-table tbody td {
		background: #ffffff !important;
		border-top: 1px solid var(--eric-border) !important;
		color: var(--eric-text) !important;
		font-size: 13px;
		line-height: 18px;
		padding: 12px 14px;
		vertical-align: middle;
	}

	#shoppinglist-table tbody tr,
	#shoppinglist-table.table-striped tbody tr:nth-of-type(odd),
	#shoppinglist-table.table-striped tbody tr:nth-of-type(even) {
		background: #ffffff !important;
	}

	#shoppinglist-table tbody tr:hover,
	#shoppinglist-table tbody tr:hover td {
		background: var(--eric-subtle) !important;
	}

	#shoppinglist-table tbody tr.table-info td {
		background: #eef7ff !important;
	}

	#shoppinglist-table tbody tr.text-muted td,
	#shoppinglist-table tbody tr.text-muted td em {
		color: #8a94a3 !important;
	}

	#shoppinglist-table .btn {
		border-radius: 8px;
	}

	.eric-shopping-table-panel .dataTables_wrapper,
	.eric-shopping-table-panel .dataTables_info,
	.eric-shopping-table-panel .dataTables_length,
	.eric-shopping-table-panel .dataTables_filter,
	.eric-shopping-table-panel .dataTables_paginate {
		color: var(--eric-muted) !important;
	}

	.eric-shopping-table-panel .dataTables_length select,
	.eric-shopping-table-panel .dataTables_filter input,
	body.night-mode .eric-shopping-table-panel .dataTables_length select,
	body.night-mode .eric-shopping-table-panel .dataTables_filter input {
		background-color: var(--eric-subtle) !important;
		border: 0 !important;
		border-radius: 8px;
		color: var(--eric-text) !important;
		min-height: 36px;
	}

	body.night-mode .modal-content {
		background: #ffffff !important;
		border: 1px solid #e5e7eb !important;
		border-radius: 12px;
		color: #1d1d1f !important;
		box-shadow: 0 18px 48px rgba(29, 29, 31, 0.16);
	}

	body.night-mode .modal-footer {
		background: #ffffff !important;
		border-top: 1px solid #e5e7eb !important;
	}

	body.night-mode .bootbox-body,
	body.night-mode .modal-body {
		color: #1d1d1f !important;
	}

	.eric-shopping-notes-title {
		align-items: center;
		display: flex;
		gap: 8px;
		justify-content: space-between;
		margin-bottom: 8px;
	}

	.eric-shopping-notes-title label {
		font-size: 16px;
		font-weight: 600;
		line-height: 24px;
		margin: 0;
	}

	@media (max-width: 991.98px) {
		.eric-shopping {
			display: block;
		}

		.eric-shopping-sidebar {
			border-bottom: 1px solid var(--eric-border);
			border-right: 0;
			min-height: auto;
			width: 100%;
		}

		.eric-shopping-nav {
			display: flex;
			gap: 8px;
			overflow-x: auto;
			padding: 8px;
		}

		.eric-shopping-nav-section {
			border-top: 0;
			display: contents;
		}

		.eric-shopping-nav-label {
			display: none;
		}

		.eric-shopping-nav-link {
			flex: 0 0 auto;
			margin-bottom: 0;
			white-space: nowrap;
		}

		.eric-shopping-main {
			padding: 16px;
		}

		.eric-shopping-header {
			flex-direction: column;
			gap: 16px;
		}

		.eric-actions {
			justify-content: flex-start;
			width: 100%;
		}

		.eric-shopping-status-grid,
		.eric-shopping-content.with-calendar {
			grid-template-columns: 1fr;
		}
	}

	@media (max-width: 575.98px) {
		.eric-shopping-status-grid {
			grid-template-columns: 1fr;
		}
	}
</style>
@endpush

@push('pageScripts')
<script src="{{ $U('/viewjs/purchase.js?v=', true) }}{{ $version }}"></script>
@endpush

@section('content')
@php
$selectedShoppingList = FindObjectInArrayByPropertyValue($shoppingLists, 'id', $selectedShoppingListId);
$doneItemsCount = 0;
foreach($listItems as $listItemForCount)
{
	if($listItemForCount->done == 1)
	{
		$doneItemsCount++;
	}
}
@endphp

<div class="eric-shopping d-print-none hide-on-fullscreen-card">
	<aside class="eric-shopping-sidebar">
		<div class="eric-shopping-brand">
			<i class="fa-solid fa-gauge-high"></i>
			<span>Grocy</span>
		</div>
		<nav class="eric-shopping-nav">
			<a class="eric-shopping-nav-link"
				href="{{ $U('/eric-dashboard') }}">
				<i class="fa-solid fa-fw fa-gauge-high"></i>
				<span>Dashboard</span>
			</a>
			<a class="eric-shopping-nav-link"
				href="{{ $U('/eric-stockoverview') }}">
				<i class="fa-solid fa-fw fa-box"></i>
				<span>Stock</span>
			</a>
			<a class="eric-shopping-nav-link active"
				href="{{ $U('/eric-shoppinglist') }}">
				<i class="fa-solid fa-fw fa-shopping-cart"></i>
				<span>Shopping List</span>
			</a>
			<a class="eric-shopping-nav-link"
				href="{{ $U('/foodlibrary') }}">
				<i class="fa-solid fa-fw fa-bowl-food"></i>
				<span>Food Library</span>
			</a>
			<a class="eric-shopping-nav-link"
				href="{{ $U('/eric-recipes') }}">
				<i class="fa-solid fa-fw fa-pizza-slice"></i>
				<span>Recipes</span>
			</a>

			<div class="eric-shopping-nav-section">
				<p class="eric-shopping-nav-label">Quick Actions</p>
				<a class="eric-shopping-nav-link"
					href="{{ $U('/purchase') }}">
					<i class="fa-solid fa-fw fa-cart-plus"></i>
					<span>入库</span>
				</a>
				<a class="eric-shopping-nav-link"
					href="{{ $U('/consume') }}">
					<i class="fa-solid fa-fw fa-utensils"></i>
					<span>出库</span>
				</a>
				<a class="eric-shopping-nav-link"
					href="{{ $U('/inventory') }}">
					<i class="fa-solid fa-fw fa-clipboard-check"></i>
					<span>盘点</span>
				</a>
			</div>

			<div class="eric-shopping-nav-section">
				<a class="eric-shopping-nav-link"
					href="{{ $U('/products') }}">
					<i class="fa-solid fa-fw fa-table"></i>
					<span>Products</span>
				</a>
				<a class="eric-shopping-nav-link"
					href="{{ $U('/shoppinglistsettings') }}">
					<i class="fa-solid fa-fw fa-gear"></i>
					<span>Settings</span>
				</a>
			</div>
		</nav>
	</aside>

	<main class="eric-shopping-main">
		<header class="eric-shopping-header">
			<div>
				<h1 class="eric-shopping-title">{{ $__t('Shopping list') }}</h1>
				<p class="eric-shopping-subtitle">
					<span class="eric-status-dot"></span>
					<span>{{ $selectedShoppingList->name }}</span>
					<span>/</span>
					<span>{{ $listItems->count() }} 项</span>
					@if(GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING)
					<span>/</span>
					<span>{!! $__t('%s total value', '<span class="locale-number locale-number-currency">' . SumArrayValue($listItems, 'last_price_total') . '</span>') !!}</span>
					@endif
				</p>
			</div>
			<div class="eric-actions">
				<select class="custom-control custom-select eric-shopping-list-select"
					id="selected-shopping-list">
					@foreach($shoppingLists as $shoppingList)
					<option @if($shoppingList->id == $selectedShoppingListId) selected="selected" @endif value="{{ $shoppingList->id }}" data-shoppinglist-name="{{ $shoppingList->name }}">{{ $shoppingList->name }} ({{ $shoppingList->item_count }})</option>
					@endforeach
				</select>
				<a class="eric-btn eric-btn-primary show-as-dialog-link"
					href="{{ $U('/shoppinglistitem/new?embedded&list=' . $selectedShoppingListId) }}">
					<i class="fa-solid fa-plus"></i>
					{{ $__t('Add item') }}
				</a>
				<a class="eric-btn show-as-dialog-link"
					href="{{ $U('/shoppinglist/new?embedded') }}">
					<i class="fa-solid fa-folder-plus"></i>
					{{ $__t('New shopping list') }}
				</a>
				<div class="dropdown">
					<a class="eric-btn dropdown-toggle"
						href="#"
						data-toggle="dropdown">
						<i class="fa-solid fa-list-check"></i>
						{{ $__t('List actions') }}
					</a>
					<div class="dropdown-menu dropdown-menu-right">
						<a class="dropdown-item show-as-dialog-link"
							href="{{ $U('/shoppinglist/' . $selectedShoppingListId . '?embedded') }}">
							{{ $__t('Edit shopping list') }}
						</a>
						<a id="delete-selected-shopping-list"
							class="dropdown-item text-danger"
							href="#">
							{{ $__t('Delete shopping list') }}
						</a>
						<div class="dropdown-divider"></div>
						<a id="print-shopping-list-button"
							class="dropdown-item"
							href="#">
							{{ $__t('Print') }}
						</a>
						<div class="dropdown-divider"></div>
						<a id="clear-shopping-list"
							class="dropdown-item text-danger @if($listItems->count() == 0) disabled @endif"
							href="#">
							{{ $__t('Clear list') }}
						</a>
						<a id="clear-done-items"
							class="dropdown-item text-danger @if($listItems->count() == 0) disabled @endif"
							href="#">
							{{ $__t('Clear done items') }}
						</a>
					</div>
				</div>
				@if(GROCY_FEATURE_FLAG_STOCK)
				<div class="dropdown">
					<a class="eric-btn dropdown-toggle"
						href="#"
						data-toggle="dropdown">
						<i class="fa-solid fa-box"></i>
						{{ $__t('Stock actions') }}
					</a>
					<div class="dropdown-menu dropdown-menu-right">
						<a id="add-all-items-to-stock-button"
							class="dropdown-item"
							href="#">{{ $__t('Add all list items to stock') }}</a>
						@if(!boolval($userSettings['shopping_list_auto_add_below_min_stock_amount']))
						<a id="add-products-below-min-stock-amount"
							class="dropdown-item"
							href="#">{{ $__t('Add products that are below defined min. stock amount') }}</a>
						@endif
						<a id="add-overdue-expired-products"
							class="dropdown-item"
							href="#">{{ $__t('Add overdue/expired products') }}</a>
					</div>
				</div>
				@endif
			</div>
		</header>

		<div id="filter-container"
			class="eric-shopping-status-grid">
			<div class="eric-shopping-status-card">
				<span class="eric-shopping-status-label">{{ $__t('All') }}</span>
				<strong>{{ $listItems->count() }}</strong>
				<p class="eric-shopping-status-note">{{ $__t('Shopping list') }}</p>
			</div>
			@if(GROCY_FEATURE_FLAG_STOCK)
			<div data-status-filter="belowminstockamount"
				class="eric-shopping-status-card status-filter-message">
				<span class="eric-shopping-status-label">{{ $__t('Below min. stock amount') }}</span>
				<strong>{{ count($missingProducts) }}</strong>
				<p class="eric-shopping-status-note">点击筛选</p>
			</div>
			@endif
			<div class="eric-shopping-status-card">
				<span class="eric-shopping-status-label">已完成</span>
				<strong>{{ $doneItemsCount }}</strong>
				<p class="eric-shopping-status-note">{{ $__t('Clear done items') }}</p>
			</div>
		</div>

		<div class="eric-shopping-filter-panel"
			id="table-filter-row">
			<div class="eric-shopping-filter">
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
	<div class="eric-shopping-filter">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text"><i class="fa-solid fa-filter"></i>&nbsp;{{ $__t('Status') }}</span>
			</div>
			<select class="custom-control custom-select"
				id="status-filter">
				<option value="all">{{ $__t('All') }}</option>
				<option class="@if(!GROCY_FEATURE_FLAG_STOCK) d-none @endif"
					value="belowminstockamount">{{ $__t('Below min. stock amount') }}</option>
				<option value="xxDONExx">{{ $__t('Only done items') }}</option>
				<option value="xxUNDONExx">{{ $__t('Only undone items') }}</option>
			</select>
		</div>
	</div>
	<div class="eric-shopping-filter-actions">
		<button id="clear-filter-button"
			class="eric-btn"
			data-toggle="tooltip"
			title="{{ $__t('Clear filter') }}">
			<i class="fa-solid fa-filter-circle-xmark"></i>
			{{ $__t('Clear filter') }}
		</button>
	</div>
</div>

<div id="shoppinglist-main"
	class="eric-shopping-content @if(boolval($userSettings['shopping_list_show_calendar'])) with-calendar @endif d-print-none">
	<section class="eric-shopping-table-panel">
		<table id="shoppinglist-table"
			class="table table-sm table-striped nowrap w-100">
			<thead>
				<tr>
					<th class="border-right"><a class="text-muted change-table-columns-visibility-button"
							data-toggle="tooltip"
							title="{{ $__t('Table options') }}"
							data-table-selector="#shoppinglist-table"
							href="#"><i class="fa-solid fa-eye"></i></a>
					</th>
					<th class="allow-grouping">{{ $__t('Product') }} / <em>{{ $__t('Note') }}</em></th>
					<th>{{ $__t('Amount') }}</th>
					<th class="allow-grouping">{{ $__t('Product group') }}</th>
					<th class="d-none">Hidden status</th>
					<th class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif">{{ $__t('Last price (Unit)') }}</th>
					<th class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif">{{ $__t('Last price (Total)') }}</th>
					<th class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif allow-grouping">{{ $__t('Default store') }}</th>
					<th>{{ $__t('Barcodes') }}</th>

					@include('components.userfields_thead', array(
					'userfields' => $userfields
					))
					@include('components.userfields_thead', array(
					'userfields' => $productUserfields
					))

				</tr>
			</thead>
			<tbody class="d-none">
				@foreach($listItems as $listItem)
				<tr id="shoppinglistitem-{{ $listItem->id }}-row"
					class="@if(FindObjectInArrayByPropertyValue($missingProducts, 'id', $listItem->product_id) !== null) table-info @endif @if($listItem->done == 1) text-muted text-strike-through @endif">
					<td class="fit-content border-right">
						<a class="btn btn-success btn-sm order-listitem-button"
							href="#"
							data-item-id="{{ $listItem->id }}"
							data-item-done="{{ $listItem->done }}"
							data-toggle="tooltip"
							data-placement="right"
							title="{{ $__t('Mark this item as done') }}">
							<i class="fa-solid fa-check"></i>
						</a>
						<a class="btn btn-secondary btn-sm shoppinglist-complete-without-stock-button"
							href="#"
							data-shoppinglist-id="{{ $listItem->id }}"
							data-toggle="tooltip"
							data-placement="right"
							title="{{ $__t('Complete without adding to stock') }}">
							<i class="fa-solid fa-check-double"></i>
						</a>
						<a class="btn btn-sm btn-info show-as-dialog-link"
							href="{{ $U('/shoppinglistitem/' . $listItem->id . '?embedded&list=' . $selectedShoppingListId ) }}"
							data-toggle="tooltip"
							data-placement="right"
							title="{{ $__t('Edit this item') }}">
							<i class="fa-solid fa-edit"></i>
						</a>
						<a class="btn btn-sm btn-danger shoppinglist-delete-button"
							href="#"
							data-shoppinglist-id="{{ $listItem->id }}"
							data-toggle="tooltip"
							data-placement="right"
							title="{{ $__t('Delete this item') }}">
							<i class="fa-solid fa-trash"></i>
						</a>
						<a class="btn btn-sm btn-primary @if(GROCY_FEATURE_FLAG_STOCK) shopping-list-stock-add-workflow-list-item-button @else d-none @endif"
							href="{{ $U('/shoppinglistitem/' . $listItem->id . '/stock?embedded&list=' . $selectedShoppingListId) }}"
							data-toggle="tooltip"
							title="{{ $__t('Add this item to stock') }}">
							<i class="fa-solid fa-box"></i>
						</a>
					</td>
					<td class="productcard-trigger cursor-link"
						data-product-id="{{ $listItem->product_id }}">
						@if(!empty($listItem->product_id)) {{ $listItem->product_name }}<br>@elseif(!empty($listItem->free_text_name)) <strong>{{ $listItem->free_text_name }}</strong><br>@endif<em>{!! nl2br($listItem->note ?? '') !!}</em>
						@if(!empty($listItem->due_date))
							@php
								$today = date('Y-m-d');
								$tomorrow = date('Y-m-d', strtotime('+1 day'));
							@endphp
							<br><small class="@if($listItem->due_date <= $today) text-danger font-weight-bold @elseif($listItem->due_date == $tomorrow) text-warning font-weight-bold @else text-muted @endif">
								<i class="fa-solid fa-calendar-day"></i>
								@if($listItem->due_date < $today){{ $__t('Overdue') }}@elseif($listItem->due_date == $today){{ $__t('Today') }}@elseif($listItem->due_date == $tomorrow){{ $__t('Tomorrow') }}@else{{ $listItem->due_date }}@endif
							</small>
						@endif
					</td>
					@if(!empty($listItem->product_id))
					@php
					$listItem->amount_origin_qu = $listItem->amount;
					$product = FindObjectInArrayByPropertyValue($products, 'id', $listItem->product_id);
					$productQuConversions = FindAllObjectsInArrayByPropertyValue($quantityUnitConversionsResolved, 'product_id', $product->id);
					$productQuConversions = FindAllObjectsInArrayByPropertyValue($productQuConversions, 'from_qu_id', $product->qu_id_stock);
					$productQuConversion = FindObjectInArrayByPropertyValue($productQuConversions, 'to_qu_id', $listItem->qu_id);
					if ($productQuConversion)
					{
					$listItem->amount = $listItem->amount * $productQuConversion->factor;
					}

					if(boolval($userSettings['shopping_list_round_up']))
					{
					$listItem->amount = ceil($listItem->amount);
					$listItem->last_price_total = $listItem->price * $listItem->amount;
					}
					@endphp
					@endif
					<td>
						<span class="custom-sort d-none">{{$listItem->amount}}</span>
						<span class="locale-number locale-number-quantity-amount">{{ $listItem->amount }}</span> @if(!empty($listItem->product_id)){{ $__n($listItem->amount, $listItem->qu_name, $listItem->qu_name_plural, true) }}@endif
					</td>
					<td>
						@if(!empty($listItem->product_group_name)) {{ $listItem->product_group_name }} @else <span class="font-italic font-weight-light">{{ $__t('Ungrouped') }}</span> @endif
					</td>
					<td id="shoppinglistitem-{{ $listItem->id }}-status-info"
						class="d-none">
						@if(FindObjectInArrayByPropertyValue($missingProducts, 'id', $listItem->product_id) !== null) belowminstockamount @endif
						@if($listItem->done == 1) xxDONExx @else xxUNDONExx @endif
					</td>
					<td class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif">
						<span class="locale-number locale-number-currency">{{ $listItem->last_price_unit }}</span>
					</td>
					<td class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif">
						<span class="locale-number locale-number-currency">{{ $listItem->last_price_total }}</span>
					</td>
					<td class="@if(!GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING) d-none @endif">
						{{ $listItem->default_shopping_location_name }}
					</td>
					<td>
						@if($listItem->product_barcodes != null)
						@foreach(explode(',', $listItem->product_barcodes) as $barcode)
						@if(!empty($barcode))
						<img class="barcode img-fluid pr-2"
							data-barcode="{{ $barcode }}">
						@endif
						@endforeach
						@endif
					</td>

					@include('components.userfields_tbody', array(
					'userfields' => $userfields,
					'userfieldValues' => FindAllObjectsInArrayByPropertyValue($userfieldValues, 'object_id', $listItem->id)
					))
					@include('components.userfields_tbody', array(
					'userfields' => $productUserfields,
					'userfieldValues' => FindAllObjectsInArrayByPropertyValue($productUserfieldValues, 'object_id', $listItem->product_id)
					))

				</tr>
				@endforeach
			</tbody>
		</table>
	</section>

	@if(boolval($userSettings['shopping_list_show_calendar']))
	<aside class="eric-shopping-side-panel d-print-none">
		@include('components.calendarcard')
	</aside>
	@endif

	<section class="eric-shopping-notes-panel d-none d-print-none">
		<div class="form-group">
			<div class="eric-shopping-notes-title">
				<label for="notes">{{ $__t('Notes') }}</label>
				<div class="eric-actions">
					<a id="save-description-button"
						class="eric-btn"
						href="#">{{ $__t('Save') }}</a>
					<a id="clear-description-button"
						class="eric-btn"
						href="#">{{ $__t('Clear') }}</a>
				</div>
			</div>
			<textarea class="form-control wysiwyg-editor"
				id="description"
				name="description">{{ $selectedShoppingList->description }}</textarea>
		</div>
	</section>
</div>
	</main>
</div>

<div class="modal fade"
	id="shopping-list-stock-add-workflow-modal"
	tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content text-center">
			<div class="modal-body">
				<iframe id="shopping-list-stock-add-workflow-purchase-form-frame"
					class="embed-responsive">
				</iframe>
			</div>
			<div class="modal-footer d-none">
				<span id="shopping-list-stock-add-workflow-purchase-item-count"
					class="d-none mr-auto"></span>
				<button id="shopping-list-stock-add-workflow-skip-button"
					type="button"
					class="btn btn-primary">{{ $__t('Skip') }}</button>
			</div>
		</div>
	</div>
</div>

<div class="d-none d-print-block">
	<div id="print-header">
		<h1 class="text-center">
			<img src="{{ $U('/img/logo.svg?v=', true) }}{{ $version }}"
				width="114"
				height="30"
				class="d-print-flex mx-auto">
			{{ $__t("Shopping list") }}
		</h1>
		@if (FindObjectInArrayByPropertyValue($shoppingLists, 'id', $selectedShoppingListId)->name != $__t("Shopping list"))
		<h3 class="text-center">
			{{ FindObjectInArrayByPropertyValue($shoppingLists, 'id', $selectedShoppingListId)->name }}
		</h3>
		@endif
		<h6 class="text-center mb-4">
			{{ $__t('Time of printing') }}:
			<span class="d-inline print-timestamp"></span>
		</h6>
	</div>
	<div class="w-75 print-layout-container print-layout-type-table d-none">
		<div>
			<table id="shopping-list-print-shadow-table"
				class="table table-sm table-striped nowrap">
				<thead>
					<tr>
						<th>{{ $__t('Product') }} / <em>{{ $__t('Note') }}</em></th>
						<th>{{ $__t('Amount') }}</th>
						<th>{{ $__t('Product group') }}</th>

						@include('components.userfields_thead', array(
						'userfields' => $userfields
						))
						@include('components.userfields_thead', array(
						'userfields' => $productUserfields
						))
						@include('components.userfields_thead', array(
						'userfields' => $productGroupUserfields
						))
					</tr>
				</thead>
				<tbody>
					@foreach($listItems as $listItem)
					<tr>
						<td>
							@if(!empty($listItem->product_id)) {{ $listItem->product_name }}<br>@elseif(!empty($listItem->free_text_name)) <strong>{{ $listItem->free_text_name }}</strong><br>@endif<em>{!! nl2br($listItem->note ?? '') !!}</em>
						</td>
						<td>
							<span class="locale-number locale-number-quantity-amount">{{ $listItem->amount }}</span> @if(!empty($listItem->product_id)){{ $__n($listItem->amount, $listItem->qu_name, $listItem->qu_name_plural, true) }}@endif
						</td>
						<td>
							@if(!empty($listItem->product_group_name)) {{ $listItem->product_group_name }} @else <span class="font-italic font-weight-light">{{ $__t('Ungrouped') }}</span> @endif
						</td>

						@include('components.userfields_tbody', array(
						'userfields' => $userfields,
						'userfieldValues' => FindAllObjectsInArrayByPropertyValue($userfieldValues, 'object_id', $listItem->id)
						))
						@include('components.userfields_tbody', array(
						'userfields' => $productUserfields,
						'userfieldValues' => FindAllObjectsInArrayByPropertyValue($productUserfieldValues, 'object_id', $listItem->product_id)
						))
						@include('components.userfields_tbody', array(
						'userfields' => $productGroupUserfields,
						'userfieldValues' => FindAllObjectsInArrayByPropertyValue($productGroupUserfieldValues, 'object_id', $listItem->product_group_id)
						))

					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
	<div class="w-75 print-layout-container print-layout-type-list d-none">
		@foreach($listItems as $listItem)
		<div class="py-0">
			<span class="locale-number locale-number-quantity-amount">{{ $listItem->amount }}</span> @if(!empty($listItem->product_id)){{ $__n($listItem->amount, $listItem->qu_name, $listItem->qu_name_plural, true) }}@endif
			@if(!empty($listItem->product_id)) {{ $listItem->product_name }}<br>@elseif(!empty($listItem->free_text_name)) <strong>{{ $listItem->free_text_name }}</strong><br>@endif<em>{!! nl2br($listItem->note ?? '') !!}</em>
		</div><br>
		@endforeach
	</div>
	<div class="w-75 pt-3">
		<div>
			<h5>{{ $__t('Notes') }}</h5>
			<p id="description-for-print"></p>
		</div>
	</div>
</div>

@include('components.productcard', [
'asModal' => true
])
@stop
