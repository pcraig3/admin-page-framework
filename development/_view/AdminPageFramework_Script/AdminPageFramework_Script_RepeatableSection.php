<?php
/**
 * Admin Page Framework
 * 
 * http://en.michaeluno.jp/admin-page-framework/
 * Copyright (c) 2013-2014 Michael Uno; Licensed MIT
 * 
 */
if ( ! class_exists( 'AdminPageFramework_Script_RepeatableSection' ) ) :
/**
 * Provides JavaScript utility scripts.
 * 
 * @since			3.0.0			
 * @package			AdminPageFramework
 * @subpackage		JavaScript
 * @internal
 */
class AdminPageFramework_Script_RepeatableSection {

	static public function getjQueryPlugin( $sCannotAddMore, $sCannotRemoveMore ) {

		return "( function( $ ) {

			$.fn.updateAPFRepeatableSections = function( aSettings ) {
				
				var nodeThis = this;	// it can be from a sections container or a cloned section container.
				var sSectionsContainerID = nodeThis.find( '.repeatable-section-add' ).first().closest( '.admin-page-framework-sectionset' ).attr( 'id' );

				/* Store the sections specific options in an array  */
				if ( ! $.fn.aAPFRepeatableSectionsOptions ) $.fn.aAPFRepeatableSectionsOptions = [];
				if ( ! $.fn.aAPFRepeatableSectionsOptions.hasOwnProperty( sSectionsContainerID ) ) {		
					$.fn.aAPFRepeatableSectionsOptions[ sSectionsContainerID ] = $.extend({	
						max: 0,	// These are the defaults.
						min: 0,
						}, aSettings );
				}
				var aOptions = $.fn.aAPFRepeatableSectionsOptions[ sSectionsContainerID ];

				/* The Add button behavior - if the tag id is given, multiple buttons will be selected. 
				 * Otherwise, a section node is given and single button will be selected. */
				$( nodeThis ).find( '.repeatable-section-add' ).click( function() {
					$( this ).addAPFRepeatableSection();
					return false;	// will not click after that
				});
				
				/* The Remove button behavior */
				$( nodeThis ).find( '.repeatable-section-remove' ).click( function() {
					$( this ).removeAPFRepeatableSection();
					return false;	// will not click after that
				});		
				
				/* If the number of sections is less than the set minimum value, add sections. */
				var sSectionID = nodeThis.find( '.repeatable-section-add' ).first().closest( '.admin-page-framework-section' ).attr( 'id' );
				var nCurrentSectionCount = jQuery( '#' + sSectionsContainerID ).find( '.admin-page-framework-section' ).length;
				if ( aOptions['min'] > 0 && nCurrentSectionCount > 0 ) {
					if ( ( aOptions['min'] - nCurrentSectionCount ) > 0 ) {					
						$( '#' + sSectionID ).addAPFRepeatableSection( sSectionID );				 
					}
				}
				
			};
			
			/**
			 * Adds a repeatable section.
			 */
			$.fn.addAPFRepeatableSection = function( sSectionContainerID ) {
				if ( typeof sSectionContainerID === 'undefined' ) {
					var sSectionContainerID = $( this ).closest( '.admin-page-framework-section' ).attr( 'id' );	
				}

				var nodeSectionContainer = $( '#' + sSectionContainerID );
				var nodeNewSection = nodeSectionContainer.clone();	// clone without bind events.
				var nodeSectionsContainer = nodeSectionContainer.closest( '.admin-page-framework-sectionset' );
				var sSectionsContainerID = nodeSectionsContainer.attr( 'id' );
				var nodeTabsContainer = $( '#' + sSectionContainerID ).closest( '.admin-page-framework-sectionset' ).find( '.admin-page-framework-section-tabs' );
				
				/* If the set maximum number of sections already exists, do not add */
				var sMaxNumberOfSections = $.fn.aAPFRepeatableSectionsOptions[ sSectionsContainerID ]['max'];
				if ( sMaxNumberOfSections != 0 && nodeSectionsContainer.find( '.admin-page-framework-section' ).length >= sMaxNumberOfSections ) {
					var nodeLastRepeaterButtons = nodeSectionContainer.find( '.admin-page-framework-repeatable-section-buttons' ).last();
					var sMessage = $( this ).formatPrintText( '{$sCannotAddMore}', sMaxNumberOfSections );
					var nodeMessage = $( '<span class=\"repeatable-section-error\" id=\"repeatable-section-error-' + sSectionsContainerID + '\" style=\"float:right;color:red;margin-left:1em;\">' + sMessage + '</span>' );
					if ( nodeSectionsContainer.find( '#repeatable-section-error-' + sSectionsContainerID ).length > 0 )
						nodeSectionsContainer.find( '#repeatable-section-error-' + sSectionsContainerID ).replaceWith( nodeMessage );
					else
						nodeLastRepeaterButtons.before( nodeMessage );
					nodeMessage.delay( 2000 ).fadeOut( 1000 );
					return;		
				}
				
				nodeNewSection.find( 'input:not([type=radio], [type=checkbox], [type=submit], [type=hidden]),textarea' ).val( '' );	// empty the value		
				nodeNewSection.find( '.repeatable-section-error' ).remove();	// remove error messages.
				
				/* If this is not for tabbed sections, do not show the title */
				var sSectionTabSlug = nodeNewSection.find( '.admin-page-framework-section-caption' ).first().attr( 'data-section_tab' );
				if ( ! sSectionTabSlug || sSectionTabSlug === '_default' ) {
					nodeNewSection.find( '.admin-page-framework-section-title' ).hide();
				}
								
				/* Add the cloned new field element */
				nodeNewSection.insertAfter( nodeSectionContainer );	

				/* It seems radio buttons of the original field need to be reassigned. Otherwise, the checked items will be gone. */
				nodeSectionContainer.find( 'input[type=radio][checked=checked]' ).attr( 'checked', 'Checked' );	
				
				/* Iterate each section and increment the names and ids of the next following siblings. */
				nodeSectionContainer.nextAll().each( function() {
					
					incrementAttributes( this );
					
					/* Iterate each field one by one */
					$( this ).find( '.admin-page-framework-field' ).each( function() {	
					
						/* Rebind the click event to the repeatable field buttons - important to update AFTER inserting the clone to the document node since the update method need to count fields. */
						$( this ).updateAPFRepeatableFields();
													
						/* Call the registered callback functions */
						$( this ).callBackAddRepeatableField( $( this ).data( 'type' ), $( this ).attr( 'id' ), 1 );
						
					});					
					
				});
			
				/* Rebind the click event to the repeatable sections buttons - important to update AFTER inserting the clone to the document node since the update method need to count sections. 
				 * Also do this after updating the attributes since the script needs to check the last added id for repeatable section options such as 'min'
				 * */
				nodeNewSection.updateAPFRepeatableSections();	
				
				/* Rebind sortable fields - iterate sortable fields containers */
				nodeNewSection.find( '.admin-page-framework-fields.sortable' ).each( function() {
					$( this ).enableAPFSortable();
				});
				
				/* For tabbed sections - add the title tab list */
				if ( nodeTabsContainer.length > 0 ) {
					
					/* The clicked(copy source) section tab */
					var nodeTab = nodeTabsContainer.find( '#section_tab-' + sSectionContainerID );
					var nodeNewTab = nodeTab.clone();
					
					nodeNewTab.removeClass( 'active' );
					nodeNewTab.find( 'input:not([type=radio], [type=checkbox], [type=submit], [type=hidden]),textarea' ).val( '' );	// empty the value
				
					/* Add the cloned new field tab */
					nodeNewTab.insertAfter( nodeTab );	
					
					/* Increment the names and ids of the next following siblings. */
					nodeTab.nextAll().each( function() {
						incrementAttributes( this );
						$( this ).find( 'a.anchor' ).incrementIDAttribute( 'href' );
					});					
					
					nodeTabsContainer.closest( '.admin-page-framework-section-tabs-contents' ).createTabs( 'refresh' );
				}				
				
				/* If more than one sections are created, show the Remove button */
				var nodeRemoveButtons =  nodeSectionsContainer.find( '.repeatable-section-remove' );
				if ( nodeRemoveButtons.length > 1 ) nodeRemoveButtons.show();				
									
				/* Return the newly created element */
				return nodeNewSection;	
				
			};	
			// Local function literal
			var incrementAttributes = function( oElement, bFirstFound ) {
				
				bFirstFound = typeof bFirstFound !== 'undefined' ? bFirstFound : true;
				$( oElement ).incrementIDAttribute( 'id', bFirstFound );	// passing true in the second parameter means to apply the change to the first occurrence.
				$( oElement ).find( 'tr.admin-page-framework-fieldrow' ).incrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( '.admin-page-framework-fieldset' ).incrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( '.admin-page-framework-fieldset' ).incrementIDAttribute( 'data-field_id', bFirstFound );	// I don't remember what this data attribute was for...
				$( oElement ).find( '.admin-page-framework-fields' ).incrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( '.admin-page-framework-field' ).incrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( 'table.form-table' ).incrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( '.repeatable-field-add' ).incrementIDAttribute( 'data-id', bFirstFound );	// holds the fields container ID referred by the repeater field script.
				$( oElement ).find( 'label' ).incrementIDAttribute( 'for', bFirstFound );	
				$( oElement ).find( 'input,textarea,select' ).incrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( 'input,textarea,select' ).incrementNameAttribute( 'name', bFirstFound );				
				
			}			
				
			$.fn.removeAPFRepeatableSection = function() {
				
				/* Need to remove the element: the secitons container */
				var nodeSectionContainer = $( this ).closest( '.admin-page-framework-section' );
				var sSectionConteinrID = nodeSectionContainer.attr( 'id' );
				var nodeSectionsContainer = $( this ).closest( '.admin-page-framework-sectionset' );
				var sSectionsContainerID = nodeSectionsContainer.attr( 'id' );
				var nodeTabsContainer = nodeSectionsContainer.find( '.admin-page-framework-section-tabs' );
				var nodeTabs = nodeTabsContainer.find( '.admin-page-framework-section-tab' );
				
				/* If the set minimum number of sections already exists, do not remove */
				var sMinNumberOfSections = $.fn.aAPFRepeatableSectionsOptions[ sSectionsContainerID ]['min'];
				if ( sMinNumberOfSections != 0 && nodeSectionsContainer.find( '.admin-page-framework-section' ).length <= sMinNumberOfSections ) {
					var nodeLastRepeaterButtons = nodeSectionContainer.find( '.admin-page-framework-repeatable-section-buttons' ).last();
					var sMessage = $( this ).formatPrintText( '{$sCannotRemoveMore}', sMinNumberOfSections );
					var nodeMessage = $( '<span class=\"repeatable-section-error\" id=\"repeatable-section-error-' + sSectionsContainerID + '\" style=\"float:right;color:red;margin-left:1em;\">' + sMessage + '</span>' );
					if ( nodeSectionsContainer.find( '#repeatable-section-error-' + sSectionsContainerID ).length > 0 )
						nodeSectionsContainer.find( '#repeatable-section-error-' + sSectionsContainerID ).replaceWith( nodeMessage );
					else
						nodeLastRepeaterButtons.before( nodeMessage );
					nodeMessage.delay( 2000 ).fadeOut( 1000 );
					return;		
				}				
				
				/* Decrement the names and ids of the next following siblings. */
				nodeSectionContainer.nextAll().each( function() {
					
					decrementAttributes( this );
					
					/* Call the registered callback functions */
					$( this ).find( '.admin-page-framework-field' ).each( function() {	
						$( this ).callBackRemoveRepeatableField( $( this ).data( 'type' ), $( this ).attr( 'id' ), 1 );
					});					
					
				});
			
				/* Remove the field */
				nodeSectionContainer.remove();
				
				/* For tabbed sections - remove the title tab list */
				if ( nodeTabsContainer.length > 0 && nodeTabs.length > 1 ) {
					nodeSelectionTab = nodeTabsContainer.find( '#section_tab-' + sSectionConteinrID );
					nodeSelectionTab.nextAll().each( function() {
						$( this ).find( 'a.anchor' ).decrementIDAttribute( 'href' );
						decrementAttributes( this );
					});	
					
					if (  nodeSelectionTab.prev().length )
						nodeSelectionTab.prev().addClass( 'active' );
					else
						nodeSelectionTab.next().addClass( 'active' );
						
					nodeSelectionTab.remove();
					nodeTabsContainer.closest( '.admin-page-framework-section-tabs-contents' ).createTabs( 'refresh' );
				}						
				
				/* Count the remaining Remove buttons and if it is one, disable the visibility of it */
				var nodeRemoveButtons = nodeSectionsContainer.find( '.repeatable-section-remove' );
				if ( nodeRemoveButtons.length == 1 ) {
					
					nodeRemoveButtons.css( 'display', 'none' );
					
					/* Also if this is not for tabbed sections, do show the title */
					var sSectionTabSlug = nodeSectionsContainer.find( '.admin-page-framework-section-caption' ).first().attr( 'data-section_tab' );
					if ( ! sSectionTabSlug || sSectionTabSlug === '_default' ) 
						nodeSectionsContainer.find( '.admin-page-framework-section-title' ).first().show();
					
				}
					
			};
			// Local function literal
			var decrementAttributes = function( oElement, bFirstFound ) {
				
				bFirstFound = typeof bFirstFound !== 'undefined' ? bFirstFound : true;
				$( oElement ).decrementIDAttribute( 'id' );					
				$( oElement ).find( 'tr.admin-page-framework-fieldrow' ).decrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( '.admin-page-framework-fieldset' ).decrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( '.admin-page-framework-fieldset' ).decrementIDAttribute( 'data-field_id', bFirstFound );	// I don't remember what this data attribute was for...
				$( oElement ).find( '.admin-page-framework-fields' ).decrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( '.admin-page-framework-field' ).decrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( 'table.form-table' ).decrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( '.repeatable-field-add' ).decrementIDAttribute( 'data-id', bFirstFound );	// holds the fields container ID referred by the repeater field script.
				$( oElement ).find( 'label' ).decrementIDAttribute( 'for', bFirstFound );
				$( oElement ).find( 'input,textarea,select' ).decrementIDAttribute( 'id', bFirstFound );
				$( oElement ).find( 'input,textarea,select' ).decrementNameAttribute( 'name', bFirstFound );				
				
			}	
			
		}( jQuery ));";		
	}
}
endif;