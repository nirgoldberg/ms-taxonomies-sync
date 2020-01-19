/**
 * MSTaxSync JS functions
 *
 * @author		Nir Goldberg
 * @package		js
 * @version		1.0.0
 */
var $ = jQuery,
	mstaxsync = (function() {

		var self = {};

		/**
		 * params
		 */
		var params = {

			relationship_fields:	$('.mstaxsync-relationship'),
			single_post_meta_box:	$('#mstaxsync_single_post_broadcast'),
			rtl:					$('html').attr('dir') && 'rtl' == $('html').attr('dir'),
			totalImported:			{},	// total posts imported successfully

		};

		/**
		 * $list
		 *
		 * Returns list jQuery element
		 *
		 * @since		1.0.0
		 * @param		list (string)
		 * @return		(jQuery)
		 */
		var $list = function(list) {

			// return
			return $('.' + list + '-list');

		};

		/**
		 * rsGetInputName
		 *
		 * @since		1.0.0
		 * @param		field (jQuery)
		 * @return		(string)
		 */
		var rsGetInputName = function(field) {

			// return
			return field.data('name');

		};

		/**
		 * rsNewValue
		 *
		 * @since		1.0.0
		 * @param		props (array)
		 * @return		(string)
		 */
		var rsNewValue = function(props) {

			// vars
			var edit_terms = _mstaxsync.settings.edit_terms,
				children = [];

			if (props.children && props.children.length) {
				$.each(props.children, function(key, value) {
					children.push(
						rsNewValue({
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

		};

		/**
		 * init
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		self.init = function() {

			// relationship fields
			relationship();

			// broadcast
			broadcast();

			// import
			postsImport();

		};

		/**
		 * relationship
		 *
		 * Relationship fields
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var relationship = function() {

			if (!params.relationship_fields.length)
				return;

			// vars
			var advanced_treeview = _mstaxsync.settings.advanced_treeview;

			if (advanced_treeview.length) {
				// advanced treeview
				relationshipAdvancedTreeview();
			}
			else {
				// simple treeview
				relationshipSimpleTreeview();
			}

			// values list operations
			relationshipValues();

			// submit data
			$('#mstaxsync-taxonomies input[name="submit"]').click(function(event) {
				event.preventDefault();
				rsOnSubmit($(this));
			});

			// nestedSortable list
			relationshipNestedSortable();

		};

		/**
		 * relationshipAdvancedTreeview
		 *
		 * Initializes relationship advanced treeview
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var relationshipAdvancedTreeview = function() {

			// init treeview
			rsInitAdvancedTreeview();

			// toggle choice
			$('body').on('click', '.choices-list li .mstaxsync-rel-item', function() {
				rsOnClickToggle($(this));
			});

			// prevent propagation on checkbox click event
			$('body').on('click', '.choices-list li .mstaxsync-rel-item input[type="checkbox"]', function(event) {
				event.stopPropagation();

				// check tree state
				rsCheckTreeState($(this).closest(params.relationship_fields));
			});

			// check all children
			$('body').on('click', '.choices-list li .mstaxsync-rel-item .check-all', function(event) {
				event.stopPropagation();
				rsOnClickCheckAllChildren($(this));
			});

			// uncheck all children
			$('body').on('click', '.choices-list li .mstaxsync-rel-item .uncheck-all', function(event) {
				event.stopPropagation();
				rsOnClickUncheckAllChildren($(this));
			});

			// check all tree
			$('body').on('click', '.advanced-treeview-controls .check-all', function(event) {
				rsOnClickCheckAllTree($(this));
			});

			// uncheck all tree
			$('body').on('click', '.advanced-treeview-controls .uncheck-all', function(event) {
				rsOnClickUncheckAllTree($(this));
			});

			// add items
			$('body').on('click', '.advanced-treeview-controls .add-items', function() {
				rsOnClickAddItems($(this));
			});

		};

		/**
		 * relationshipSimpleTreeview
		 *
		 * Initializes relationship simple treeview
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var relationshipSimpleTreeview = function() {

			// add choice
			$('body').on('click', '.choices-list li .mstaxsync-rel-item', function() {
				rsOnClickAdd($(this));
			});

		};

		/**
		 * relationshipValues
		 *
		 * Relationship values list operations
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var relationshipValues = function() {

			// remove value
			$('body').on('click', '.values-list li .remove', function() {
				rsOnClickRemove($(this).parent('.mstaxsync-rel-item'));
			});

			// edit value
			$('body').on('click', '.values-list li .edit', function() {
				rsOnClickEdit($(this));
			});

			// submit edit value
			$('body').on('click', '.values-list li .ok', function() {
				rsOnClickSubmitEdit($(this));
			});

			// Enter keypress as submit edit value
			$('body').on('keypress', '.values-list li input', function(event) {
				rsOnKeypressSubmitEdit(event);
			});

			// cancel edit value
			$('body').on('click', '.values-list li .cancel', function() {
				rsOnClickCancelEdit($(this));
			});

			// detach value
			$('body').on('click', '.values-list li .synced', function() {
				rsOnClickDetach($(this));
			});

			// delete value
			$('body').on('click', '.values-list li .trash', function() {
				rsOnClickDelete($(this));
			});

		};

		/**
		 * rsInitAdvancedTreeview
		 *
		 * Checks already synced choices
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var rsInitAdvancedTreeview = function() {

			// vars
			var cbs = $('.mstaxsync-rel-item.disabled input[type="checkbox"]');

			if (cbs.length) {
				cbs.prop({'checked': true, 'disabled': true});
			}

		};

		/**
		 * rsOnClickToggle
		 *
		 * Toggles choices item checkbox
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickToggle = function(el) {

			// vars
			var field = el.closest(params.relationship_fields),
				cb = el.children('input[type="checkbox"]');

			// can be added?
			if (el.hasClass('disabled')) {
				return false;
			}

			// toggle
			cb.prop('checked', !cb.prop('checked'));

			// check tree state
			rsCheckTreeState(field);

		};

		/**
		 * rsOnClickCheckAllChildren
		 *
		 * Checks item and all its children
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickCheckAllChildren = function(el) {

			// vars
			var field = el.closest(params.relationship_fields),
				cbs = el.closest('li').find('input[type="checkbox"]');

			// check
			cbs.prop('checked', true);

			// check tree state
			rsCheckTreeState(field);

		};

		/**
		 * rsOnClickUncheckAllChildren
		 *
		 * Unchecks item and all its children
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickUncheckAllChildren = function(el) {

			// vars
			var field = el.closest(params.relationship_fields),
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
			rsCheckTreeState(field);

		};

		/**
		 * rsOnClickCheckAllTree
		 *
		 * Checks all tree
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickCheckAllTree = function(el) {

			// vars
			var field = el.closest('.mstaxsync-taxonomy-terms-box').children(params.relationship_fields),
				cbs = field.find($list('choices')).find('input[type="checkbox"]');

			// check
			if (cbs.length) {
				cbs.prop('checked', true);
			}

			// check tree state
			rsCheckTreeState(field);

		};

		/**
		 * rsOnClickUncheckAllTree
		 *
		 * Unchecks all tree
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickUncheckAllTree = function(el) {

			// vars
			var field = el.closest('.mstaxsync-taxonomy-terms-box').children(params.relationship_fields),
				cbs = field.find($list('choices')).find('input[type="checkbox"]');

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
			rsCheckTreeState(field);

		};

		/**
		 * rsCheckTreeState
		 *
		 * Checks tree state and enable/disable Add Items button
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsCheckTreeState = function(el) {

			// vars
			var cbs = el.find($list('choices')).find('input[type="checkbox"]'),
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

		};

		/**
		 * rsOnClickAddItems
		 *
		 * Appends choices items to values list
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickAddItems = function(el) {

			if (!el.hasClass('enabled'))
				return;

			// vars
			var field = el.closest('.mstaxsync-taxonomy-terms-box').children(params.relationship_fields),
				choices = field.find($list('choices')).children('li'),
				parents = [],
				items = [];

			// build values items
			if (choices.length) {
				choices.each(function() {
					rsBuildValuesItems(parents, items, $(this), 0);
				});
			}

			// add
			rsAddItems(field, items);

		};

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
		var rsBuildValuesItems = function(parents, items, el, parent_id) {

			// vars
			var span = el.find('.mstaxsync-rel-item').first(),
				id = span.data('id'),
				cb = span.children('input[type="checkbox"]'),
				text = span.children('.val').text(),
				children = el.children('ul').children('li'),
				valid_parent = rsGetValidParent(parents, parent_id),
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
					rsBuildValuesItems(parents, items, $(this), parent_id);
				});
			}

		};

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
		var rsGetValidParent = function(parents, id) {

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
					return rsGetValidParent(parents, parent_id);
				}
			}

			// return
			return 0;

		};

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
		var rsAddItems = function(field, items) {

			// vars
			var choicesList = field.find($list('choices')),
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
				rsAddItem(html, item);
			});

			// append
			rsAppendItems(field, html);

		};

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
		var rsAddItem = function(html, item) {

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
							rsAddItem(value.children, item);
						}
					}
				});
			}

		};

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
		var rsAppendItems = function(field, html) {

			if (!html.length)
				return;

			$.each(html, function(key, value) {
				// vars
				var item = rsNewValue({
					id: value.id,
					text: value.text,
					children: value.children,
				});

				field.find($list('values')).append(item);
			});

		};

		/**
		 * rsOnClickAdd
		 *
		 * Appends choices item to values list
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickAdd = function(el) {

			// vars
			var field = el.closest(params.relationship_fields);

			// can be added?
			if (el.hasClass('disabled')) {
				return false;
			}

			// disable
			el.addClass('disabled');

			// add
			var html = rsNewValue({
				id: el.data('id'),
				text: el.children('.val').text(),
			});

			field.find($list('values')).append(html);

		};

		/**
		 * rsOnClickRemove
		 *
		 * Removes item from values list
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickRemove = function(el) {

			// vars
			var field = el.closest(params.relationship_fields),
				id = el.data('id'),
				li = el.closest('li'),
				ul = li.children('ul'),
				siblings = li.siblings(),
				choice = field.find($list('choices')).find('li .mstaxsync-rel-item[data-id=' + id + ']'),
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

		};

		/**
		 * rsOnClickEdit
		 *
		 * Edits item name
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickEdit = function(el) {

			// vars
			var span = el.closest('.mstaxsync-rel-item'),
				input = span.children('input');

			// activate editing mode
			span.addClass('editing');

			// focus input
			input.focus();

		};

		/**
		 * rsOnClickSubmitEdit
		 *
		 * Submits item new name
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickSubmitEdit = function(el) {

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
				rsIndicateChanged(span.closest('li'));
			}

			// deactivate editing mode
			span.removeClass('editing');

		};

		/**
		 * rsOnKeypressSubmitEdit
		 *
		 * Submits item new name
		 *
		 * @since		1.0.0
		 * @param		event
		 * @return		N/A
		 */
		var rsOnKeypressSubmitEdit = function(event) {

			// vars
			var keycode = (event.keyCode ? event.keyCode : event.which);

			if ('13' == keycode) {
				rsOnClickSubmitEdit($(event.target));
			}

			event.stopPropagation();

		};

		/**
		 * rsOnClickCancelEdit
		 *
		 * Cancels Edit and reverts to item old name
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickCancelEdit = function(el) {

			// vars
			var span = el.closest('.mstaxsync-rel-item'),
				input = span.children('input');

			// reset input
			input.val('');

			// deactivate editing mode
			span.removeClass('editing');

		};

		/**
		 * rsOnClickDetach
		 *
		 * Detaches item
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickDetach = function(el) {

			// vars
			var field = el.closest(params.relationship_fields),
				nonce = el.data('nonce'),
				span = el.closest('.mstaxsync-rel-item'),
				localId = span.data('id'),
				mainId = span.data('synced'),
				choice = field.find($list('choices')).find('li .mstaxsync-rel-item[data-id=' + mainId + ']'),
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

		};

		/**
		 * rsOnClickDelete
		 *
		 * Deletes item
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnClickDelete = function(el) {

			// vars
			var field = el.closest(params.relationship_fields),
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
						choice = field.find($list('choices')).find('li .mstaxsync-rel-item[data-id=' + mainId + ']');
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
						rsOnClickRemove(span);
					}, 500);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					// deactivate removing mode
					span.removeClass('removing');
				},
			});

		};

		/**
		 * rsOnSubmit
		 *
		 * Submits relationship fields data
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsOnSubmit = function(el) {

			// vars
			var nonce = el.data('nonce'),
				taxonomyTerms = rsGroupTaxonomyTerms(),
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
					rsRefreshLists(response);

					// build result message
					rsBuildResultMsg(response, msg);
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

		};

		/**
		 * rsGroupTaxonomyTerms
		 *
		 * Groups terms by taxonomy
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		(array)
		 */
		var rsGroupTaxonomyTerms = function() {

			// vars
			taxonomyTerms = [];

			$.each(params.relationship_fields, function() {
				var values = $(this).find($list('values')).children('li'),
					terms = [];

				if (values.length) {
					values.each(function() {
						rsBuildTaxonomyTerms(terms, $(this), 0);
					});

					taxonomyTerms.push({
						taxonomy: $(this).data('name'),
						terms: terms,
					});
				}
			});

			// return
			return taxonomyTerms;

		};

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
		var rsBuildTaxonomyTerms = function(terms, el, parent_id) {

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
					rsBuildTaxonomyTerms(terms, $(this), parent_id);
				});
			}

		};

		/**
		 * rsRefreshLists
		 *
		 * Refreshes relationship field lists
		 *
		 * @since		1.0.0
		 * @param		response (json)
		 * @return		N/A
		 */
		var rsRefreshLists = function(response) {

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
						rs_field.find($list('choices')).html(choices);

						if (advanced_treeview.length) {
							rsInitAdvancedTreeview();
						}
					}

					// refresh values
					if (values.length) {
						rs_field.find($list('values')).html(values);
					}
				});
			}

		};

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
		var rsBuildResultMsg = function(response, msg) {

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

		};

		/**
		 * rsIndicateChanged
		 *
		 * Adds indication for changed item
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var rsIndicateChanged = function(el) {

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

		};

		/**
		 * relationshipNestedSortable
		 *
		 * Initializes relationship nestedSortable
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var relationshipNestedSortable = function() {

			$list('values').nestedSortable({
				listType: 'ul',
				items: 'li',
				handle: 'div',
				toleranceElement: '> div',
				opacity: .6,
				revert: 250,
				rtl: params.rtl,
				relocate: function(event, ui){
					rsIndicateChanged(ui.item);
				},
			});

		};

		/**
		 * broadcast
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var broadcast = function() {

			// single post broadcast
			broadcastSinglePost();

			// quick edit
			broadcastQuickEdit();

			// bulk edit
			broadcastBulkEdit();

		};

		/**
		 * broadcastSinglePost
		 *
		 * Single post broadcast operations
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var broadcastSinglePost = function() {

			if (!params.single_post_meta_box.length)
				return;

			// check all
			params.single_post_meta_box.find('.check-all').click(function() {
				broadcastOnClickCheckAllSites($(this));
			});

			// uncheck all
			params.single_post_meta_box.find('.uncheck-all').click(function() {
				broadcastOnClickUncheckAllSites($(this));
			});

		};

		/**
		 * broadcastOnClickCheckAllSites
		 *
		 * Checks all sites
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var broadcastOnClickCheckAllSites = function(el) {

			// vars
			var meta_box = el.closest(params.single_post_meta_box),
				cbs = meta_box.find('.synced-sites input.unsynced-post');

			// check
			cbs.prop('checked', true);

		};

		/**
		 * broadcastOnClickUncheckAllSites
		 *
		 * Unchecks all sites
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var broadcastOnClickUncheckAllSites = function(el) {

			// vars
			var meta_box = el.closest(params.single_post_meta_box),
				cbs = meta_box.find('.synced-sites input.unsynced-post');

			// check
			cbs.prop('checked', false);

		};

		/**
		 * broadcastQuickEdit
		 *
		 * Quick edit broadcast operations
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var broadcastQuickEdit = function() {

			if (typeof inlineEditPost == 'undefined')
				return;

			// copy of the inline edit function
			var wpInlineEditFunction = inlineEditPost.edit;

			// overwrite inline edit function
			inlineEditPost.edit = function(post_id) {

				// merge arguments of the original function
				wpInlineEditFunction.apply(this, arguments);

				// get post ID from the argument
				var id = 0;

				if (typeof(post_id) == 'object') {
					id = parseInt(this.getId(post_id));
				}

				if (id > 0) {

					// populate input fields
					QuickEditPopulateInputs(id);

				}
			}

		};

		/**
		 * QuickEditPopulateInputs
		 *
		 * Quick edit populate input fields
		 *
		 * @since		1.0.0
		 * @param		id (int)
		 * @return		N/A
		 */
		var QuickEditPopulateInputs = function(id) {

			// vars
			var postEditRow = $('#edit-' + id),
				postRow = $('#post-' + id),
				syncedSites = postRow.find('.column-mstaxsync_synced li'),
				destSites = postEditRow.find('.mstaxsync-broadcast-checklist li'),
				syncedSitesArr = [];

			if (syncedSites.length && destSites.length) {
				// build synced sites array
				syncedSites.each(function() {
					syncedSitesArr.push($(this).attr('id'));
				});

				// populate input fields with column data
				destSites.each(function() {
					var site = $(this).attr('id'),
						checked = false,
						disabled = false;

					// check if dest site exists in synced sites array
					if ($.inArray(site, syncedSitesArr) > -1) {
						checked = true;
						disabled = true;
					}

					// populate input field
					$(this).find('input').prop({'checked': checked, 'disabled': disabled});
				});
			}

		};

		/**
		 * broadcastBulkEdit
		 *
		 * Bulk edit broadcast operations
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var broadcastBulkEdit = function() {

			$('body').on('click', 'input[name="bulk_edit"]', function() {

				// add the WordPress default spinner just before the button
				$(this).after('<span class="spinner is-active"></span>');

				// bulk edit table row
				var nonce = $('#mstaxsync_quick_edit_post_broadcast').val(),
					bulkEditRow = $('tr#bulk-edit'),
					post_ids = [],
					destSites = bulkEditRow.find('.mstaxsync-broadcast-checklist li'),
					destSitesArr = [];

				// obtain the post IDs selected for bulk edit
				bulkEditRow.find('#bulk-titles').children().each(function() {
					post_ids.push($(this).attr('id').replace(/^(ttle)/i,''));
				});

				if (!post_ids.length)
					return;

				// build dest sites array
				destSites.each(function() {
					// is checked
					checked = $(this).find('input').attr('checked');

					if (checked) {
						id = $(this).attr('id').replace(/^(site-)/i,'');
						destSitesArr.push(id);
					}
				});

				// save data
				$.ajax({
					type: 'post',
					url: _mstaxsync.ajaxurl,
					async: false,
					cache: false,
					data: {
						action: 'bulk_broadcast',
						nonce: nonce,
						post_ids: post_ids,
						dest_sites: destSitesArr,
					},
				});
			});

		};

		/**
		 * postsImport
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		var postsImport = function() {

			// import
			$('body').on('click', '.mstaxsync-import', function() {
				onClickImport($(this));
			});

		};

		/**
		 * onClickImport
		 *
		 * Imports all taxonomy term posts
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		var onClickImport = function(el) {

			// vars
			var row = el.parent(),
				nonce = el.data('nonce'),
				mainTermId = el.data('id'),
				mainTermCount = el.data('term-count'),
				import_posts = _mstaxsync.settings.import_posts,
				confirm_import = mainTermCount > 1 ? _mstaxsync.strings.confirm_import.replace('%s', mainTermCount) : _mstaxsync.strings.confirm_single_import;

			// check if import posts capability is on
			if (row.hasClass('done') || row.hasClass('active') || !import_posts || !confirm(confirm_import))
				return;

			// expose loader
			row.addClass('active');

			// init totalImported
			params.totalImported[mainTermId] = 0;

			// import
			initImport(row, mainTermId, nonce);

		};

		/**
		 * initImport
		 *
		 * Initializes import process
		 *
		 * @since		1.0.0
		 * @param		row (jQuery)
		 * @param		mainTermId (int) Main site term ID
		 * @param		nonce (string)
		 * @return		N/A
		 */
		var initImport = function(row, mainTermId, nonce) {

			if (!row || !mainTermId || !nonce)
				return;

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: _mstaxsync.ajaxurl,
				data: {
					action: 'prepare_taxonomy_term_posts_import',
					nonce: nonce,
					main_term_id: mainTermId,
				},
				success: function(response) {
					row.children('.mstaxsync-import-result').html(_mstaxsync.strings.success_import + 0);

					if (response.posts && response.posts.length) {
						importPosts(row, mainTermId, response.posts, nonce);
					}
					else {
						row.addClass('done');

						// hide loader
						row.removeClass('active');
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					row.children('.mstaxsync-import-result').html(_mstaxsync.strings.failed_import);

					// hide loader
					row.removeClass('active');
				},
			});

		};

		/**
		 * importPosts
		 *
		 * Imports all taxonomy term posts
		 *
		 * @since		1.0.0
		 * @param		row (jQuery)
		 * @param		mainTermId (int)
		 * @param		posts (json)
		 * @param		nonce (string)
		 * @return		N/A
		 */
		var importPosts = function(row, mainTermId, posts, nonce) {

			if (posts && posts.length) {
				$.each(posts, function(i, post) {
					importPost(row, mainTermId, post, i, posts.length, nonce);
				});
			}

		};

		/**
		 * importPost
		 *
		 * Imports a single post
		 *
		 * @since		1.0.0
		 * @param		row (jQuery)
		 * @param		mainTermId (int)
		 * @param		post (json)
		 * @param		index (int)
		 * @param		totalToImport (int)
		 * @param		nonce (string)
		 * @return		N/A
		 */
		var importPost = function(row, mainTermId, post, index, totalToImport, nonce) {

			if (!post)
				return;

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: _mstaxsync.ajaxurl,
				data: {
					action: 'import_post',
					nonce: nonce,
					post: post,
				},
				success: function(response) {
					if (response.length) {
						// update totalImported
						params.totalImported[mainTermId]++;
						row.children('.mstaxsync-import-result').html(_mstaxsync.strings.success_import + params.totalImported[mainTermId]);
					}
				},
				complete: function(jqXHR, textStatus) {
					if (index+1 == totalToImport) {
						row.addClass('done');

						// hide loader
						row.removeClass('active');
					}
				},
			});

		};

		// return
		return self;

	}

());

mstaxsync.init();