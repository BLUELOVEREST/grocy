@extends('layout.default')

@section('title', $__t('Shopping lists'))

@section('content')
<div class="row">
	<div class="col">
		<div class="title-related-links">
			<h2 class="title mr-auto">@yield('title')</h2>
			<a class="btn btn-primary show-as-dialog-link"
				href="{{ $U('/shoppinglist/new?embedded') }}">
				<i class="fa-solid fa-plus"></i> {{ $__t('New shopping list') }}
			</a>
		</div>
	</div>
</div>

@if($shoppingLists->count() == 0)
<div class="row mt-5">
	<div class="col-12 text-center py-5">
		<i class="fa-solid fa-list-check fa-3x text-muted mb-3"></i>
		<h3>{{ $__t('No shopping lists yet') }}</h3>
		<p class="text-muted">{{ $__t('Create a shopping list before adding items.') }}</p>
		<a class="btn btn-primary show-as-dialog-link"
			href="{{ $U('/shoppinglist/new?embedded') }}">
			{{ $__t('Create shopping list') }}
		</a>
	</div>
</div>
@else
<div class="row mt-3">
	@foreach($shoppingLists as $shoppingList)
	<div class="col-12 col-md-6 col-xl-4 mb-3">
		<div class="card h-100 shopping-list-card">
			<div class="card-body d-flex align-items-center">
				<a class="stretched-link flex-grow-1 shopping-list-card-link"
					href="{{ $U('/shoppinglist?list=' . $shoppingList->id) }}">
					<h4 class="mb-1">{{ $shoppingList->name }}</h4>
					<span class="text-muted">{{ $__n($shoppingList->item_count, '%s item', '%s items') }}</span>
				</a>
				<div class="dropdown position-relative" style="z-index: 2;">
					<button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
						<i class="fa-solid fa-ellipsis"></i>
					</button>
					<div class="dropdown-menu dropdown-menu-right">
						<a class="dropdown-item show-as-dialog-link" href="{{ $U('/shoppinglist/' . $shoppingList->id . '?embedded') }}">{{ $__t('Edit') }}</a>
						<a class="dropdown-item text-danger delete-shopping-list @if($shoppingList->item_count > 0) disabled text-muted @endif"
							href="#" data-list-id="{{ $shoppingList->id }}" data-list-name="{{ $shoppingList->name }}">{{ $__t('Delete') }}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	@endforeach
</div>
@endif
@stop
