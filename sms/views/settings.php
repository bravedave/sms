<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

/**
 * replace:
 * [x] data-dismiss => data-bs-dismiss
 * [x] data-toggle => data-bs-toggle
 * [x] data-parent => data-bs-parent
 * [x] text-right => text-end
 * [x] custom-select - form-select
 * [x] mr-* => me-*
 * [x] ml-* => ms-*
 * [x] pr-* => pe-*
 * [x] pl-* => ps-*
 * [x] input-group-prepend - remove
 * [x] input-group-append - remove
 * [x] btn input-group-text => btn btn-light
 */

extract((array)$this->data); ?>

<style>
	.cellcast .js-providor:not(.js-cellcast) {
		display: none;
	}
	.smsbroadcast .js-providor:not(.js-smsbroadcast) {
		display: none;
	}
</style>

<form method="post" action="<?= $this->route ?>" id="<?= $_form = strings::rand() ?>">
	<input type="hidden" name="action" value="save-settings">

	<div class="mb-2">

		<label class="form-label">Country Code:</label>
		<input type="text" class="form-control" name="countrycode" value="<?= $settings->countrycode ?>" required>
	</div>

	<div class="mb-2">

		<label class="form-label">From:</label>
		<input type="text" class="form-control" name="from" value="<?= $settings->fromnumber ?>" required>
	</div>

	<div class="mb-2">

		<label class="form-label">Providor:</label>
		<select class="form-select" name="providor" required>

			<option value="">select providor</option>
			<option value="smsbroadcast" <?= 'smsbroadcast' == $settings->providor ? 'selected' : '' ?>>SMS Broadcast</option>
			<option value="cellcast" <?= 'cellcast' == $settings->providor ? 'selected' : '' ?>>Cellcast</option>
		</select>
	</div>

	<div class="mb-2 js-providor js-smsbroadcast">

		<label class="form-label">Account: </label>
		<input type="text" class="form-control js-smsbroadcast-field" name="account" value="<?= $settings->accountid ?>">
	</div>

	<div class="mb-2 js-providor js-smsbroadcast">

		<label>Password:</label>
		<div class="input-group">

			<input type="password" class="form-control js-smsbroadcast-field" name="password" value="<?= $settings->accountpassword ?>">
			<button type="button" class="btn btn-light js-show-password">
				<i class="bi bi-eye"></i>
			</button>
		</div>
	</div>

	<div class="mb-2 js-providor js-cellcast">

		<label>APPKey:</label>
		<div class="input-group">

			<input type="text" class="form-control js-cellcast-field" name="appkey" value="<?= $settings->appkey ?>">
		</div>
	</div>

	<div class="text-end">

		<button type="submit" class="btn btn-primary">update settings</button>
	</div>
	<script>
		(_ => {
			const form = $('#<?= $_form ?>');

			form.find('.js-show-password').on('click', function(e) {

				let _me = $(this);
				let fld = _me.siblings('input[name="password"]');

				if ('text' == fld.attr('type')) {

					fld.attr('type', 'password');
					_me.find('.bi').removeClass('bi-eye-slash').addClass('bi-eye');
				} else {

					fld.attr('type', 'text');
					_me.find('.bi').removeClass('bi-eye').addClass('bi-eye-slash');
				}
			});

			form.find('select[name="providor"]').on('change', e => form.trigger('set-required'));

			form
				.on('set-required', function(e) {

					let _me = $(this);
					let _data = _me.serializeFormJSON();

					if ('smsbroadcast' == _data.providor) {

						_me.find('.js-smsbroadcast-field').prop('required', true);
						_me.find('.js-cellcast-field').prop('required', false);
						form.removeClass('cellcast').addClass('smsbroadcast');
					} else if ('cellcast' == _data.providor) {

						_me.find('.js-smsbroadcast-field').prop('required', false);
						_me.find('.js-cellcast-field').prop('required', true);
						form.removeClass('smsbroadcast').addClass('cellcast');
					} else {

						_me.find('.js-smsbroadcast-field').prop('required', false);
						_me.find('.js-cellcast-field').prop('required', false);
						form.removeClass('smsbroadcast cellcast');
					}
				});

			form.trigger('set-required');
		})(_brayworth_);
	</script>

</form>