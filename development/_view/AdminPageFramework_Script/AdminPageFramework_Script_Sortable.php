<?php
/**
 * Admin Page Framework
 * 
 * http://en.michaeluno.jp/admin-page-framework/
 * Copyright (c) 2013-2014 Michael Uno; Licensed MIT
 * 
 */
if ( ! class_exists( 'AdminPageFramework_Script_Sortable' ) ) :
/**
 * Provides JavaScript scripts for the sortable method.
 * 
 * @since			3.0.0			
 * @package			AdminPageFramework
 * @subpackage		JavaScript
 * @internal
 */
class AdminPageFramework_Script_Sortable {

	static public function getjQueryPlugin() {

		/**
		 * HTML5 Sortable jQuery Plugin
		 * http://farhadi.ir/projects/html5sortable
		 * 
		 * Copyright 2012, Ali Farhadi
		 * Released under the MIT license.
		 */	
		return "(function($) {
			var dragging, placeholders = $();
			$.fn.sortable = function(options) {
				var method = String(options);
				options = $.extend({
					connectWith: false
				}, options);
				return this.each(function() {
					if (/^enable|disable|destroy$/.test(method)) {
						var items = $(this).children($(this).data('items')).attr('draggable', method == 'enable');
						if (method == 'destroy') {
							items.add(this).removeData('connectWith items')
								.off('dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s');
						}
						return;
					}
					var isHandle, index, items = $(this).children(options.items);
					var placeholder = $('<' + (/^ul|ol$/i.test(this.tagName) ? 'li' : 'div') + ' class=\"sortable-placeholder\">');
					items.find(options.handle).mousedown(function() {
						isHandle = true;
					}).mouseup(function() {
						isHandle = false;
					});
					$(this).data('items', options.items)
					placeholders = placeholders.add(placeholder);
					if (options.connectWith) {
						$(options.connectWith).add(this).data('connectWith', options.connectWith);
					}
					items.attr('draggable', 'true').on('dragstart.h5s', function(e) {
						if (options.handle && !isHandle) {
							return false;
						}
						isHandle = false;
						var dt = e.originalEvent.dataTransfer;
						dt.effectAllowed = 'move';
						dt.setData('Text', 'dummy');
						index = (dragging = $(this)).addClass('sortable-dragging').index();
					}).on('dragend.h5s', function() {
						dragging.removeClass('sortable-dragging').show();
						placeholders.detach();
						if (index != dragging.index()) {
							items.parent().trigger('sortupdate', {item: dragging});
						}
						dragging = null;
					}).not('a[href], img').on('selectstart.h5s', function() {
						this.dragDrop && this.dragDrop();
						return false;
					}).end().add([this, placeholder]).on('dragover.h5s dragenter.h5s drop.h5s', function(e) {
						if (!items.is(dragging) && options.connectWith !== $(dragging).parent().data('connectWith')) {
							return true;
						}
						if (e.type == 'drop') {
							e.stopPropagation();
							placeholders.filter(':visible').after(dragging);
							return false;
						}
						e.preventDefault();
						e.originalEvent.dataTransfer.dropEffect = 'move';
						if (items.is(this)) {
							if (options.forcePlaceholderSize) {
								placeholder.height(dragging.outerHeight());
							}
							dragging.hide();
							$(this)[placeholder.index() < $(this).index() ? 'after' : 'before'](placeholder);
							placeholders.not(placeholder).detach();
						} else if (!placeholders.is(this) && !$(this).children(options.items).length) {
							placeholders.detach();
							$(this).append(placeholder);
						}
						return false;
					});
				});
			};
			
			$.fn.enableAPFSortable = function( sFieldsContainerID ) {
				
				var oTarget = typeof sFieldsContainerID === 'string' 
					? $( '#' + sFieldsContainerID + '.sortable' )
					: this;
				
				oTarget.unbind( 'sortupdate' );
				oTarget.sortable(
					{	items: '> div:not( .disabled )', }	// the options for the sortable plugin
				).bind( 'sortupdate', function() {
					
					/* Rename the ids and names */
					var nodeFields = $( this ).children( 'div' );
					var iCount = 1;
					var iMaxCount = nodeFields.length;

					$( $( this ).children( 'div' ).reverse() ).each( function() {	// reverse is needed for radio buttons since they loose the selections when updating the IDs

						var iIndex = ( iMaxCount - iCount );
						$( this ).setIndexIDAttribute( 'id', iIndex );
						$( this ).find( 'label' ).setIndexIDAttribute( 'for', iIndex );
						$( this ).find( 'input,textarea,select' ).setIndexIDAttribute( 'id', iIndex );
						$( this ).find( 'input,textarea,select' ).setIndexNameAttribute( 'name', iIndex );

						/* Radio buttons loose their selections when IDs and names are updated, so reassign them */
						$( this ).find( 'input[type=radio]' ).each( function() {	
							var sAttr = $( this ).prop( 'checked' );
							if ( typeof sAttr !== 'undefined' && sAttr !== false) 
								$( this ).attr( 'checked', 'Checked' );
						});
							
						iCount++;
					});
					
					/* It seems radio buttons need to be taken cared of again. Otherwise, the checked items will be gone. */
					$( this ).find( 'input[type=radio][checked=checked]' ).attr( 'checked', 'Checked' );	
					
					/* Callback the registered functions */
					$( this ).callBackSortedFields( $( this ).data( 'type' ), $( this ).attr( 'id' ) );
					
				}); 				
			
			};
		}( jQuery ));";
		
	}
}
endif;