@extends('layout.default')

@section('title', $__t('Add shopping list item to stock'))

@section('content')
<script>
	Grocy.QuantityUnits = {!! json_encode($quantityUnits) !!};
	Grocy.QuantityUnitConversionsResolved = {!! json_encode($quantityUnitConversionsResolved) !!};
	Grocy.ShoppingListItemId = {{ $listItem->id }};
	Grocy.ShoppingListItemProductId = @if($listItem->product_id !== null){{ $listItem->product_id }}@else null @endif;
	Grocy.ShoppingListItemFreeTextName = {!! json_encode($listItem->free_text_name) !!};
	Grocy.ShoppingListItemAmount = {{ $listItem->amount }};
	Grocy.ShoppingListItemQuId = @if($listItem->qu_id !== null){{ $listItem->qu_id }}@else null @endif;
</script>

<div class="row">
	<div class="col-12 col-md-6 col-xl-4 pb-3">
		<h2 class="title">@yield('title')</h2>

		<hr class="my-2">

		@if(!empty($listItem->free_text_name))
		<p class="text-muted small">
			{{ $__t('Original shopping list item') }}: {{ $listItem->free_text_name }}
		</p>
		@endif

		<form id="shoppinglistitemstock-form"
			novalidate>

			@include('components.productpicker', array(
			'products' => $products,
			'barcodes' => $barcodes,
			'nextInputSelector' => '#display_amount',
			'prefillById' => $listItem->product_id,
			'disallowAddProductWorkflows' => true,
			'validationMessage' => 'You have to select a product'
			))

			@include('components.productamountpicker', array(
			'value' => $listItem->amount,
			'initialQuId' => $listItem->qu_id
			))

			@if(boolval($userSettings['show_purchased_date_on_purchase']))
			@include('components.datetimepicker2', array(
			'id' => 'purchased_date',
			'label' => 'Purchased date',
			'format' => 'YYYY-MM-DD',
			'initWithNow' => true,
			'limitEndToNow' => false,
			'limitStartToNow' => false,
			'invalidFeedback' => $__t('A purchased date is required'),
			'nextInputSelector' => '#best_before_date',
			'additionalCssClasses' => 'date-only-datetimepicker2',
			'activateNumberPad' => GROCY_FEATURE_FLAG_STOCK_BEST_BEFORE_DATE_FIELD_NUMBER_PAD
			))
			@endif

			@if(GROCY_FEATURE_FLAG_STOCK_BEST_BEFORE_DATE_TRACKING)
			@include('components.datetimepicker', array(
			'id' => 'best_before_date',
			'label' => 'Due date',
			'format' => 'YYYY-MM-DD',
			'initWithNow' => false,
			'limitEndToNow' => false,
			'limitStartToNow' => false,
			'invalidFeedback' => $__t('A due date is required'),
			'nextInputSelector' => '#price',
			'additionalCssClasses' => 'date-only-datetimepicker',
			'shortcutValue' => '2999-12-31',
			'shortcutLabel' => 'Never overdue',
			'earlierThanInfoLimit' => date('Y-m-d'),
			'earlierThanInfoText' => $__t('The given date is earlier than today, are you sure?'),
			'activateNumberPad' => GROCY_FEATURE_FLAG_STOCK_BEST_BEFORE_DATE_FIELD_NUMBER_PAD
			))
			@endif

			@if(GROCY_FEATURE_FLAG_STOCK_PRICE_TRACKING)
			@include('components.numberpicker', array(
			'id' => 'price',
			'label' => 'Price',
			'min' => '0.' . str_repeat('0', $userSettings['stock_decimal_places_prices_input']),
			'decimals' => $userSettings['stock_decimal_places_prices_input'],
			'value' => '',
			'contextInfoId' => 'price-hint',
			'isRequired' => false,
			'additionalGroupCssClasses' => 'mb-1',
			'additionalCssClasses' => 'locale-number-input locale-number-currency'
			))

			@include('components.shoppinglocationpicker', array(
			'label' => 'Store',
			'shoppinglocations' => $shoppinglocations
			))
			@else
			<input type="hidden"
				name="price"
				id="price"
				value="0">
			@endif

			@if(GROCY_FEATURE_FLAG_STOCK_LOCATION_TRACKING)
			@include('components.locationpicker', array(
			'locations' => $locations,
			'isRequired' => false
			))
			@endif

			<div class="form-group">
				<label for="note">{{ $__t('Note') }}</label>
				<div class="input-group">
					<input type="text"
						class="form-control"
						id="note"
						name="note"
						value="{{ $listItem->note }}">
				</div>
			</div>

			@include('components.userfieldsform', array(
			'userfields' => $userfields,
			'entity' => 'stock'
			))

			<button id="save-shoppinglistitemstock-button"
				class="btn btn-success d-block">{{ $__t('OK') }}</button>

		</form>
	</div>

	<div class="col-12 col-md-6 col-xl-4 hide-when-embedded">
		@include('components.productcard')
	</div>
</div>
@stop
