<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

extract((array)($this->data ?? []));	?>

<nav class="nav flex-column" id="<?= $_nav = strings::rand() ?>">

	<button type="button" class="nav-link text-start d-none js-sms-button" href="#">SMS</button>
	<a class="nav-link" href="<?= strings::url($this->route . '/settings') ?>">Settings</a>
</nav>
<script>
	(_ => {
		const nav = $('#<?= $_nav ?>');

		_.ready(() => {

			_.get.sms.enabled()
				.then(() => {

					console.log('sms enabled...');
					nav.find('.js-sms-button').removeClass('d-none');
				});

			nav.find('.js-sms-button')
				.on('click', function(e) {
					e.stopPropagation();

					_.get.sms()
						.then(modal => {

							modal.trigger('add.recipient', '0418745334');
							modal.find('textarea[name="message"]').focus();
						});
				});
		});
	})(_brayworth_);
</script>