@extends('layout.default')

@if($mode == 'edit')
@section('title', $__t('Edit shopping list item'))
@else
@section('title', $__t('Create shopping list item'))
@endif

@push('pageStyles')
<style>
	body.embedded {
		background: #ffffff !important;
		color: #1d1d1f;
	}

	body.embedded.night-mode,
	body.embedded.night-mode .content-wrapper,
	body.embedded.night-mode .container-fluid {
		background: #ffffff !important;
		color: #1d1d1f !important;
	}

	body.embedded .content-wrapper {
		background: #ffffff;
		min-height: auto;
		padding-top: 0;
	}

	body.embedded .container-fluid {
		padding: 20px 24px !important;
	}

	body.embedded #page-content {
		padding: 0;
	}

	body.embedded .row:first-of-type {
		margin: 0 0 16px;
	}

	body.embedded .row:first-of-type .col {
		padding: 0;
	}

	body.embedded h2.title {
		color: #1d1d1f;
		font-size: 20px;
		font-weight: 600;
		line-height: 28px;
		margin: 0;
	}

	body.embedded hr {
		display: none;
	}

	body.embedded .row {
		margin-left: 0;
		margin-right: 0;
	}

	body.embedded .col-12 {
		flex: 0 0 100%;
		max-width: 100%;
		padding: 0;
	}

	body.embedded .form-group {
		margin-bottom: 14px;
	}

	body.embedded label {
		color: #5f6673;
		font-size: 12px;
		font-weight: 600;
		letter-spacing: 0.04em;
		line-height: 16px;
		margin-bottom: 6px;
		text-transform: uppercase;
	}

	body.embedded .form-control,
	body.embedded .custom-select,
	body.embedded .input-group-text,
	body.embedded.night-mode .form-control,
	body.embedded.night-mode .custom-select,
	body.embedded.night-mode select,
	body.embedded.night-mode .input-group-text {
		background-color: #eef2f6 !important;
		border: 0 !important;
		border-radius: 8px;
		color: #1d1d1f !important;
		font-size: 14px;
		min-height: 40px;
	}

	body.embedded textarea.form-control {
		min-height: 96px;
	}

	body.embedded .form-control:focus,
	body.embedded .custom-select:focus {
		background-color: #ffffff !important;
		box-shadow: 0 0 0 2px rgba(47, 128, 237, 0.24);
	}

	body.embedded .btn {
		border-radius: 8px;
		font-size: 13px;
		font-weight: 500;
		min-height: 36px;
	}

	body.embedded #save-shoppinglist-button {
		background: #2f80ed;
		border-color: #2f80ed;
		color: #ffffff;
		min-height: 40px;
		padding-left: 16px;
		padding-right: 16px;
	}
</style>
@endpush

@section('content')
<script>
	Grocy.QuantityUnits = {!! json_encode($quantityUnits) !!};
	Grocy.QuantityUnitConversionsResolved = {!! json_encode($quantityUnitConversionsResolved) !!};
</script>

<div class="row">
	<div class="col">
		<h2 class="title">@yield('title')</h2>
	</div>
</div>

<hr class="my-2">

<div class="row">
	<div class="col-12 col-md-6 col-xl-4 pb-3">
		<script>
			Grocy.EditMode = '{{ $mode }}';
		</script>

		@if($mode == 'edit')
		<script>
			Grocy.EditObjectId = {{ $listItem->id }};
		</script>
		@endif

		<form id="shoppinglist-form"
			novalidate>

			@if($mode == 'create' && $selectedShoppingListId !== null)
			<input type="hidden"
				id="shopping_list_id"
				name="shopping_list_id"
				data-shopping-list-name="{{ FindObjectInArrayByPropertyValue($shoppingLists, 'id', $selectedShoppingListId)->name }}"
				value="{{ $selectedShoppingListId }}">
			<div class="form-group">
				<label>{{ $__t('Shopping list') }}</label>
				<input class="form-control" type="text" readonly
					value="{{ FindObjectInArrayByPropertyValue($shoppingLists, 'id', $selectedShoppingListId)->name }}">
			</div>
			@else
			<div class="form-group">
				<label for="shopping_list_id">{{ $__t('Shopping list') }}</label>
				<select class="custom-control custom-select"
					id="shopping_list_id"
					name="shopping_list_id"
					required>
					@if($mode == 'create')<option value="" selected disabled>{{ $__t('Select a shopping list') }}</option>@endif
					@foreach($shoppingLists as $shoppingList)
					<option @if($shoppingList->id == $selectedShoppingListId) selected="selected" @endif value="{{ $shoppingList->id }}">{{ $shoppingList->name }}</option>
					@endforeach
				</select>
			</div>
			@endif

			<div class="form-group">
				<label for="free_text_name">{{ $__t('Item name') }}</label>
				<input class="form-control"
					type="text"
					id="free_text_name"
					name="free_text_name"
					@if($mode == 'edit') value="{{ $listItem->free_text_name }}" @endif
					placeholder="{{ $__t('For example: tomatoes, face wash, screws') }}">
				<div class="invalid-feedback">{{ $__t('A product or an item name is required') }}</div>
			</div>

			<div>
				@php if($mode == 'edit') { $productId = $listItem->product_id; } else { $productId = ''; } @endphp
				@include('components.productpicker', array(
				'products' => $products,
				'barcodes' => $barcodes,
				'nextInputSelector' => '#amount',
				'isRequired' => false,
				'prefillById' => $productId,
				'validationMessage' => 'A product or an item name is required'
				))
			</div>

			@php if($mode == 'edit') { $value = $listItem->amount; } else { $value = 1; } @endphp
			@php if($mode == 'edit') { $initialQuId = $listItem->qu_id; } else { $initialQuId = ''; } @endphp
			@include('components.productamountpicker', array(
			'value' => $value,
			'initialQuId' => $initialQuId,
			'allowZero' => true,
			'isRequired' => false
			))

			<div class="form-group">
				<label for="due_date">{{ $__t('Buy by') }}</label>
				<input class="form-control"
					type="date"
					id="due_date"
					name="due_date"
					@if($mode == 'edit' && !empty($listItem->due_date)) value="{{ $listItem->due_date }}" @endif>
				<div class="btn-group btn-group-sm mt-2" role="group">
					<button type="button" class="btn btn-outline-secondary set-shopping-due-date" data-offset="0">{{ $__t('Today') }}</button>
					<button type="button" class="btn btn-outline-secondary set-shopping-due-date" data-offset="1">{{ $__t('Tomorrow') }}</button>
					<button type="button" class="btn btn-outline-secondary" id="set-shopping-due-weekend">{{ $__t('This weekend') }}</button>
					<button type="button" class="btn btn-outline-secondary" id="clear-shopping-due-date">{{ $__t('Clear') }}</button>
				</div>
			</div>

			<div class="form-group">
				<label for="note">{{ $__t('Note') }}</label>
				<textarea class="form-control"
					rows="10"
					id="note"
					name="note">@if($mode == 'edit'){{ $listItem->note }}@endif</textarea>
			</div>

			@include('components.userfieldsform', array(
			'userfields' => $userfields,
			'entity' => 'shopping_list'
			))

			<button id="save-shoppinglist-button"
				class="btn btn-success">{{ $__t('Save') }}</button>

		</form>
	</div>
</div>
@stop
