@extends('layout.default')

@if($mode == 'edit')
@section('title', $__t('Edit shopping list item'))
@else
@section('title', $__t('Create shopping list item'))
@endif

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

			@if(GROCY_FEATURE_FLAG_SHOPPINGLIST_MULTIPLE_LISTS)
			<div class="form-group">
				<label for="shopping_list_id">{{ $__t('Shopping list') }}</label>
				<select class="custom-control custom-select"
					id="shopping_list_id"
					name="shopping_list_id">
					@foreach($shoppingLists as $shoppingList)
					<option @if($mode=='edit'
						&&
						$shoppingList->id == $listItem->shopping_list_id) selected="selected" @endif value="{{ $shoppingList->id }}">{{ $shoppingList->name }}</option>
					@endforeach
				</select>
			</div>
			@else
			<input type="hidden"
				id="shopping_list_id"
				name="shopping_list_id"
				value="1">
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
