<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/	?>

<ul class="nav flex-column">
	<li class="nav-item d-none" id="<?= $_uid = strings::rand() ?>ni"><a class="nav-link" href="#" id="<?= $_uid ?>">SMS</a></li>
	<li class="nav-item"><a class="nav-link" href="<?= strings::url( $this->route . '/settings') ?>">Settings</a></li>

</ul>
<script>
( _ => $(document).ready( () => {
	_.get.sms.enabled().then( () => {
		console.log( 'sms enabled...');
		$('#<?= $_uid ?>ni').removeClass( 'd-none');

	});

	$('#<?= $_uid ?>').on( 'click', function( e) {
		e.stopPropagation(); e.preventDefault();

		_.get.sms().then( modal => {
			modal.trigger( 'add.recipient', '0418745334');
			$('textarea[name="message"]', modal).focus();

		});

	});

}))( _brayworth_);
</script>
