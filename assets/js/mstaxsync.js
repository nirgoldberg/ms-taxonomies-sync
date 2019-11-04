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
			var edit_terms = _mstaxsync.settings.edit_terms,
				children = [];

			if (props.children && props.children.length) {
				$.each(props.children, function(key, value) {
					children.push(
						mstaxsync.rsNewValue({
							id: value.id,
							text: value.text,
							children: value.children,
						})
					);
				});

				children = children.join('');
			}

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
						'<span class="remove dashicons dashicons-minus"></span>',
					'</span>',
				'</div>',
				(children.length ? '<ul>' + children + '</ul>' : ''),
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

			// vars
			var advanced_treeview = _mstaxsync.settings.advanced_treeview;

			if (advanced_treeview.length) {
				// advanced treeview
				mstaxsync.relationshipAdvancedTreeview();
			}
			else {
				// simple treeview
				mstaxsync.relationshipSimpleTreeview();
			}

			// values list operations
			mstaxsync.relationshipValues();

			// submit data
			$('#mstaxsync-taxonomies input[name="submit"]').click(function(event) {
				event.preventDefault();
				mstaxsync.rsOnSubmit($(this));
			});

			// nestedSortable list
			mstaxsync.relationshipNestedSortable();

		},

		/**
		 * relationshipAdvancedTreeview
		 *
		 * Initializes relationship advanced treeview
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		relationshipAdvancedTreeview: function() {

			// init treeview
			mstaxsync.rsInitAdvancedTreeview();

			// toggle choice
			$('body').on('click', '.choices-list li .mstaxsync-rel-item', function() {
				mstaxsync.rsOnClickToggle($(this));
			});

			// prevent propagation on checkbox click event
			$('body').on('click', '.choices-list li .mstaxsync-rel-item input[type="checkbox"]', function(event) {
				event.stopPropagation();

				// check tree state
				mstaxsync.rsCheckTreeState($(this).closest(mstaxsync.params.relationship_fields));
			});

			// check all children
			$('body').on('click', '.choices-list li .mstaxsync-rel-item .check-all', function(event) {
				event.stopPropagation();
				mstaxsync.rsOnClickCheckAllChildren($(this));
			});

			// uncheck all children
			$('body').on('click', '.choices-list li .mstaxsync-rel-item .uncheck-all', function(event) {
				event.stopPropagation();
				mstaxsync.rsOnClickUncheckAllChildren($(this));
			});

			// check all tree
			$('body').on('click', '.advanced-treeview-controls .check-all', function(event) {
				mstaxsync.rsOnClickCheckAllTree($(this));
			});

			// uncheck all tree
			$('body').on('click', '.advanced-treeview-controls .uncheck-all', function(event) {
				mstaxsync.rsOnClickUncheckAllTree($(this));
			});

			// add items
			$('body').on('click', '.advanced-treeview-controls .add-items', function() {
				mstaxsync.rsOnClickAddItems($(this));
			});

		},

		/**
		 * relationshipSimpleTreeview
		 *
		 * Initializes relationship simple treeview
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		relationshipSimpleTreeview: function() {

			// add choice
			$('body').on('click', '.choices-list li .mstaxsync-rel-item', function() {
				mstaxsync.rsOnClickAdd($(this));
			});

		},

		/**
		 * relationshipValues
		 *
		 * Relationship values list operations
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		relationshipValues: function() {

			// remove value
			$('body').on('click', '.values-list li .remove', function() {
				mstaxsync.rsOnClickRemove($(this).parent('.mstaxsync-rel-item'));
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

			// delete value
			$('body').on('click', '.values-list li .trash', function() {
				mstaxsync.rsOnClickDelete($(this));
			});

		},

		/**
		 * rsInitAdvancedTreeview
		 *
		 * Checks already synced choices
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		rsInitAdvancedTreeview: function() {

			// vars
			var cbs = $('.mstaxsync-rel-item.disabled input[type="checkbox"]');

			if (cbs.length) {
				cbs.prop({'checked': true, 'disabled': true});
			}

		},

		/**
		 * rsOnClickToggle
		 *
		 * Toggles choices item checkbox
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickToggle: function(el) {

			// vars
			var field = el.closest(mstaxsync.params.relationship_fields),
				cb = el.children('input[type="checkbox"]');

			// can be added?
			if (el.hasClass('disabled')) {
				return false;
			}

			// toggle
			cb.prop('checked', !cb.prop('checked'));

			// check tree state
			mstaxsync.rsCheckTreeState(field);

		},

		/**
		 * rsOnClickCheckAllChildren
		 *
		 * Checks item and all its children
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickCheckAllChildren: function(el) {

			// vars
			var field = el.closest(mstaxsync.params.relationship_fields),
				cbs = el.closest('li').find('input[type="checkbox"]');

			// check
			cbs.prop('checked', true);

			// check tree state
			mstaxsync.rsCheckTreeState(field);

		},

		/**
		 * rsOnClickUncheckAllChildren
		 *
		 * Unchecks item and all its children
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickUncheckAllChildren: function(el) {

			// vars
			var field = el.closest(mstaxsync.params.relationship_fields),
				cbs = el.closest('li').find('input[type="checkbox"]');

			cbs.each(function() {
				var cb = $(this);

				// can be unchecked
				if (!cb.parent('.mstaxsync-rel-item').hasClass('disabled')) {
					// uncheck
					cb.prop('checked', false);
				}
			});

			// check tree state
			mstaxsync.rsCheckTreeState(field);

		},

		/**
		 * rsOnClickCheckAllTree
		 *
		 * Checks all tree
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickCheckAllTree: function(el) {

			// vars
			var field = el.closest('.mstaxsync-taxonomy-terms-box').children(mstaxsync.params.relationship_fields),
				cbs = field.find(mstaxsync.$list('choices')).find('input[type="checkbox"]');

			// check
			if (cbs.length) {
				cbs.prop('checked', true);
			}

			// check tree state
			mstaxsync.rsCheckTreeState(field);

		},

		/**
		 * rsOnClickUncheckAllTree
		 *
		 * Unchecks all tree
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickUncheckAllTree: function(el) {

			// vars
			var field = el.closest('.mstaxsync-taxonomy-terms-box').children(mstaxsync.params.relationship_fields),
				cbs = field.find(mstaxsync.$list('choices')).find('input[type="checkbox"]');

			if (!cbs.length)
				return;

			cbs.each(function() {
				var cb = $(this);

				// can be unchecked
				if (!cb.parent('.mstaxsync-rel-item').hasClass('disabled')) {
					// uncheck
					cb.prop('checked', false);
				}
			});

			// check tree state
			mstaxsync.rsCheckTreeState(field);

		},

		/**
		 * rsCheckTreeState
		 *
		 * Checks tree state and enable/disable Add Items button
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsCheckTreeState: function(el) {

			// vars
			var cbs = el.find(mstaxsync.$list('choices')).find('input[type="checkbox"]'),
				addItemsBtn = el.next('.advanced-treeview-controls').children('.add-items'),
				enable = false;

			if (cbs.length) {
				cbs.each(function() {
					var cb = $(this);

					if (!cb.parent('.mstaxsync-rel-item').hasClass('disabled') && cb.prop('checked')) {
						// enable
						enable = true;
						return false;
					}
				});
			}

			if (enable) {
				// enable
				addItemsBtn.addClass('enabled');
			}
			else {
				// disable
				addItemsBtn.removeClass('enabled');
			}

		},

		/**
		 * rsOnClickAddItems
		 *
		 * Appends choices items to values list
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickAddItems: function(el) {

			if (!el.hasClass('enabled'))
				return;

			// vars
			var field = el.closest('.mstaxsync-taxonomy-terms-box').children(mstaxsync.params.relationship_fields),
				choices = field.find(mstaxsync.$list('choices')).children('li'),
				parents = [],
				items = [];

			// build values items
			if (choices.length) {
				choices.each(function() {
					mstaxsync.rsBuildValuesItems(parents, items, $(this), 0);
				});
			}

			// add
			mstaxsync.rsAddItems(field, items);

		},

		/**
		 * rsBuildValuesItems
		 *
		 * Builds values items in a recursive way
		 *
		 * @since		1.0.0
		 * @param		parents (array)
		 * @param		items (array)
		 * @param		el (jQuery)
		 * @param		parent_id (int)
		 * @return		N/A
		 */
		rsBuildValuesItems: function(parents, items, el, parent_id) {

			// vars
			var span = el.find('.mstaxsync-rel-item').first(),
				id = span.data('id'),
				cb = span.children('input[type="checkbox"]'),
				text = span.children('.val').text(),
				children = el.children('ul').children('li'),
				valid_parent = mstaxsync.rsGetValidParent(parents, parent_id),
				selected_choice = !span.hasClass('disabled') && cb.prop('checked');

			// append to parents
			parents.push({id: id, parent: parent_id, valid: selected_choice});

			if (selected_choice) {
				// append to items
				items.push({id: id, text: text, parent: valid_parent});
			}

			if (children.length) {
				parent_id = id;

				children.each(function() {
					mstaxsync.rsBuildValuesItems(parents, items, $(this), parent_id);
				});
			}

		},

		/**
		 * rsGetValidParent
		 *
		 * Returns valid parent ID before append item to values list
		 *
		 * @since		1.0.0
		 * @param		parents (array)
		 * @param		id (int)
		 * @return		(int)
		 */
		rsGetValidParent: function(parents, id) {

			if (0 == id)
				// return
				return 0;

			// vars
			var parent = $.grep(parents, function(e){ return e.id == id; });

			if (parent.length) {
				// vars
				var parent_id = parent[0].parent,
					valid = parent[0].valid;

				if (valid) {
					// found a valid parent
					return id;
				}
				else {
					// recursive call to find a valid parent ancestor
					return mstaxsync.rsGetValidParent(parents, parent_id);
				}
			}

			// return
			return 0;

		},

		/**
		 * rsAddItems
		 *
		 * Appends choices items to values list
		 *
		 * @since		1.0.0
		 * @param		field (jQuery)
		 * @param		items (array)
		 * @return		N/A
		 */
		rsAddItems: function(field, items) {

			// vars
			var choicesList = field.find(mstaxsync.$list('choices')),
				addItemsBtn = field.next('.advanced-treeview-controls').children('.add-items'),
				html = [];

			// disable
			addItemsBtn.removeClass('enabled');

			if (!items.length) {
				// return
				return;
			}

			$.each(items, function(key, item) {
				// vars
				var id = item.id,
					choice = choicesList.find('li .mstaxsync-rel-item[data-id=' + id + ']'),
					cb = choice.children('input[type="checkbox"]');

				// disable
				choice.addClass('disabled');
				cb.prop({'disabled': true});

				// add
				mstaxsync.rsAddItem(html, item);
			});

			// append
			mstaxsync.rsAppendItems(field, html);

		},

		/**
		 * rsAddItem
		 *
		 * Appends choices item to values list
		 *
		 * @since		1.0.0
		 * @param		html (array)
		 * @param		item (object)
		 * @return		N/A
		 */
		rsAddItem: function(html, item) {

			// vars
			var id = item.id,
				text = item.text,
				parent = item.parent;

			if (0 == parent) {
				html.push({
					id: id,
					text: text,
					children: [],
				});
			}
			else {
				// locate parent item to push object into as a child item
				$.each(html, function(key, value) {
					if (parent == value.id) {
						value.children.push({
							id: id,
							text: text,
							children: [],
						});

						return false;
					}
					else {
						if (value.children && value.children.length) {
							mstaxsync.rsAddItem(value.children, item);
						}
					}
				});
			}

		},

		/**
		 * rsAppendItems
		 *
		 * Appends items html to values list
		 *
		 * @since		1.0.0
		 * @param		field (jQuery)
		 * @param		html (array)
		 * @return		N/A
		 */
		rsAppendItems: function(field, html) {

			if (!html.length)
				return;

			$.each(html, function(key, value) {
				// vars
				var item = mstaxsync.rsNewValue({
					id: value.id,
					text: value.text,
					children: value.children,
				});

				field.find(mstaxsync.$list('values')).append(item);
			});

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
				id: el.data('id'),
				text: el.children('.val').text(),
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
				id = el.data('id'),
				li = el.closest('li'),
				ul = li.children('ul'),
				siblings = li.siblings(),
				choice = field.find(mstaxsync.$list('choices')).find('li .mstaxsync-rel-item[data-id=' + id + ']'),
				advanced_treeview = _mstaxsync.settings.advanced_treeview;

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

			if (advanced_treeview.length) {
				choice.children('input[type="checkbox"]').prop({'checked': false, 'disabled': false});
			}

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
				val = span.children('.val'),
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
				choice = field.find(mstaxsync.$list('choices')).find('li .mstaxsync-rel-item[data-id=' + mainId + ']'),
				advanced_treeview = _mstaxsync.settings.advanced_treeview,
				detach_terms = _mstaxsync.settings.detach_terms;

			// check if detach terms capability is on
			if (!detach_terms || !confirm(_mstaxsync.strings.confirm_detach))
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

					if (advanced_treeview.length) {
						choice.children('input[type="checkbox"]').prop({'checked': false, 'disabled': false});
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					el.removeClass('rotate');
				},
			});

		},

		/**
		 * rsOnClickDelete
		 *
		 * Deletes item
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		rsOnClickDelete: function(el) {

			// vars
			var field = el.closest(mstaxsync.params.relationship_fields),
				nonce = el.data('nonce'),
				span = el.closest('.mstaxsync-rel-item'),
				taxonomy = field.data('name'),
				localId = span.data('id'),
				mainId = span.data('synced'),
				advanced_treeview = _mstaxsync.settings.advanced_treeview,
				delete_terms = _mstaxsync.settings.delete_terms,
				synced = span.children('.synced');

			// check if delete terms capability is on
			if (!delete_terms || !confirm(_mstaxsync.strings.confirm_delete))
				return;

			// activate removing mode
			span.addClass('removing');

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: _mstaxsync.ajaxurl,
				data: {
					action: 'delete_taxonomy_term',
					nonce: nonce,
					taxonomy: taxonomy,
					main_id: mainId,
					local_id: localId,
				},
				success: function(response) {
					// enable choice
					if (mainId) {
						choice = field.find(mstaxsync.$list('choices')).find('li .mstaxsync-rel-item[data-id=' + mainId + ']');
						choice.removeClass('disabled');
						choice.data('synced', '');

						if (advanced_treeview.length) {
							choice.children('input[type="checkbox"]').prop({'checked': false, 'disabled': false});
						}
					}

					// hide
					span.css('opacity', '0');

					// remove
					setTimeout(function() {
						mstaxsync.rsOnClickRemove(span);
					}, 500);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					// deactivate removing mode
					span.removeClass('removing');
				},
			});

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
				taxonomyTerms = mstaxsync.rsGroupTaxonomyTerms(),
				loader = $('.submit .ajax-loading'),
				result = $('.submit').next('.result'),
				msg = [];

			// expose loader
			loader.css('visibility', 'visible');

			// hide result message
			result.html('').hide();

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
					mstaxsync.rsRefreshLists(response);

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
		 * rsGroupTaxonomyTerms
		 *
		 * Groups terms by taxonomy
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		(array)
		 */
		rsGroupTaxonomyTerms: function() {

			// vars
			taxonomyTerms = [];

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

			// return
			return taxonomyTerms;

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
		 * rsRefreshLists
		 *
		 * Refreshes relationship field lists
		 *
		 * @since		1.0.0
		 * @param		response (json)
		 * @return		N/A
		 */
		rsRefreshLists: function(response) {

			// vars
			var advanced_treeview = _mstaxsync.settings.advanced_treeview;

			if (Object.keys(response.rs_fields).length) {
				$.each(response.rs_fields, function(taxonomy, lists) {
					// vars
					var rs_field = $('.mstaxsync-relationship[data-name="' + taxonomy + '"]'),
						choices = lists.choices,
						values = lists.values;

					// refresh choices
					if (choices.length) {
						rs_field.find(mstaxsync.$list('choices')).html(choices);

						if (advanced_treeview.length) {
							mstaxsync.rsInitAdvancedTreeview();
						}
					}

					// refresh values
					if (values.length) {
						rs_field.find(mstaxsync.$list('values')).html(values);
					}
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
				val = span.children('.val'),
				ind = span.children('.ind'),
				edit = span.children('.edit');

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