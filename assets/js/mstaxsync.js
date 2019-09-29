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

			// return
			return [
			'<li class="new">',
				'<div>',
					'<input type="hidden" name="' + mstaxsync.rsGetInputName(props.field) + '[]" value="' + props.id + '" />',
					'<span class="mstaxsync-rel-item" data-id="' + props.id + '">',
						'<span class="val">' + props.text + '</span>',
						'<input type="text" placeholder="' + props.text + '" />',
						'<span class="ind">(' + _mstaxsync.relationship_new_item_str + ')</span>',
						'<span class="edit dashicons dashicons-edit"></span>',
						'<span class="ok dashicons dashicons-yes"></span>',
						'<span class="cancel dashicons dashicons-no"></span>',
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

			if (keycode == '13') {
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
				ind = span.children('span.ind'),
				edit = span.children('span.edit');

			el.addClass('changed');

			if (!ind.length) {
				$( '<span class="ind">(' + _mstaxsync.relationship_changed_item_str + ')</span>' ).insertBefore(edit);
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

			$('.values-list').nestedSortable({
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