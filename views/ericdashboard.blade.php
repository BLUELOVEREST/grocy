@extends('layout.default')

@section('title', 'Eric Dashboard')

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

	.eric-dashboard {
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

	.eric-dashboard a {
		color: inherit;
	}

	.eric-dashboard-sidebar {
		background: #f2f4f7;
		border-right: 1px solid var(--eric-border);
		display: flex;
		flex: 0 0 240px;
		flex-direction: column;
		min-height: 100vh;
		width: 240px;
	}

	.eric-dashboard-brand {
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

	.eric-dashboard-nav {
		flex: 1;
		overflow-y: auto;
		padding: 16px 8px;
	}

	.eric-dashboard-nav-section {
		border-top: 1px solid var(--eric-border);
		margin-top: 16px;
		padding-top: 16px;
	}

	.eric-dashboard-nav-label {
		color: var(--eric-muted);
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.05em;
		line-height: 16px;
		margin: 0 0 8px;
		padding: 0 12px;
		text-transform: uppercase;
	}

	.eric-dashboard-nav-link {
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

	.eric-dashboard-nav-link:hover {
		background: #e0e3e6;
		color: var(--eric-text);
		text-decoration: none;
	}

	.eric-dashboard-nav-link.active {
		background: var(--eric-primary);
		color: #ffffff;
		font-weight: 500;
	}

	.eric-dashboard-main {
		flex: 1;
		min-width: 0;
		padding: 32px;
	}

	.eric-dashboard-header,
	.eric-dashboard-panel {
		background: var(--eric-panel);
		border: 1px solid var(--eric-border);
		border-radius: 12px;
		box-shadow: 0 1px 2px rgba(29, 29, 31, 0.04);
	}

	.eric-dashboard-header {
		align-items: center;
		display: flex;
		justify-content: space-between;
		margin-bottom: 24px;
		padding: 20px 24px;
	}

	.eric-dashboard-title {
		font-size: 24px;
		font-weight: 600;
		line-height: 32px;
		margin: 0;
	}

	.eric-dashboard-subtitle {
		align-items: center;
		color: var(--eric-muted);
		display: flex;
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
		border: 1px solid var(--eric-border);
		border-radius: 8px;
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

	.eric-search-panel {
		margin-bottom: 24px;
		min-height: 0;
		padding: 24px;
	}

	.eric-search-wrap {
		margin: 0 auto;
		max-width: 920px;
		position: relative;
	}

	.eric-search-input {
		background: var(--eric-subtle);
		border: 0;
		border-radius: 8px;
		color: var(--eric-text);
		font-size: 16px;
		font-weight: 600;
		line-height: 24px;
		min-height: 56px;
		padding: 15px 56px 15px 50px;
		width: 100%;
	}

	.eric-search-input:focus {
		background: #ffffff;
		box-shadow: 0 0 0 2px rgba(47, 128, 237, 0.24);
		outline: none;
	}

	.eric-search-icon,
	.eric-search-button {
		align-items: center;
		color: var(--eric-muted);
		display: flex;
		height: 40px;
		justify-content: center;
		position: absolute;
		top: 8px;
		width: 40px;
	}

	.eric-search-icon {
		left: 8px;
	}

	.eric-search-button {
		background: transparent;
		border: 0;
		right: 8px;
	}

	.eric-search-button:hover {
		color: var(--eric-primary);
	}

	.eric-search-results {
		margin: 24px auto 0;
		max-width: 980px;
	}

	.eric-label {
		color: var(--eric-muted);
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.05em;
		line-height: 16px;
		margin: 0 0 12px;
		text-transform: uppercase;
	}

	.eric-result-row,
	.eric-alert-row,
	.eric-shopping-row {
		align-items: center;
		border: 1px solid transparent;
		border-radius: 8px;
		display: flex;
		gap: 16px;
		justify-content: space-between;
		padding: 12px;
		transition: background 120ms ease, border-color 120ms ease;
	}

	.eric-result-row:hover,
	.eric-alert-row:hover,
	.eric-shopping-row:hover {
		background: var(--eric-subtle);
		border-color: var(--eric-border);
		text-decoration: none;
	}

	.eric-result-main,
	.eric-alert-main,
	.eric-activity-main {
		min-width: 0;
	}

	.eric-result-title,
	.eric-alert-title,
	.eric-shopping-title {
		font-size: 16px;
		font-weight: 600;
		line-height: 24px;
		margin: 0;
	}

	.eric-result-meta,
	.eric-alert-meta,
	.eric-activity-meta {
		color: var(--eric-muted);
		font-size: 13px;
		line-height: 18px;
		margin: 2px 0 0;
	}

	.eric-result-icon,
	.eric-activity-icon {
		align-items: center;
		background: var(--eric-subtle);
		border-radius: 8px;
		color: var(--eric-muted);
		display: flex;
		flex: 0 0 40px;
		height: 40px;
		justify-content: center;
		width: 40px;
	}

	.eric-dashboard-grid {
		display: grid;
		gap: 24px;
		grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
		margin-bottom: 24px;
	}

	.eric-dashboard-panel {
		display: flex;
		flex-direction: column;
		min-height: 500px;
		overflow: hidden;
	}

	.eric-dashboard-panel.eric-search-panel {
		min-height: 0;
	}

	.eric-panel-header {
		align-items: center;
		background: rgba(238, 242, 246, 0.55);
		border-bottom: 1px solid var(--eric-border);
		display: flex;
		justify-content: space-between;
		padding: 16px;
	}

	.eric-panel-title {
		align-items: center;
		display: flex;
		font-size: 16px;
		font-weight: 600;
		gap: 8px;
		line-height: 24px;
		margin: 0;
	}

	.eric-count {
		background: #eceef1;
		border: 1px solid var(--eric-border);
		border-radius: 4px;
		color: var(--eric-muted);
		font-size: 13px;
		font-weight: 500;
		line-height: 18px;
		padding: 4px 8px;
	}

	.eric-panel-body {
		flex: 1;
		overflow: auto;
		padding: 16px;
	}

	.eric-panel-footer {
		background: #f2f4f7;
		border-top: 1px solid var(--eric-border);
		padding: 12px;
		text-align: center;
	}

	.eric-quantity-pill {
		background: var(--eric-bg);
		border: 1px solid var(--eric-border);
		border-radius: 4px;
		color: var(--eric-muted);
		font-size: 13px;
		font-weight: 500;
		line-height: 18px;
		padding: 4px 8px;
		white-space: nowrap;
	}

	.eric-alert-group + .eric-alert-group {
		margin-top: 24px;
	}

	.eric-alert-heading {
		align-items: center;
		color: var(--eric-muted);
		display: flex;
		font-size: 12px;
		font-weight: 600;
		gap: 8px;
		letter-spacing: 0.05em;
		line-height: 16px;
		margin: 0 0 12px;
		text-transform: uppercase;
	}

	.eric-alert-dot {
		border-radius: 999px;
		display: inline-block;
		height: 8px;
		width: 8px;
	}

	.eric-empty {
		color: var(--eric-muted);
		font-size: 13px;
		font-style: italic;
		margin: 0;
		padding: 0 8px;
	}

	.eric-secondary-grid {
		display: grid;
		gap: 24px;
		grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
	}

	.eric-compact-panel {
		min-height: 0;
		padding: 16px;
	}

	.eric-compact-panel .eric-label {
		border-bottom: 1px solid var(--eric-border);
		padding-bottom: 8px;
	}

	.eric-activity-row {
		align-items: flex-start;
		display: flex;
		gap: 12px;
		padding: 8px 0;
	}

	.eric-chip-list {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}

	.eric-chip {
		background: var(--eric-subtle);
		border: 1px solid var(--eric-border);
		border-radius: 999px;
		color: var(--eric-text);
		display: inline-flex;
		font-size: 13px;
		line-height: 18px;
		padding: 6px 12px;
		text-decoration: none;
	}

	.eric-chip:hover {
		background: #e0e3e6;
		text-decoration: none;
	}

	.eric-color-primary {
		color: var(--eric-primary);
	}

	.eric-color-green {
		color: var(--eric-green);
	}

	.eric-color-amber {
		color: var(--eric-amber);
	}

	.eric-color-danger {
		color: var(--eric-danger);
	}

	@media (max-width: 991.98px) {
		.eric-dashboard {
			flex-direction: column;
		}

		.eric-dashboard-sidebar {
			border-bottom: 1px solid var(--eric-border);
			border-right: 0;
			min-height: 0;
			width: 100%;
		}

		.eric-dashboard-nav {
			display: flex;
			gap: 8px;
			overflow-x: auto;
			padding: 12px;
		}

		.eric-dashboard-nav-section {
			border-top: 0;
			display: contents;
			margin-top: 0;
			padding-top: 0;
		}

		.eric-dashboard-nav-label {
			display: none;
		}

		.eric-dashboard-nav-link {
			flex: 0 0 auto;
			margin-bottom: 0;
			white-space: nowrap;
		}

		.eric-dashboard-main {
			padding: 16px;
		}

		.eric-dashboard-header,
		.eric-dashboard-grid,
		.eric-secondary-grid {
			grid-template-columns: 1fr;
		}

		.eric-dashboard-header {
			align-items: flex-start;
			flex-direction: column;
			gap: 16px;
		}

		.eric-actions {
			justify-content: flex-start;
			width: 100%;
		}
	}
</style>
@endpush

@section('content')
<script>
	Grocy.EricDashboardProducts = {!! json_encode($productsForSearch, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>

<div class="eric-dashboard">
	<aside class="eric-dashboard-sidebar">
		<div class="eric-dashboard-brand">
			<i class="fa-solid fa-gauge-high"></i>
			<span>Grocy</span>
		</div>
		<nav class="eric-dashboard-nav">
			<a class="eric-dashboard-nav-link active"
				href="{{ $U('/eric-dashboard') }}">
				<i class="fa-solid fa-fw fa-gauge-high"></i>
				<span>Dashboard</span>
			</a>
			<a class="eric-dashboard-nav-link"
				href="{{ $U('/eric-stockoverview') }}">
				<i class="fa-solid fa-fw fa-box"></i>
				<span>Stock</span>
			</a>
			<a class="eric-dashboard-nav-link"
				href="{{ $U('/eric-shoppinglist') }}">
				<i class="fa-solid fa-fw fa-shopping-cart"></i>
				<span>Shopping List</span>
			</a>
			<a class="eric-dashboard-nav-link"
				href="{{ $U('/foodlibrary') }}">
				<i class="fa-solid fa-fw fa-bowl-food"></i>
				<span>Food Library</span>
			</a>
			<a class="eric-dashboard-nav-link"
				href="{{ $U('/eric-recipes') }}">
				<i class="fa-solid fa-fw fa-pizza-slice"></i>
				<span>Recipes</span>
			</a>

			<div class="eric-dashboard-nav-section">
				<p class="eric-dashboard-nav-label">Quick Actions</p>
				<a class="eric-dashboard-nav-link"
					href="{{ $U('/purchase') }}">
					<i class="fa-solid fa-fw fa-cart-plus"></i>
					<span>入库</span>
				</a>
				<a class="eric-dashboard-nav-link"
					href="{{ $U('/consume') }}">
					<i class="fa-solid fa-fw fa-utensils"></i>
					<span>出库</span>
				</a>
				<a class="eric-dashboard-nav-link"
					href="{{ $U('/inventory') }}">
					<i class="fa-solid fa-fw fa-clipboard-check"></i>
					<span>盘点</span>
				</a>
			</div>

			<div class="eric-dashboard-nav-section">
				<a class="eric-dashboard-nav-link"
					href="{{ $U('/products') }}">
					<i class="fa-solid fa-fw fa-table"></i>
					<span>Products</span>
				</a>
				<a class="eric-dashboard-nav-link"
					href="{{ $U('/stocksettings') }}">
					<i class="fa-solid fa-fw fa-gear"></i>
					<span>Settings</span>
				</a>
			</div>
		</nav>
	</aside>

	<main class="eric-dashboard-main">
	<header class="eric-dashboard-header">
		<div>
			<h1 class="eric-dashboard-title">Eric Grocy</h1>
			<p class="eric-dashboard-subtitle">
				<span class="eric-status-dot"></span>
				本地产品 {{ count($productsForSearch) }} 项 / 食品库与薄荷回退沿用现有入口 / 服务正常
			</p>
		</div>
		<div class="eric-actions">
			<a class="eric-btn"
				href="{{ $U('/purchase') }}"><i class="fa-solid fa-plus"></i> 入库</a>
			<a class="eric-btn"
				href="{{ $U('/consume') }}"><i class="fa-solid fa-minus"></i> 出库</a>
			<a class="eric-btn"
				href="{{ $U('/inventory') }}"><i class="fa-solid fa-clipboard-check"></i> 盘点</a>
			<a class="eric-btn eric-btn-primary"
				href="{{ $U('/product/new') }}"><i class="fa-solid fa-box-open"></i> 添加产品</a>
		</div>
	</header>

	<section class="eric-dashboard-panel eric-search-panel">
		<div class="eric-search-wrap">
			<span class="eric-search-icon"><i class="fa-solid fa-search"></i></span>
			<input id="eric-dashboard-search"
				class="eric-search-input"
				type="text"
				autocomplete="off"
				placeholder="搜索所有产品">
			<button id="eric-dashboard-search-button"
				class="eric-search-button"
				type="button"
				title="{{ $__t('Search') }}">
				<i class="fa-solid fa-arrow-right"></i>
			</button>
		</div>
		<div class="eric-search-results">
			<p id="eric-dashboard-search-label"
				class="eric-label">Quick Results</p>
			<div id="eric-dashboard-search-results"></div>
		</div>
	</section>

	<div class="eric-dashboard-grid">
		<section class="eric-dashboard-panel">
			<div class="eric-panel-header">
				<h2 class="eric-panel-title">
					<i class="fa-solid fa-shopping-cart eric-color-primary"></i>
					购物清单
				</h2>
				<span class="eric-count">{{ $selectedShoppingList === null ? 0 : $selectedShoppingList->item_count }} pending</span>
			</div>
			<div class="eric-panel-body">
				@if($selectedShoppingList === null)
				<p class="eric-empty">还没有购物清单。</p>
				@else
				@foreach($shoppingListItems as $listItem)
				<a class="eric-shopping-row show-as-dialog-link"
					href="{{ $U('/shoppinglistitem/' . $listItem->id . '?embedded&list=' . $selectedShoppingList->id) }}">
					<span class="eric-shopping-title">
						@if(!empty($listItem->product_id))
						{{ $listItem->product_name }}
						@else
						{{ $listItem->free_text_name }}
						@endif
					</span>
					<span class="eric-quantity-pill">
						<span class="locale-number locale-number-quantity-amount">{{ $listItem->amount }}</span>
						@if(!empty($listItem->product_id))
						{{ $__n($listItem->amount, $listItem->qu_name, $listItem->qu_name_plural, true) }}
						@endif
					</span>
				</a>
				@endforeach
				@if($selectedShoppingList->item_count == 0)
				<p class="eric-empty">当前购物清单为空。</p>
				@endif
				@endif
			</div>
			<div class="eric-panel-footer">
				@if($selectedShoppingList === null)
				<a class="eric-btn eric-btn-primary show-as-dialog-link"
					href="{{ $U('/shoppinglist/new?embedded') }}">创建购物清单</a>
				@else
				<a class="eric-btn show-as-dialog-link"
					href="{{ $U('/shoppinglistitem/new?embedded&list=' . $selectedShoppingList->id) }}">添加购物项</a>
				<a class="eric-btn eric-btn-primary"
					href="{{ $U('/eric-shoppinglist?list=' . $selectedShoppingList->id) }}">打开购物清单</a>
				@endif
			</div>
		</section>

		<section class="eric-dashboard-panel">
			<div class="eric-panel-header">
				<h2 class="eric-panel-title">
					<i class="fa-solid fa-triangle-exclamation eric-color-amber"></i>
					库存提醒
				</h2>
			</div>
			<div class="eric-panel-body">
				<div class="eric-alert-group">
					<h3 class="eric-alert-heading"><span class="eric-alert-dot"
							style="background: var(--eric-amber);"></span>临期</h3>
					@forelse(array_slice($dueProducts, 0, 4) as $dueProduct)
					<a class="eric-alert-row"
						href="{{ $U('/eric-stockoverview') }}">
						<div class="eric-alert-main">
							<p class="eric-alert-title">{{ $dueProduct->product_name }}</p>
							<p class="eric-alert-meta">{{ $dueProduct->best_before_date }}</p>
						</div>
						<i class="fa-solid fa-utensils eric-color-primary"></i>
					</a>
					@empty
					<p class="eric-empty">No expiring items.</p>
					@endforelse
				</div>

				<div class="eric-alert-group">
					<h3 class="eric-alert-heading"><span class="eric-alert-dot"
							style="background: var(--eric-danger);"></span>已过期</h3>
					@forelse(array_slice($expiredProducts, 0, 3) as $expiredProduct)
					<a class="eric-alert-row"
						href="{{ $U('/eric-stockoverview') }}">
						<div class="eric-alert-main">
							<p class="eric-alert-title">{{ $expiredProduct->product_name }}</p>
							<p class="eric-alert-meta">{{ $expiredProduct->best_before_date }}</p>
						</div>
						<i class="fa-solid fa-box-open eric-color-danger"></i>
					</a>
					@empty
					<p class="eric-empty">No expired items.</p>
					@endforelse
				</div>

				<div class="eric-alert-group">
					<h3 class="eric-alert-heading"><span class="eric-alert-dot"
							style="background: var(--eric-primary);"></span>低库存</h3>
					@forelse(array_slice($missingProducts, 0, 4) as $missingProduct)
					<a class="eric-alert-row"
						href="{{ $U('/eric-stockoverview') }}">
						<div class="eric-alert-main">
							<p class="eric-alert-title">{{ $missingProduct->name ?? $missingProduct->product->name ?? $__t('Product') }}</p>
							<p class="eric-alert-meta">
								缺少 <span class="locale-number locale-number-quantity-amount">{{ $missingProduct->amount_missing }}</span>
							</p>
						</div>
						<i class="fa-solid fa-cart-plus eric-color-primary"></i>
					</a>
					@empty
					<p class="eric-empty">No low-stock items.</p>
					@endforelse
				</div>
			</div>
			<div class="eric-panel-footer">
				<a class="eric-btn eric-btn-primary"
					href="{{ $U('/eric-stockoverview') }}">查看库存</a>
			</div>
		</section>
	</div>

	<div class="eric-secondary-grid">
		<section class="eric-dashboard-panel eric-compact-panel">
			<h3 class="eric-label">最近操作</h3>
			@forelse($recentStockTransactions as $transaction)
			<div class="eric-activity-row">
				<span class="eric-activity-icon">
					@if(($transaction->transaction_type ?? '') === 'purchase')
					<i class="fa-solid fa-plus eric-color-green"></i>
					@elseif(($transaction->transaction_type ?? '') === 'consume')
					<i class="fa-solid fa-minus eric-color-amber"></i>
					@else
					<i class="fa-solid fa-clock-rotate-left eric-color-primary"></i>
					@endif
				</span>
				<div class="eric-activity-main">
					<p class="eric-result-title">{{ $transaction->product_name ?? $transaction->product ?? $__t('Product') }}</p>
					<p class="eric-activity-meta">{{ $transaction->transaction_type ?? $__t('Stock') }} · {{ $transaction->row_created_timestamp ?? '' }}</p>
				</div>
			</div>
			@empty
			<p class="eric-empty">No recent activity.</p>
			@endforelse
		</section>

		<section class="eric-dashboard-panel eric-compact-panel">
			<h3 class="eric-label">常用入口</h3>
			<div class="eric-chip-list">
				<a class="eric-chip"
					href="{{ $U('/foodlibrary') }}">食品库</a>
				<a class="eric-chip"
					href="{{ $U('/products') }}">产品管理</a>
				<a class="eric-chip"
					href="{{ $U('/eric-recipes') }}">菜谱</a>
				<a class="eric-chip"
					href="{{ $U('/purchase') }}">入库</a>
				<a class="eric-chip"
					href="{{ $U('/consume') }}">出库</a>
				<a class="eric-chip"
					href="{{ $U('/inventory') }}">盘点</a>
			</div>
		</section>
	</div>
	</main>
</div>
@endsection

@section('viewJsName', 'ericdashboard')
