$(function() {
	$(".navbar-expand-toggle").click(function() {
		$(".app-container").toggleClass("expanded");
		return $(".navbar-expand-toggle").toggleClass("fa-rotate-90");
	});

	return $(".navbar-right-expand-toggle").click(function() {
		$(".navbar-right").toggleClass("expanded");
		return $(".navbar-right-expand-toggle").toggleClass("fa-rotate-90");
	});
});

$(function() {
	return $('.match-height').matchHeight();
});

$(function() {
	return $(".side-menu .nav .dropdown").on('show.bs.collapse', function() {
		return $(".side-menu .nav .dropdown .collapse").collapse('hide');
	});
});

$(function() {
	function load_plugins() {
		$('[data-toggle="confirmation"]').confirmation({
			placement: 'auto left',
			singleton: true,
			popout: true,
			copyAttributes: 'data-href data-params',
			btnOkLabel: 'Bekræft',
			btnCancelLabel: 'Annuller'
		});

		$('.toggle-checkbox').bootstrapSwitch({
			size: 'mini',
			onColor: 'success',
			offColor: 'danger',
			onText: '<i class="fa fa-check" aria-hidden="true"></i>',
			offText: '<i class="fa fa-times" aria-hidden="true"></i>'
		});

		$('.select2').select2();

		var sortable_cache = $('#sortable').html();
		$('#sortable').sortable({
			items : '.sortable-item',
			handle: '.sortable-handle',
			distance: 5,
			update: function() {
				var data_array	= []; // opretter et tom array
				$('.sortable-item').each(function(index) { //for hvert ting i id'et sortable med class sortable-item kører vi en funktion med index (nummer fra 0-XX)
					data_array[index] = {id: $(this).attr('id')}; // Tilføj id'et fra hvert element til et array

					// Opdatér sorteringsnummeret i første kolonne
					$('#' + $(this).attr('id') + ' td:first-child').text(index + 1);
				});
				var data_object =
				{
					type	: $(this).data('type'),
					section	: $(this).data('section'),
					data	: data_array
				};
				//do ajax request to toggle_status.php, send the jsonObject as data and use post. Return the data from the php-file as json-encoded
				$.ajax({
					type    : 'post',
					url     : 'includes/sortable.php',
					data    : data_object,
					dataType: 'json',
					//on success, check if the returned status is false. If it is, return order to the previous state
					success : function (response)
					{
						if ( !response.status)
						{
							$('#sortable').html(sortable_cache);
						}
					}
				})
			}
		});
		prettyPrint();

        //when we toggle checkbox with class .toggle-checkbox, do this (checkbox is replaced with bootstrapSwitch)
        $('.toggle-checkbox') .on('switchChange.bootstrapSwitch', function (event, state) {
            //save data from the clicked item in to json object, that can be passed through the ajax request
            var $this   = $(this),
                jsonObject =
            {
                'status':state,
                'type'  : $(this).data('type'),
                'id'    : $(this).attr('id')
            };
            //do ajax request to toggle_status.php, send the jsonObject as data and use post. Return the data from the php-file as json-encoded
            $.ajax({
                type    : 'post',
                url     : 'includes/toggle_status.php',
                data    : jsonObject,
                dataType: 'json',
                //on success, check if the returned status is false. If it is, return state to the previous state
                success : function (response)
                {
                    if ( !response.status ) $this.bootstrapSwitch('state', !state, 'skip');

                }
            })
        });
	}

	load_plugins();

	function load_breadcrumb(page, params) {
		$.get( 'includes/breadcrumb.php', 'page=' + page + ( params != '' ? '&' + params : '') )
			.done( function (data) {
				$("#breadcrumb").html(data);
			})
	}

	function load_content(page, params, data, method) {
		$('.modal').modal('hide');
		$('body').removeClass('modal-open');
		$('.modal-backdrop').remove();
		var content		= $("#main-content"),
			nav_items	= $('.nav.navbar-nav li'),
			file_path	= 'view/' + page + '.php' + ( params != '' ? '?' + params : '');

		content.parent('.container-fluid').addClass('loader');
		$.ajax({
			type: typeof method !== 'undefined' ? method : 'get',
			url: file_path,
			data: typeof data !== 'undefined' ? data : '',
			success: function(data)
			{
				content.html(data).attr('data-content', page);
				load_plugins();
				load_breadcrumb(page, params);
				content.parent('.container-fluid').removeClass('loader');
				nav_items.removeClass('active');
				nav_items.each( function() {
					if ($(this).find('a').data('page') == page) {
						$(this).addClass('active');
					}
				})
			},
			error: function(xhr)
			{
				load_content('error', 'status=' + xhr.status);
			}
		});
	}

	function getQueryVariable(variable)
	{
		var query = window.location.search.substring(1);
		var vars = query.split("&");
		for (var i=0;i<vars.length;i++) {
			var pair = vars[i].split("=");
			if(pair[0] == variable){return pair[1];}
		}
		return(false);
	}

	$(window).bind("popstate", function() {
		var query_string = location.search.substring(1),
			page	= getQueryVariable('page'),
			params	= '';

		if (page !== false) {
			params	= query_string.replace('page=' + page + '&', '');
		}
		else {
			page = 'index';
		}

		load_content(page, params);
	});

	$(document.body).delegate('a[data-page]', 'click', function(e) {
		e.preventDefault();
		var page		= $(this).data('page'),
			params		= $(this).data('params') ? $(this).data('params') : '',
			href		= $(this).attr('href');
		history.pushState({}, null, href);
		load_content(page, params);
	});

	$(document.body).delegate('form[data-page]', 'afterSubmit', function(e) {
		e.preventDefault();
		var method		= $(this).attr('method') ? $(this).attr('method') : 'get',
			page		= $(this).data('page'),
			data		= $(this).serialize(),
			params		= method == 'post' ? ($(this).data('params') ? $(this).data('params') : '') : data.replace('page=' + page + '&', ''),
			href		= page == 'index' ? '' : 'index.php?page=' + page + ( params != '' ? '&' + params : '');
		history.pushState({}, null, href);
		load_content(page, params, method == 'post' ? data : '', method);
	});

	$(document.body).delegate('select[data-change="submit-form"]', 'change', function() {
		$(this).closest('form').submit();
	});


	// If there's a instance of CKeditor, we update the current elements ID when content is changed.
	if ( typeof(CKEDITOR) != 'undefined')
	{
		$.each( CKEDITOR.instances, function(instance) {
			CKEDITOR.instances[instance].on('blur', function() {
				var sel = $('#'+instance);
				CKEDITOR.instances[instance].updateElement();
				sel.trigger('change');
			});
		});
	}

	$(document.body).on('change', '#content_type', function() {
		var item	= $(this),
			value	= item.val();

		if (value == 1)
		{
			$('#1').show();
			$('#2').hide();
			$('#1 #description').attr('required', true);
			$('#2 #page_function').attr('required', false);
		}
		else
		{
			$('#1').hide();
			$('#2').show();
			$('#1 #description').attr('required', false);
			$('#2 #page_function').attr('required', true);
		}
	});

	$(document.body).on('change', '#link_type', function() {
		var item	= $(this),
			value	= item.val();

		if (value == 2)
		{
			$('#2').show();
			$('#2 #post').attr('required', true);
		}
		else
		{
			$('#2').hide();
			$('#2 #post').attr('required', false);
		}
	});
});
