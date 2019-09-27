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
		 * getInputName
		 *
		 * @since		1.0.0
		 * @param		field (jQuery)
		 * @return		(string)
		 */
		getInputName: function(field) {

			// return
			return field.data('name');

		},

		/**
		 * newValue
		 *
		 * @since		1.0.0
		 * @param		props (array)
		 * @return		(string)
		 */
		newValue: function(props) {

			// return
			return [
			'<li>',
				'<div>',
					'<input type="hidden" name="' + mstaxsync.getInputName(props.field) + '[]" value="' + props.id + '" />',
					'<span data-id="' + props.id + '" class="mstaxsync-rel-item">' + props.text,
						'<a href="#" class="mstaxsync-icon" data-name="remove_item"></a>',
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
		 * relationship fields
		 *
		 * @since		1.0.0
		 * @param		N/A
		 * @return		N/A
		 */
		relationship: function() {

			if (!mstaxsync.params.relationship_fields.length)
				return;

			// add choice
			$('.choices-list li span').click(function() {
				mstaxsync.onClickAdd($(this));
			});

			// remove value
			$('body').on('click', '.values-list li .mstaxsync-icon', function() {
				mstaxsync.onClickRemove($(this));
			});

			// nestedSortable list
			$('.values-list').nestedSortable({
				listType: 'ul',
				items: 'li',
				handle: 'div',
				toleranceElement: '> div',
				opacity: .6,
				revert: 250,
				rtl: mstaxsync.params.rtl,
			});

		},

		/**
		 * onClickAdd
		 *
		 * Append choices item to values list
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		onClickAdd: function(el) {

			// vars
			var field = el.closest(mstaxsync.params.relationship_fields);

			// can be added?
			if (el.hasClass('disabled')) {
				return false;
			}

			// disable
			el.addClass('disabled');

			// add
			var html = mstaxsync.newValue({
				field: field,
				id: el.data('id'),
				text: el.html(),
			});

			field.find(mstaxsync.$list('values')).append(html);

		},

		/**
		 * onClickRemove
		 *
		 * Remove item from values list
		 *
		 * @since		1.0.0
		 * @param		el (jQuery)
		 * @return		N/A
		 */
		onClickRemove: function(el) {

			// vars
			var field = el.closest(mstaxsync.params.relationship_fields),
				id = el.parent().data('id'),
				li = el.closest('li'),
				ul = li.children('ul'),
				siblings = li.siblings(),
				choice = field.find(mstaxsync.$list('choices')).find('li span[data-id=' + id + ']');

			// unwrap li parent ul if does not have siblings and does not have chidren
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

	};

$(mstaxsync.init);