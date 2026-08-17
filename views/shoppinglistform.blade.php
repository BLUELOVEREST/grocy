@extends('layout.default')

@if($mode == 'edit')
@section('title', $__t('Edit shopping list'))
@else
@section('title', $__t('Create shopping list'))
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

	body.embedded .col-lg-6 {
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
	body.embedded.night-mode .form-control,
	body.embedded.night-mode .custom-select,
	body.embedded.night-mode select {
		background-color: #eef2f6 !important;
		border: 0 !important;
		border-radius: 8px;
		color: #1d1d1f !important;
		font-size: 14px;
		min-height: 40px;
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
		min-height: 40px;
		padding-left: 16px;
		padding-right: 16px;
	}

	body.embedded #save-shopping-list-button {
		background: #2f80ed;
		border-color: #2f80ed;
		color: #ffffff;
	}
</style>
@endpush

@section('content')
<div class="row">
	<div class="col">
		<h2 class="title">@yield('title')</h2>
	</div>
</div>

<hr class="my-2">

<div class="row">
	<div class="col-lg-6 col-12">
		<script>
			Grocy.EditMode = '{{ $mode }}';
		</script>

		@if($mode == 'edit')
		<script>
			Grocy.EditObjectId = {{ $shoppingList->id }};
		</script>
		@endif

		<form id="shopping-list-form"
			novalidate>

			<div class="form-group">
				<label for="name">{{ $__t('Name') }}</label>
				<input type="text"
					class="form-control"
					required
					id="name"
					name="name"
					value="@if($mode == 'edit'){{ $shoppingList->name }}@endif">
				<div class="invalid-feedback">{{ $__t('A name is required') }}</div>
			</div>

			@include('components.userfieldsform', array(
			'userfields' => $userfields,
			'entity' => 'shopping_lists'
			))

			<button id="save-shopping-list-button"
				class="btn btn-success">{{ $__t('Save') }}</button>

		</form>
	</div>
</div>
@stop
