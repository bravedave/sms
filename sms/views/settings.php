<?php
/*
	David Bray
	BrayWorth Pty Ltd
	e. david@brayworth.com.au

	This work is licensed under a Creative Commons Attribution 4.0 International Public License.
		http://creativecommons.org/licenses/by/4.0/
	*/
	$settings = $this->data->settings;
	?>
<form method="post" action="/">
	<input type="hidden" name="action" value="save-settings" />

	<div class="form-group row">
		<div class="col">
			<label>Country Code:</label>
			<input type="text" class="form-control" name="countrycode" value="<?php if ( $settings) print $settings->countrycode; ?>" required />

		</div>

	</div>

	<div class="form-group row">
		<div class="col">
			<label>From:</label>
			<input type="text" class="form-control" name="from" value="<?php if ( $settings) print $settings->from; ?>" required />

		</div>

	</div>

	<div class="form-group row">
		<div class="col">
			<label>Providor:</label>
			<select class="form-control" name="providor" required>
				<option value="">select providor</option>
				<option value="smsbroadcast" <?php if ( $settings && 'smsbroadcast' == $settings->providor ) print 'selected' ?>>SMS Broadcast</option>

			</select>

		</div>

	</div>

	<div class="form-group row">
		<div class="col">
			<label>Account: </label>
			<input type="text" class="form-control" name="account" value="<?php if ( $settings) print $settings->account; ?>" required />

		</div>

	</div>

	<div class="form-group row">
		<div class="col">
			<label>Password:</label>
			<div class="input-group">
				<input type="password" class="form-control" name="password" value="<?php if ( $settings) print $settings->password; ?>" required />

				<div class="input-group-append" id="<?= $uid = strings::rand() ?>">
					<div class="input-group-text">
						<i class="fa fa-eye"></i>

					</div>

				</div>

			</div>

			<script>
			$(document).ready( function() {
				$('#<?= $uid ?>').on( 'click', function( e) {
					let _me = $(this);
					let fld = $('input[name="password"]', _me.closest('.input-group'));

					if ( 'text' == fld.attr( 'type')) {
						fld.attr( 'type', 'password');
						$('.fa-eye-slash', _me).removeClass('fa-eye-slash').addClass('fa-eye');

					}
					else {
						fld.attr( 'type', 'text');
						$('.fa-eye', _me).removeClass('fa-eye').addClass('fa-eye-slash');

					}

				})

			});
			</script>

		</div>

	</div>

	<div class="row">
		<div class="col text-right">
			<button class="btn btn-primary">update settings</button>

		</div>

	</div>

</form>