<?php
/*
	David Bray
	BrayWorth Pty Ltd
	e. david@brayworth.com.au

	This work is licensed under a Creative Commons Attribution 4.0 International Public License.
		http://creativecommons.org/licenses/by/4.0/

	*/	?>
<div class="row">
	<div class="col pt-4">
		<h6>Index</h6>

		<ul class="list-unstyled mt-4">
			<li><a href="#" id="<?= $uid = strings::rand() ?>">SMS</a></li>
			<li><a href="<?= strings::url( sprintf( '%s/settings', $this->route)) ?>">Settings</a></li>

		</ul>

	</div>

</div>
<script>
$(document).ready( function() {
	$('#<?= $uid ?>').on( 'click', function( e) {
		e.stopPropagation(); e.preventDefault();

			//~ headerClass : '',
			//~ beforeOpen : function() {},
			//~ onClose : function() {},
			//~ onSuccess : function() { /* trigger( 'brayworth.success') */},

		_brayworth_.loadModal({
			url : '<?= strings::url( sprintf( '%s/dialog', $this->route)) ?>',

		})
		.then( function( modal) {
			$('form', modal).trigger( 'add.recipient', '0418745334');

			$('textarea[name="message"]', modal).focus();

		});

	});

});
</script>
