<?php
/*
	David Bray
	BrayWorth Pty Ltd
	e. david@brayworth.com.au

	This work is licensed under a Creative Commons Attribution 4.0 International Public License.
		http://creativecommons.org/licenses/by/4.0/

	usage:
		_brayworth_.loadModal({
			url : _brayworth_.url('<?php url::write('/dialog') ?>'),

		})
		.then( function( modal) {
			$('form', modal).trigger( 'add.recipient', '041..');

			$('textarea[name="message"]', modal).focus();

		});


	*/	?>
<form id="<?= $frmID = strings::rand(); ?>">
	<input type="hidden" name="action" value="send-sms" />
	<div class="form-row">
		<div class="col">
			<div class="input-group">
				<input class="form-control" type="text" placeholder="0418 .."
					autocomplete="tel" name="to[]" id="<?= $toID = strings::rand(); ?>" autofocus required />

				<div class="input-group-append">
					<a href="#add-recipient" class="input-group-text">
						<i class="fa fa-plus"></i>

					</a>

				</div>

			</div>

		</div>

	</div>

	<div class="row form-group mt-2">
		<div class="col">

			<!-- label for="<?= $msgID = strings::rand(); ?>">Message</label -->
			<textarea class="form-control" name="message" placeholder="message" required
				id="<?= $msgID; ?>"
				data-maxlength="<?= $this->_handler->max() ?>"
				rows="<?= userAgent::isMobileDevice() ? 6 : 9 ?>"></textarea>

		</div>

	</div>

	<div class="row form-group">
		<div class="col-9 pr-0">
			<div class="input-group">
				<input type="text" class="form-control bg-white" id="<?= $logID = strings::rand(); ?>" readonly />
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
$(document).ready( function(){
	let replaceWordCharacters = function( text) {
		// Replaces commonly-used Windows 1252 encoded chars that do not exist in ASCII or ISO-8859-1 with ISO-8859-1 cognates.
		let s = text;
		// smart single quotes and apostrophe
		s = s.replace(/[\u2018|\u2019|\u201A]/g, "\'");
		// smart double quotes
		s = s.replace(/[\u201C|\u201D|\u201E]/g, "\"");
		// ellipsis
		s = s.replace(/\u2026/g, "...");
		// dashes
		s = s.replace(/[\u2013|\u2014]/g, "-");
		// circumflex
		s = s.replace(/\u02C6/g, "^");
		// open angle bracket
		s = s.replace(/\u2039/g, "<");
		// close angle bracket
		s = s.replace(/\u203A/g, ">");
		// spaces
		s = s.replace(/[\u02DC|\u00A0]/g, " ");

		return s;

	};

	$('#<?= $frmID ?>').on( 'submit', function() {
		let frm = $(this);

		let data = frm.serializeFormJSON();
		console.log( data);

		_brayworth_.post({
			url : '<?php url::write() ?>',
			data : data,

		})
		.then( function( d) {
			_brayworth_.growl( d);
			if ( 'ack' == d.response) {
				frm.closest('.modal').trigger('brayworth.success');

			}
			frm.closest('.modal').modal('hide');

		});

		return ( false);

	});

	let change = function() {
		let _me = $(this);
		let tel = '' + _me.val();

		if ( tel.IsMobilePhone()) {
			let ig = $('<div class="input-group-append" />');
			$('<div class="input-group-text" />').html( tel).appendTo( ig);

			ig.insertAfter( _me);

			_me.prop( 'readonly', true);
			$('#<?= $toID ?>').prop('required', $('input[name="to[]"]', '#<?= $frmID ?>').length < 2);

		};

	}

	$('#<?= $msgID ?>')
	.on( 'paste', function( e) {
		//~ console.log( e);
		//~ let paste = (e.originalEvent.clipboardData || window.clipboardData).getData('text');

		//~ console.log( paste);

		window.setTimeout(() => {
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

		$('#<?= $logID ?>').val(s);

	});

	$('#<?= $msgID ?>').trigger( 'keyup.sms');

	let newField = function( before) {
		let lastFld = $('input[name="to[]"]', '#<?= $frmID ?>').last();

		let row = $('<div class="form-row" />');
		!!before ? row.insertBefore( lastFld.closest('.form-row')) : row.insertAfter( lastFld.closest('.form-row'));

		let col = $('<div class="col" />').appendTo( row);
		let ig = $('<div class="input-group" />').appendTo( col);
		let input = $('<input class="form-control" type="text" placeholder="0418 .." autocomplete="tel" name="to[]" />').appendTo( ig);

		let a = $('<a href="#" class="input-group-text">-</a>').on( 'click', function( e) {
			e.stopPropagation(); e.preventDefault();
			row.remove();

			$('#<?= $toID ?>').prop('required', $('input[name="to[]"]', '#<?= $frmID ?>').length < 2);

		});

		$('<div class="input-group-append" />').appendTo(ig).append( a);

		return ( input);

	}

	$('a[href="#add-recipient"]').on( 'click', function( e) {
		e.stopPropagation(); e.preventDefault();
		newField();

	});

	$('#<?= $frmID ?>').on( 'add.recipient', function( e, mobile) {
		newField( true).val( mobile).trigger('change');

	});

});
</script>
