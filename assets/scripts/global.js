'use strict';
(function ( $ ) {

	/**
	 * Get the details for a single user and fill
	 * the data.
	 *
	 * @param userId
	 */
	function inpsydeGetUserDetails ( userId ) {

		let tableWrapper = $( '#inpsyde-table-wrapper' );
		let userDetails  = $( '#inpsyde-user-details' );

		// Send an ajax request
		$.ajax(
			{
				url: inpsyde.rest_url.user_details,
				method: 'GET',
				data: { id: userId, nonce: inpsyde.nonce },
				dataType: 'json',
				beforeSend: function ( jqXHR, settings ) {
					jqXHR.overrideMimeType( "text/plain; charset=x-user-defined" );
					jqXHR.setRequestHeader( 'X-WP-Nonce', inpsyde.nonce );

					// Block the table
					tableWrapper.block( {
						message: '<div class="blockui "><span>' + inpsyde.i18n.processing + '</span><span><div class="spinner spinner--v2 spinner--primary "></div></span></div>',
						css: {
							border: '0px',
							backgroundColor: 'transparent',
							textAlign: 'center',
							width: 'auto',
						},
						overlayCSS: {
							backgroundColor: '#000000',
							opacity: 0.1,
							cursor: 'wait'
						}
					} );
				},
				success: function ( data, textStatus, jqXHR ) {

					// Check if the server's response vas valid
					if ( false === data.success ) {
						swal.fire( inpsyde.i18n.error, data.message, 'error' );
						return;
					}

					// Render the details
					$( '#inpsyde-name' ).text( data.name );
					$( '#inpsyde-user-company' ).text( data.company );
					$( '#inpsyde-user-email' ).text( data.email );
					$( '#inpsyde-user-phone' ).text( data.phone );
					$( '#inpsyde-user-address' ).text( data.location );
					$( '#inpsyde-user-website' ).text( data.website );
					$( '#inpsyde-user-location' ).text( data.city );
					$( '#inpsyde-refresh-details' ).data( 'id', data.ID );
					$( '#inpsyde-user-avatar' ).attr( { src: data.avatar, alt: data.name } );

					// Show the details
					userDetails.fadeIn();

				},
				error: function ( jqXHR, textStatus, errorThrown ) {
					// Show the error
					swal.fire( inpsyde.i18n.error, jqXHR.responseText, 'error' );
					// Hide the results
					userDetails.fadeOut();
				},
				complete: function ( jqXHR, textStatus ) {
					// Unblock the table
					tableWrapper.unblock();
				}
			}
		);
	}

	/**
	 * Initiate and fill the datatable with users' data.
	 *
	 */
	function inpsydeFillDatatable ( reset = false ) {

		// Initialize the datatable and fill the fields
		let table = $( '#inpsyde-table' );

		// If there's need to be a rest
		if ( reset ) {
			table.DataTable().destroy();
		}

		table.DataTable( {
			responsive: true,
			searchDelay: 500,
			processing: true,
			ajax: {
				url: inpsyde.rest_url.users,
				type: 'GET',
				data: {
					pagination: {
						perpage: 10,
					}
				},
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', inpsyde.nonce );
				},
				error: function ( xhr, error, code ) {
					swal.fire( inpsyde.i18n.error, error, 'error' );
				},
				dataSrc: function ( json ) {
					if ( false === json.success ) {
						// Show an alert
						swal.fire( inpsyde.i18n.error, json.message, 'error' );
						return [];
					} else {
						return json.data;
					}
				}
			},
			columns: [
				{ data: 'ID' },
				{ data: 'Name' },
				{ data: 'Username' },
				{ data: 'Email' },
				{ data: 'Phone' },
				{ data: 'Website' },
				{ data: 'Actions', responsivePriority: -1 },
			],
			columnDefs: [
				{
					targets: -1,
					title: 'Actions',
					orderable: false,
					render: function ( data, type, full, meta ) {
						return `
                        <a href="#" data-userid="` + data + `" class="btn btn-label-primary inpsyde-get-user-details">
                          <i class="flaticon-eye"></i>` + inpsyde.i18n.view + `
                        </a>`;
					},
				}
			],
		} );
	}

	// On page load, fill the datatable.
	inpsydeFillDatatable();

	/**
	 * Fetch user's details
	 *
	 */
	$( '#inpsyde-table-wrapper' ).on( 'click', '.inpsyde-get-user-details', function ( e ) {
		e.preventDefault();

		// Get the user's ID
		let userId = $( this ).data( 'userid' );

		// Fetch and fill
		inpsydeGetUserDetails( userId );

	} );

	/**
	 * Flush the user table
	 */
	$( '#inpsyde-flush-users' ).on( 'click', function ( e ) {

		// Prevent any actions
		e.preventDefault();

		let tableWrapper = $( '#inpsyde-table_wrapper' );

		// Send an ajax request
		$.ajax(
			{
				url: inpsyde.rest_url.users_flush,
				method: 'DELETE',
				data: { nonce: inpsyde.nonce },
				dataType: 'json',
				beforeSend: function ( jqXHR, settings ) {

					jqXHR.overrideMimeType( "text/plain; charset=x-user-defined" );
					jqXHR.setRequestHeader( 'X-WP-Nonce', inpsyde.nonce );

					// Block the table
					tableWrapper.block( {
						message: '<div class="blockui "><span>' + inpsyde.i18n.processing + '</span><span><div class="spinner spinner--v2 spinner--primary "></div></span></div>',
						css: {
							border: '0px',
							backgroundColor: 'transparent',
							textAlign: 'center',
							width: 'auto',
						},
						overlayCSS: {
							backgroundColor: '#000000',
							opacity: 0.1,
							cursor: 'wait'
						}
					} );
				},
				success: function ( data, textStatus, jqXHR ) {

					// Check if the server's response vas valid
					if ( false === data.success ) {
						swal.fire( inpsyde.i18n.error, data.message, 'error' );
						return;
					}

					// Fetch a fresh copy
					inpsydeFillDatatable( true );

				},
				error: function ( jqXHR, textStatus, errorThrown ) {
					// Show the error
					swal.fire( inpsyde.i18n.error, jqXHR.responseText, 'error' );
				},
				complete: function ( jqXHR, textStatus ) {
					// Unblock the table
					tableWrapper.unblock();
				}
			}
		);

	} );

	/**
	 * Refresh the user's details
	 *
	 */
	$( '#inpsyde-refresh-details' ).on( 'click', function ( e ) {

		e.preventDefault();
		// Get the user ID
		let userId      = $( this ).data( 'id' );
		let userWrapper = $( '#inpsyde-user-details' );

		// Wrong ID
		if ( !Number.isInteger( userId ) ) {
			swal.fire( inpsyde.i18n.error, inpsyde.i18n.invalid_id, 'error' );
			return;
		}

		// Send an ajax request
		$.ajax(
			{
				url: inpsyde.rest_url.user_flush,
				method: 'DELETE',
				data: { user_id: userId, nonce: inpsyde.nonce },
				dataType: 'json',
				beforeSend: function ( jqXHR, settings ) {
					jqXHR.overrideMimeType( "text/plain; charset=x-user-defined" );
					jqXHR.setRequestHeader( 'X-WP-Nonce', inpsyde.nonce );

					// Block the table
					userWrapper.block( {
						message: '<div class="blockui "><span>' + inpsyde.i18n.processing + '</span><span><div class="spinner spinner--v2 spinner--primary "></div></span></div>',
						css: {
							border: '0px',
							backgroundColor: 'transparent',
							textAlign: 'center',
							width: 'auto',
						},
						overlayCSS: {
							backgroundColor: '#000000',
							opacity: 0.1,
							cursor: 'wait'
						}
					} );
				},
				success: function ( data, textStatus, jqXHR ) {

					// Check if the server's response vas valid
					if ( false === data.success ) {
						swal.fire( inpsyde.i18n.error, data.message, 'error' );
						return;
					}

					// Fetch a fresh copy
					inpsydeGetUserDetails( userId );

				},
				error: function ( jqXHR, textStatus, errorThrown ) {
					// Show the error
					swal.fire( inpsyde.i18n.error, jqXHR.responseText, 'error' );

					// Hide the results
					userWrapper.fadeOut();
				},
				complete: function ( jqXHR, textStatus ) {
					// Unblock the table
					userWrapper.unblock();
				}
			}
		);
	} );
})( jQuery );