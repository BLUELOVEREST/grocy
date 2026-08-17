@php require_frontend_packages(['datatables']); @endphp

@extends('layout.default')

@section('title', $__t('Food library'))
@section('viewJsVersion', $version . '-foodlibrary-ui-20260817')

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

	.eric-food-library {
		--eric-bg: #f5f7fa;
		--eric-panel: #ffffff;
		--eric-subtle: #eef2f6;
		--eric-border: #e5e7eb;
		--eric-text: #1d1d1f;
		--eric-muted: #6b7280;
		--eric-primary: #2f80ed;
		--eric-green: #34c759;
		--eric-danger: #ba1a1a;
		background: var(--eric-bg);
		color: var(--eric-text);
		display: flex;
		font-family: Roboto, Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
		min-height: 100vh;
	}

	.eric-food-library a {
		color: inherit;
	}

	.eric-food-sidebar {
		background: #f2f4f7;
		border-right: 1px solid var(--eric-border);
		display: flex;
		flex: 0 0 240px;
		flex-direction: column;
		min-height: 100vh;
		width: 240px;
	}

	.eric-food-brand {
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

	.eric-food-nav {
		flex: 1;
		overflow-y: auto;
		padding: 16px 8px;
	}

	.eric-food-nav-section {
		border-top: 1px solid var(--eric-border);
		margin-top: 16px;
		padding-top: 16px;
	}

	.eric-food-nav-label {
		color: var(--eric-muted);
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.05em;
		line-height: 16px;
		margin: 0 0 8px;
		padding: 0 12px;
		text-transform: uppercase;
	}

	.eric-food-nav-link {
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

	.eric-food-nav-link:hover {
		background: #e0e3e6;
		color: var(--eric-text);
		text-decoration: none;
	}

	.eric-food-nav-link.active {
		background: var(--eric-primary);
		color: #ffffff;
		font-weight: 500;
	}

	.eric-food-main {
		flex: 1;
		min-width: 0;
		padding: 32px;
	}

	.eric-food-header,
	.eric-food-search-panel,
	.eric-food-table-panel {
		background: var(--eric-panel);
		border: 1px solid var(--eric-border);
		border-radius: 12px;
		box-shadow: 0 1px 2px rgba(29, 29, 31, 0.04);
	}

	.eric-food-header {
		align-items: flex-start;
		display: flex;
		justify-content: space-between;
		margin-bottom: 16px;
		padding: 20px 24px;
	}

	.eric-food-title {
		font-size: 24px;
		font-weight: 600;
		line-height: 32px;
		margin: 0;
	}

	.eric-food-subtitle {
		color: var(--eric-muted);
		font-size: 13px;
		line-height: 18px;
		margin: 6px 0 0;
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

	.eric-food-search-panel {
		margin-bottom: 16px;
		padding: 16px;
	}

	.eric-food-search-wrap {
		align-items: center;
		display: grid;
		gap: 12px;
		grid-template-columns: minmax(240px, 1fr) auto auto;
	}

	.eric-food-search-field {
		align-items: center;
		background: var(--eric-subtle);
		border-radius: 10px;
		display: flex;
		min-height: 44px;
		padding: 0 12px;
	}

	.eric-food-search-field i {
		color: var(--eric-muted);
		margin-right: 10px;
	}

	.eric-food-search-field .form-control,
	body.night-mode .eric-food-search-field .form-control {
		background: transparent !important;
		border: 0 !important;
		box-shadow: none !important;
		color: var(--eric-text) !important;
		font-size: 14px;
		height: 44px;
		padding: 0;
	}

	.eric-food-table-panel {
		overflow: hidden;
		padding: 0;
	}

	.eric-food-table-panel .dataTables_wrapper {
		color: var(--eric-muted) !important;
		padding: 16px;
	}

	#food-library-table {
		background: #ffffff !important;
		border-collapse: separate !important;
		border-spacing: 0;
		color: var(--eric-text) !important;
		margin: 0 !important;
		width: 100% !important;
	}

	#food-library-table thead th {
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

	#food-library-table tbody tr,
	#food-library-table.table-striped tbody tr:nth-of-type(odd),
	#food-library-table.table-striped tbody tr:nth-of-type(even) {
		background: #ffffff !important;
	}

	#food-library-table tbody td {
		background: #ffffff !important;
		border-top: 1px solid var(--eric-border) !important;
		color: var(--eric-text) !important;
		font-size: 13px;
		line-height: 18px;
		padding: 12px 14px;
		vertical-align: middle;
	}

	#food-library-table tbody tr:hover,
	#food-library-table tbody tr:hover td {
		background: var(--eric-subtle) !important;
	}

	#food-library-table .badge {
		border-radius: 999px;
		font-size: 11px;
		font-weight: 500;
		padding: 4px 8px;
	}

	#food-library-table .btn {
		border-radius: 8px;
		font-size: 12px;
		font-weight: 500;
	}

	.eric-food-table-panel .dataTables_length select,
	.eric-food-table-panel .dataTables_filter input,
	body.night-mode .eric-food-table-panel .dataTables_length select,
	body.night-mode .eric-food-table-panel .dataTables_filter input {
		background-color: var(--eric-subtle) !important;
		border: 0 !important;
		border-radius: 8px;
		color: var(--eric-text) !important;
		min-height: 36px;
	}

	body.night-mode .modal-content,
	#food-library-aliases-modal .modal-content {
		background: #ffffff !important;
		border: 1px solid #e5e7eb !important;
		border-radius: 12px;
		box-shadow: 0 18px 48px rgba(29, 29, 31, 0.16);
		color: #1d1d1f !important;
	}

	body.night-mode .modal-header,
	body.night-mode .modal-footer,
	#food-library-aliases-modal .modal-header,
	#food-library-aliases-modal .modal-footer {
		background: #ffffff !important;
		border-color: #e5e7eb !important;
		color: #1d1d1f !important;
	}

	#food-library-aliases-modal label {
		color: #5f6673;
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.04em;
		text-transform: uppercase;
	}

	#food-library-aliases-modal .form-control,
	body.night-mode #food-library-aliases-modal .form-control {
		background-color: #eef2f6 !important;
		border: 0 !important;
		border-radius: 8px;
		color: #1d1d1f !important;
		font-size: 14px;
	}

	@media (max-width: 991.98px) {
		.eric-food-library {
			display: block;
		}

		.eric-food-sidebar {
			border-bottom: 1px solid var(--eric-border);
			border-right: 0;
			min-height: auto;
			width: 100%;
		}

		.eric-food-nav {
			display: flex;
			gap: 8px;
			overflow-x: auto;
			padding: 8px;
		}

		.eric-food-nav-section {
			border-top: 0;
			display: contents;
		}

		.eric-food-nav-label {
			display: none;
		}

		.eric-food-nav-link {
			flex: 0 0 auto;
			margin-bottom: 0;
			white-space: nowrap;
		}

		.eric-food-main {
			padding: 16px;
		}

		.eric-food-header {
			flex-direction: column;
			gap: 16px;
		}

		.eric-actions {
			justify-content: flex-start;
			width: 100%;
		}

		.eric-food-search-wrap {
			grid-template-columns: 1fr;
		}
	}
