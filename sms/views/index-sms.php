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
	<li class="nav-item"><a class="nav-link" href="#" id="<?= $uid = strings::rand() ?>">SMS</a></li>
	<li class="nav-item"><a class="nav-link" href="<?= strings::url( $this->route . '/settings') ?>">Settings</a></li>

</ul>
<script>
$(document).ready( () => {( _ => {
	$('#<?= $uid ?>').on( 'click', function( e) {
		e.stopPropagation(); e.preventDefault();

		_.get.modal( _.url( '<?= $this->route ?>/dialog'))
		.then( modal => {
			modal.trigger( 'add.recipient', '0418745334');
			$('textarea[name="message"]', modal).focus();

		});

	});

})( _brayworth_) });	// ready
</script>
