/**
 * MSTaxSync JS functions
 *
 * @author		Nir Goldberg
 * @package		js
 * @version		1.0.0
 */
var $ = jQuery,
	mstaxsync = {

		/**
		 * params
		 */
		params: {

			relationship_fields:	$('.mstaxsync-relationship'),
			rtl:					$('html').attr('dir') && 'rtl' == $('html').attr('dir'),

		},

		/**
		 * $list
		 *
		 * Returns list jQuery element
		 *
		 * @since		1.0.0
		 * @param		list (string)
		 * @return		(jQuery)
		 */
		$list: function(list) {

			// return
			return $('.' + list + '-list');

		},

		/**
		 * rsGetInputName
		 *
		 * @since		1.0.0
		 * @param		field (jQuery)
		 * @return		(string)
		 */
		rsGetInputName: function(field) {

			// return
			return field.data('name');

		},

		/**
		 * rsNewValue
		 *
		 * @since		1.0.0
		 * @param		props (array)
		 * @return		(string)
		 */
		rsNewValue: function(props) {

			// vars
			var edit_terms = _mstaxsync.settings.edit_terms;

			// return
			return [
			'<li class="new">',
				'<div>',
					'<span class="mstaxsync-rel-item" data-id="' + props.id + '">',
						'<span class="val">' + props.text + '</span>',
						(edit_terms ? '<input type="text" placeholder="' + props.text + '" />' : ''),
						'<span class="ind">(' + _mstaxsync.strings.relationship_new_item_str + ')</span>',
						(edit_terms ?
							'<span class="edit dashicons dashicons-edit"></span>' +
							'<span class="ok dashicons dashicons-yes"></span>' +
							'<span class="cancel dashicons dashicons-no"></span>'
						: ''),
						'<a href="#" class="remove dashicons dashicons-minus" data-name="remove_item"></a>',
					'</span>',
				'</div>',
			'</li>'
			].join('');

		},

		/**
		 * init
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		init: function() {

			// relationship fields
			mstaxsync.relationship();

		},

		/**
		 * relationship
		 *
		 * Relationship fields
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		relationship: function() {

			if (!mstaxsync.params.relationship_fields.length)
				return;

			// add choice
			$('.choices-list li .mstaxsync-rel-item').click(function() {
				mstaxsync.rsOnClickAdd($(this));
			});

			// remove value
			$('body').on('click', '.values-list li .remove', function() {
				mstaxsync.rsOnClickRemove($(this));
			});

			// edit value
			$('body').on('click', '.values-list li .edit', function() {
				mstaxsync.rsOnClickEdit($(this));
			});

			// submit edit value
			$('body').on('click', '.values-list li .ok', function() {
				mstaxsync.rsOnClickSubmitEdit($(this));
			});

			// Enter keypress as submit edit value
			$('body').on('keypress', '.values-list li input', function(event) {
				mstaxsync.rsOnKeypressSubmitEdit(event);
			});

			// cancel edit value
			$('body').on('click', '.values-list li .cancel', function() {
				mstaxsync.rsOnClickCancelEdit($(this));
			});

			// detach value
			$('body').on('click', '.values-list li .synced', function() {
				mstaxsync.rsOnClickDetach($(this));
			});

			// submit data
			$('#mstaxsync-taxonomies input[name="submit"]').click(function(event) {
				event.preventDefault();
				mstaxsync.rsOnSubmit($(this));
			});

			// nestedSortable list
			mstaxsync.relationshipNestedSortable();

		},

		/**
		 * rsOnClickAdd
		 *
		 * Appends choices item to values list
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickAdd: function(el) {

			// vars
			var field = el.closest(mstaxsync.params.relationship_fields);

			// can be added?
			if (el.hasClass('disabled')) {
				return false;
			}

			// disable
			el.addClass('disabled');

			// add
			var html = mstaxsync.rsNewValue({
				field: field,
				id: el.data('id'),
				text: el.children('span.val').text(),
			});

			field.find(mstaxsync.$list('values')).append(html);

		},

		/**
		 * rsOnClickRemove
		 *
		 * Removes item from values list
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickRemove: function(el) {

			// vars
			var field = el.closest(mstaxsync.params.relationship_fields),
				id = el.parent().data('id'),
				li = el.closest('li'),
				ul = li.children('ul'),
				siblings = li.siblings(),
				choice = field.find(mstaxsync.$list('choices')).find('li .mstaxsync-rel-item[data-id=' + id + ']');

			// unwrap li parent ul if does not have siblings and does not have children
			if (!siblings.length && !li.parent('ul').hasClass('list') && !ul.length) {
				li.unwrap('ul');
			}

			// remove
			if (ul.length) {
				// pull out children and unwrap li
				li.children('div').remove();
				ul.children('li').unwrap('ul').unwrap('li');
			}
			else {
				// remove li
				li.remove();
			}

			// enable
			choice.removeClass('disabled');

		},

		/**
		 * rsOnClickEdit
		 *
		 * Edits item name
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickEdit: function(el) {

			// vars
			var span = el.closest('.mstaxsync-rel-item'),
				input = span.children('input');

			// activate editing mode
			span.addClass('editing');

			// focus input
			input.focus();

		},

		/**
		 * rsOnClickSubmitEdit
		 *
		 * Submits item new name
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickSubmitEdit: function(el) {

			// vars
			var span = el.closest('.mstaxsync-rel-item'),
				val = span.children('span.val'),
				input = span.children('input');

			if (input.val().length) {
				// store new value
				val.text(input.val());

				// reset input
				input.attr('placeholder', val.text());
				input.val('');

				// indicate changed
				mstaxsync.rsIndicateChanged(span.closest('li'));
			}

			// deactivate editing mode
			span.removeClass('editing');

		},

		/**
		 * rsOnKeypressSubmitEdit
		 *
		 * Submits item new name
		 *
		 * @since		1.0.0
		 * @param		event
		 * @return		N/A
		 */
		rsOnKeypressSubmitEdit: function(event) {

			// vars
			var keycode = (event.keyCode ? event.keyCode : event.which);

			if ('13' == keycode) {
				mstaxsync.rsOnClickSubmitEdit($(event.target));
			}

			event.stopPropagation();

		},

		/**
		 * rsOnClickCancelEdit
		 *
		 * Cancels Edit and reverts to item old name
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickCancelEdit: function(el) {

			// vars
			var span = el.closest('.mstaxsync-rel-item'),
				input = span.children('input');

			// reset input
			input.val('');

			// deactivate editing mode
			span.removeClass('editing');

		},

		/**
		 * rsOnClickDetach
		 *
		 * Detaches item
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickDetach: function(el) {

			// vars
			var field = el.closest(mstaxsync.params.relationship_fields),
				nonce = el.data('nonce'),
				span = el.closest('.mstaxsync-rel-item'),
				localId = span.data('id'),
				mainId = span.data('synced'),
				choice = field.find(mstaxsync.$list('choices')).find('li .mstaxsync-rel-item[data-id=' + mainId + ']');
				detach_terms = _mstaxsync.settings.detach_terms;

			// check if detach terms capability is on
			if (!detach_terms)
				return;

			// expose loader
			el.addClass('rotate');

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: _mstaxsync.ajaxurl,
				data: {
					action: 'detach_taxonomy_term',
					nonce: nonce,
					main_id: mainId,
					local_id: localId,
				},
				success: function(response) {
					// update value
					el.remove();
					span.data('synced', '');

					// enable choice
					choice.removeClass('disabled');
					choice.data('synced', '');
				},
				error: function(jqXHR, textStatus, errorThrown) {
					el.removeClass('rotate');
				},
			});

		},

		/**
		 * rsIndicateChanged
		 *
		 * Adds indication for changed item
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsIndicateChanged: function(el) {

			// vars
			var span = el.find('.mstaxsync-rel-item').first(),
				val = span.children('span.val'),
				ind = span.children('span.ind'),
				edit = span.children('span.edit');

			el.addClass('changed');

			if (!ind.length) {

				ind = $( '<span class="ind">(' + _mstaxsync.strings.relationship_changed_item_str + ')</span>' );

				// edit might be missing in case of edit terms capability is off
				if (edit.length) {
					ind.insertBefore(edit);
				}
				else {
					ind.insertAfter(val);
				}

			}

		},

		/**
		 * rsOnSubmit
		 *
		 * Submits relationship fields data
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnSubmit: function(el) {

			// vars
			var nonce = el.data('nonce'),
				taxonomyTerms = [],
				loader = $('.submit .ajax-loading'),
				result = $('.submit').next('.result'),
				msg = [];

			// expose loader
			loader.css('visibility', 'visible');

			// hide result message
			result.html('').hide();

			// group terms by taxonomy
			$.each(mstaxsync.params.relationship_fields, function() {
				var values = $(this).find(mstaxsync.$list('values')).children('li'),
					terms = [];

				if (values.length) {
					values.each(function() {
						mstaxsync.rsBuildTaxonomyTerms(terms, $(this), 0);
					});

					taxonomyTerms.push({
						taxonomy: $(this).data('name'),
						terms: terms,
					});
				}
			});

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: _mstaxsync.ajaxurl,
				data: {
					action: 'taxonomy_terms_sync',
					nonce: nonce,
					taxonomy_terms: taxonomyTerms,
				},
				success: function(response) {
					// refresh relationship field lists
					//mstaxsync.rsRefreshLists();

					// build result message
					mstaxsync.rsBuildResultMsg(response, msg);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					// result message
					msg.push(_mstaxsync.strings.relationship_internal_server_error_str);
				},
				complete: function(jqXHR, textStatus) {
					// hide loader
					loader.css('visibility', 'hidden');

					// expose result message
					result.show().html(msg.join('<br />'));
				},
			});

		},

		/**
		 * rsBuildTaxonomyTerms
		 *
		 * Builds taxonomyTerms in a recursive way
		 *
		 * @since		1.0.0
		 * @param		terms (array)
		 * @param		el (jQuery)
		 * @param		parent_id (int)
		 * @return		N/A
		 */
		rsBuildTaxonomyTerms: function(terms, el, parent_id) {

			// vars
			var span = el.find('.mstaxsync-rel-item').first(),
				id = span.data('id'),
				name = span.children('.val').text(),
				source = el.hasClass('new') ? 'main' : 'local',
				children = el.children('ul').children('li');

			terms.push({id: id, name: name, source: source, parent: parent_id});

			if (children.length) {
				parent_id = id;

				children.each(function() {
					mstaxsync.rsBuildTaxonomyTerms(terms, $(this), parent_id);
				});
			}

		},

		/**
		 * rsBuildResultMsg
		 *
		 * Builds result message
		 *
		 * @since		1.0.0
		 * @param		response (json)
		 * @param		msg (array)
		 * @return		N/A
		 */
		rsBuildResultMsg: function(response, msg) {

			// no errors found
			if (!response.errors.length) {
				msg.push(_mstaxsync.strings.relationship_success_str);
			}

			// number of main terms synced
			if (response.main.length) {
				msg.push(response.main.length + ' ' + _mstaxsync.strings.relationship_main_terms_str);
			}

			// number of local terms updated
			if (response.local.length) {
				msg.push(response.local.length + ' ' + _mstaxsync.strings.relationship_local_terms_str);
			}

			// errors found
			if (response.errors.length) {
				msg.push(_mstaxsync.strings.relationship_errors_str);

				$.each(response.errors, function(key, value) {
					msg.push(_mstaxsync.strings.relationship_error_str + ' #' + value.code + ': ' + value.description);
				});
			}

		},

		/**
		 * relationshipNestedSortable
		 *
		 * Initializes relationship nestedSortable
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		relationshipNestedSortable: function() {

			mstaxsync.$list('values').nestedSortable({
				listType: 'ul',
				items: 'li',
				handle: 'div',
				toleranceElement: '> div',
				opacity: .6,
				revert: 250,
				rtl: mstaxsync.params.rtl,
				relocate: function(event, ui){
					mstaxsync.rsIndicateChanged(ui.item);
				},
			});

		},

	};

$(mstaxsync.init);