</style>
@endpush

@push('pageScripts')
<script>
	delete Grocy.UserSettings["datatables_state_food-library-table"];
</script>
@endpush

@section('content')
<div class="eric-food-library d-print-none hide-on-fullscreen-card">
	<aside class="eric-food-sidebar">
		<div class="eric-food-brand">
			<i class="fa-solid fa-gauge-high"></i>
			<span>Grocy</span>
		</div>
		<nav class="eric-food-nav">
			<a class="eric-food-nav-link"
				href="{{ $U('/eric-dashboard') }}">
				<i class="fa-solid fa-fw fa-gauge-high"></i>
				<span>Dashboard</span>
			</a>
			<a class="eric-food-nav-link"
				href="{{ $U('/stockoverview') }}">
				<i class="fa-solid fa-fw fa-box"></i>
				<span>Stock</span>
			</a>
			<a class="eric-food-nav-link"
				href="{{ $U('/shoppinglist') }}">
				<i class="fa-solid fa-fw fa-shopping-cart"></i>
				<span>Shopping List</span>
			</a>
			<a class="eric-food-nav-link active"
				href="{{ $U('/eric-foodlibrary') }}">
				<i class="fa-solid fa-fw fa-bowl-food"></i>
				<span>Food Library</span>
			</a>
			<a class="eric-food-nav-link"
				href="{{ $U('/recipes') }}">
				<i class="fa-solid fa-fw fa-pizza-slice"></i>
				<span>Recipes</span>
			</a>

			<div class="eric-food-nav-section">
				<p class="eric-food-nav-label">Quick Actions</p>
				<a class="eric-food-nav-link"
					href="{{ $U('/purchase') }}">
					<i class="fa-solid fa-fw fa-cart-plus"></i>
					<span>入库</span>
				</a>
				<a class="eric-food-nav-link"
					href="{{ $U('/consume') }}">
					<i class="fa-solid fa-fw fa-utensils"></i>
					<span>出库</span>
				</a>
				<a class="eric-food-nav-link"
					href="{{ $U('/inventory') }}">
					<i class="fa-solid fa-fw fa-clipboard-check"></i>
					<span>盘点</span>
				</a>
			</div>

			<div class="eric-food-nav-section">
				<a class="eric-food-nav-link"
					href="{{ $U('/products') }}">
					<i class="fa-solid fa-fw fa-table"></i>
					<span>Products</span>
				</a>
				<a class="eric-food-nav-link"
					href="{{ $U('/userfields?entity=products') }}">
					<i class="fa-solid fa-fw fa-gear"></i>
					<span>Settings</span>
				</a>
			</div>
		</nav>
	</aside>

	<main class="eric-food-main">
		<header class="eric-food-header">
			<div>
				<h1 class="eric-food-title">@yield('title')</h1>
				<p class="eric-food-subtitle">本地营养数据库，支持别名搜索和薄荷候选导入</p>
			</div>
			<div class="eric-actions">
				<a class="eric-btn eric-btn-primary show-as-dialog-link"
					href="{{ $U('/product/new?embedded') }}">
					<i class="fa-solid fa-plus"></i>
					{{ $__t('Add') }}
				</a>
			</div>
		</header>

		<section class="eric-food-search-panel"
			id="table-filter-row">
			<div class="eric-food-search-wrap">
				<div class="eric-food-search-field">
					<i class="fa-solid fa-search"></i>
					<input type="text"
						id="food-library-search"
						class="form-control"
						placeholder="{{ $__t('Search') }}">
				</div>
				<button type="button"
					class="eric-btn eric-btn-primary"
					id="food-library-search-button"
					title="{{ $__t('Search') }}">
					<i class="fa-solid fa-search"></i>
					{{ $__t('Search') }}
				</button>
				<button type="button"
					class="eric-btn"
					id="food-library-external-search-button"
					title="{{ $__t('Search Boohee') }}">
					<i class="fa-solid fa-cloud-arrow-down"></i>
					{{ $__t('Boohee') }}
				</button>
			</div>
		</section>

		<section class="eric-food-table-panel">
		<table id="food-library-table"
			class="table table-sm table-striped nowrap w-100">
			<thead>
				<tr>
					<th>{{ $__t('Name') }}</th>
					<th>{{ $__t('Aliases') }}</th>
					<th>{{ $__t('Energy') }}</th>
					<th>{{ $__t('Protein') }}</th>
					<th>{{ $__t('Carbs') }}</th>
					<th>{{ $__t('Fat') }}</th>
					<th>{{ $__t('Nutrition basis') }}</th>
					<th>{{ $__t('Source') }}</th>
					<th>{{ $__t('Actions') }}</th>
				</tr>
			</thead>
			<tbody class="d-none"></tbody>
		</table>
		</section>
	</main>
</div>

<div class="modal fade"
	id="food-library-aliases-modal"
	tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ $__t('Aliases') }}</h5>
				<button type="button"
					class="close"
					data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<input type="hidden"
					id="food-library-aliases-product-id">
				<div class="form-group">
					<label for="food-library-aliases-input">{{ $__t('Aliases') }}</label>
					<textarea class="form-control"
						id="food-library-aliases-input"
						rows="5"></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button"
					class="btn btn-secondary"
					data-dismiss="modal">{{ $__t('Cancel') }}</button>
				<button type="button"
					class="btn btn-primary"
					id="food-library-aliases-save">{{ $__t('Save') }}</button>
			</div>
		</div>
	</div>
</div>
@endsection

@section('viewJsName', 'foodlibrary')
