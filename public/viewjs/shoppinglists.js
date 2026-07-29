$(document).on('click', '.delete-shopping-list:not(.disabled)', function (e)
{
	e.preventDefault();

	var listId = $(e.currentTarget).data('list-id');
	var listName = $(e.currentTarget).data('list-name');
	bootbox.confirm({
		message: __t('Are you sure you want to delete shopping list "%s"?', listName),
		buttons: {
			confirm: { label: __t('Yes'), className: 'btn-danger' },
			cancel: { label: __t('No'), className: 'btn-secondary' }
		},
		callback: function (confirmed)
		{
			if (!confirmed) return;
			Grocy.Api.Delete('objects/shopping_lists/' + listId, {}, function ()
			{
				window.location.reload();
			}, function (xhr)
			{
				console.error(xhr);
			});
		}
	});
});

$(window).on('message', function(e)
{
	var data = e.originalEvent.data;
	if (data && data.Message === 'ShoppingListChanged') window.location.reload();
});
