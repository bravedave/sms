<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * usage:
 *

	( _ => {
		_.get.modal( _.url('sms/dialog'))
		.then( modal => {
			$('form', modal).trigger( 'add.recipient', '041..');
			$('textarea[name="message"]', modal).focus();

		});

	})( _brayworth_);

*/	?>
<form id="<?= $_form = strings::rand(); ?>">
	<input type="hidden" name="action" value="send-sms">

	<div class="form-row mb-2">
		<div class="col">

			<!-- label for="<?= $msgID = strings::rand(); ?>">Message</label -->
			<textarea class="form-control" name="message" placeholder="message" required
				id="<?= $msgID ?>"
				data-maxlength="<?= $this->_handler->max() ?>"
				rows="<?= userAgent::isMobileDevice() ? 6 : 9 ?>"></textarea>

		</div>

	</div>

	<div class="form-row">
		<div class="col-9 pr-0">
			<div class="input-group">
				<div class="form-control text-muted font-italic" id="<?= $logID = strings::rand(); ?>"></div>
				<div class="input-group-append">
					<div class="input-group-text">
						Credit: <?= $this->_handler->balance(); ?>

					</div>

				</div>

			</div>

		</div>

		<div class="col-3 text-right">
			<button class="btn btn-primary"><i class="fa fa-paper-plane-o"></i></button>

		</div>

	</div>

</form>

<script>
( _ => {

	let replaceWordCharacters = ( text) => {
		/**
			* Replaces commonly-used Windows 1252 encoded chars
			* that do not exist in ASCII or ISO-8859-1 with
			* ISO-8859-1 cognates.
		*/

		let s = text;
		s = s.replace(/[\u2018|\u2019|\u201A]/g, "\'");	// smart single quotes and apostrophe
		s = s.replace(/[\u201C|\u201D|\u201E]/g, "\"");	// smart double quotes
		s = s.replace(/\u2026/g, "...");	// ellipsis
		s = s.replace(/[\u2013|\u2014]/g, "-");	// dashes
		s = s.replace(/\u02C6/g, "^");	// circumflex
		s = s.replace(/\u2039/g, "<");	// open angle bracket
		s = s.replace(/\u203A/g, ">");	// close angle bracket
		s = s.replace(/[\u02DC|\u00A0]/g, " ");	// spaces

		return s;

	};

	let change = function( e) {
		let _me = $(this);
		let tel = String( _me.val());

		if ( tel.IsMobilePhone()) {
			_.post({
				url : _.url('<?= $this->route ?>'),
				data : {
					action : 'get-by-phone',
					tel : tel
				}

			})
			.then( d => {
				if ( 'ack' == d.response) {
					let ig = $('<div class="input-group-append"></div>');
					$('<div class="input-group-text"></div>').html( d.data.name).appendTo( ig);

					ig.insertAfter( _me);

					_me
					.prop( 'readonly', true)
					.removeClass( 'bg-warning');

				}
				else {
					_me.addClass( 'bg-warning');

				}

			});

		}
		else {
			_me.addClass( 'bg-warning');

		}

	};

	let newField = before => {
		let row = $('<div class="form-row mb-2"></div>');

		if ( !!before) {
			row.insertBefore( before.closest('.form-row'));

		}
		else {
			row.insertBefore( $('#<?= $msgID ?>'));

		}

		let col = $('<div class="col"></div>').appendTo( row);
		let ig = $('<div class="input-group"></div>').appendTo( col);
		let input = $('<input class="form-control" type="text" placeholder="0418 .." autocomplete="tel" name="to[]">').appendTo( ig);

		$('<div class="input-group-append"></div>').appendTo(ig);

		fixBtns();

		input
		.on( 'change', change)
		.autofill({
      source : ( request, response) => {
        console.log( request.term);
        _.post({
          url : _.url('<?= $this->route ?>'),
          data : {
            action : 'search-person',
            term : request.term

          },

        })
				.then( d => {
					if ( 'ack' == d.response) {
						return d.data.map( e => {
							return {
								label : e.label + ' ' + e.mobile,
								value : e.mobile,
								name : e.label,

							}

						});

					}

					return [];

				})
        .then( data => response( data));

      }

    });

		return ( input);

	};

	let fixBtns = () => {
		let flds = $('input[name="to\[\]"]');

		flds.each( ( i, el) => {
			let p = $(el).parent();
			let appendix = $('.input-group-append', p).last();

			if ( i < flds.length - 1) {
				let a = $('<a href="#" class="input-group-text"><i class="fa fa-minus fa-fw"></i></a>').on( 'click', e => {
					e.stopPropagation(); e.preventDefault();
					p.closest('.form-row').remove();

				});
				appendix.html('').append( a);

			}
			else {
				let a = $('<a href="#" class="input-group-text"><i class="fa fa-plus fa-fw"></i></a>').on( 'click', e => {
					e.stopPropagation(); e.preventDefault();
					$('#<?= $_form ?>').trigger('new-field');

				});
				appendix.html('').append( a);

			}

		});

	};

	$('#<?= $_form ?>')
	.on( 'add.recipient', ( e, mobile) => {
		newField( $('input[name="to\[\]"]').last())
		.val( mobile)
		.trigger('change');

	})
	.on( 'new-field', function( e) {
		newField();

	})
	.on( 'submit', function() {
		let frm = $(this);
		let data = frm.serializeFormJSON();

		_.post({
			url : _.url('<?= $this->route ?>'),
			data : data,

		})
		.then( d => {
			_.growl( d);
			if ( 'ack' == d.response) {
				frm.closest('.modal').trigger('brayworth.success');

			}
			frm.closest('.modal').modal('hide');

		});

		return ( false);

	});

	$('#<?= $msgID ?>')
	.on( 'paste', function( e) {
		setTimeout(() => {
			// Do something immediately after the paste event
			let _ta = $(this);
			let val = _ta.val();
			let v = replaceWordCharacters( val);
			if ( v != val) {
				_ta.val( v);

			}

			_ta.trigger('keyup.sms');

		});

	})
	.on( 'keyup.sms', function( e ) {
		let m = $(this).val();
		let len = m.length;
		let max = $(this).data('maxlength');
		if ( len > max ) {
			$(this).val( m.substring( 0, max ));

		}

		let s = len + '/' + max;
		if ( len > 160) {
			s += ' (' + String( Math.ceil(len / 153)) + ' credits)';
			$('#<?= $logID ?>').addClass('text-danger');

		}
		else {
			$('#<?= $logID ?>').removeClass('text-danger');

		}

		$('#<?= $logID ?>').html(s);

	});

	$('#<?= $_form ?>').trigger('new-field');

}) (_brayworth_);

$(document).ready( () => {
	$('#<?= $msgID ?>').trigger( 'keyup.sms');

});	// ready
</script>
