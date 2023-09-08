<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * usage:
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
/*
	( _ => {
		_.get.modal( _.url('sms/dialog'))
		.then( modal => {
			modal.trigger( 'add.recipient', '041..');
			$('textarea[name="message"]', modal).focus();
		});
	})( _brayworth_);
*/

extract((array)$this->data); ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">

	<input type="hidden" name="action" value="send-sms">

	<div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">

		<div class="modal-dialog modal-dialog-centered" role="document">

			<div class="modal-content">

				<div class="modal-header <?= theme::modalHeader() ?>">

					<h5 class="modal-title" id="<?= $_modal ?>Label"><?= $title ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body">

					<div class="row">

						<div class="col mb-2">

							<textarea class="form-control" name="message" placeholder="message" required id="<?= $msgID = strings::rand(); ?>" data-maxlength="<?= $this->_handler->max() ?>" rows="<?= userAgent::isMobileDevice() ? 6 : 9 ?>"></textarea>
						</div>
					</div>
				</div>

				<div class="modal-footer">

					<div class="flex-fill">

						<div class="input-group">

							<div class="form-control text-muted font-italic" id="<?= $logID = strings::rand(); ?>"></div>
							<div class="input-group-text">

								Credit: <?= $this->_handler->balance(); ?>
							</div>
						</div>
					</div>

					<button type="button" class="btn btn-light js-add-recipient">
						<i class="bi bi-person-plus"></i>
					</button>

					<button class="btn btn-primary"><i class="bi bi-send"></i></button>
				</div>
			</div>
		</div>
	</div>

	<script>
		(_ => {
			const form = $('#<?= $_form ?>');
			const modal = $('#<?= $_modal ?>');
			const msg = $('#<?= $msgID ?>');
			const fldLog = $('#<?= $logID ?>');

			const replaceWordCharacters = (text) => {

				/**
				 * Replaces commonly-used Windows 1252 encoded chars
				 * that do not exist in ASCII or ISO-8859-1 with
				 * ISO-8859-1 cognates.
				 */

				let s = text;
				s = s.replace(/[\u2018|\u2019|\u201A]/g, "\'"); // smart single quotes and apostrophe
				s = s.replace(/[\u201C|\u201D|\u201E]/g, "\""); // smart double quotes
				s = s.replace(/\u2026/g, "..."); // ellipsis
				s = s.replace(/[\u2013|\u2014]/g, "-"); // dashes
				s = s.replace(/\u02C6/g, "^"); // circumflex
				s = s.replace(/\u2039/g, "<"); // open angle bracket
				s = s.replace(/\u203A/g, ">"); // close angle bracket
				s = s.replace(/[\u02DC|\u00A0]/g, " "); // spaces

				return s;
			};

			const newField = before => {
				let row = $(`<div class="row">
						<div class="col mb-2">
							<div class="input-group">
								<input class="form-control" type="text" placeholder="0418 .." autocomplete="tel" name="to[]">
							</div>
						</div>
					</div>`);

				if (!!before) {

					if (before.length > 0) {

						row.insertAfter(before.closest('.row'));
					} else {

						modal.find('.modal-body').prepend(row);
					}
				} else {

					modal.find('.modal-body').prepend(row);
				}

				let input = row.find('input');

				fixBtns();

				input
					.on('input', function(e) {
						let _me = $(this);
						let tel = String(_me.val());

						if (tel.IsMobilePhone()) $(this).trigger('change')
					})
					.on('change', function(e) {

						let _me = $(this);
						let tel = String(_me.val());

						if (tel.IsMobilePhone()) {

							_.fetch
								.post(_.url('<?= $this->route ?>'), {
									action: 'get-people-by-phone',
									tel: tel
								})
								.then(d => {

									if ('ack' == d.response) {

										$('<div class="input-group-text"></div>')
											.html(d.data.name)
											.insertAfter(_me);

										_me
											.prop('readonly', true)
											.removeClass('bg-warning')
											.autofill('destroy');
									} else {

										_me.addClass('bg-warning');
									}

									fixBtns();
								});
						} else {

							_me.addClass('bg-warning');
						}
					})
					.autofill({
						select: (e, item) => input.trigger('change'),
						source: (request, response) => {

							// console.log(request.term);

							_.fetch.post(_.url('<?= $this->route ?>'), {
									action: 'search-person',
									term: request.term
								})
								.then(d => {

									if ('ack' == d.response) {

										return d.data.map(e => {
											return {
												label: e.label + ' ' + e.mobile,
												value: e.mobile,
												name: e.label,
											}
										});
									}

									return [];
								})
								.then(data => response(data));
						}
					});

				return input;
			};

			const fixBtns = () => {
				let flds = $('input[name="to\[\]"]');

				flds.each((i, el) => {

					let _me = $(el);
					let ig = _me.closest('.input-group');

					ig.find('.js-action').remove();

					if (_me.hasClass('bg-warning')) {

						$(`<button type="button" class="btn btn-light js-action">
								<i class="bi bi-person-plus"></i>
							</button>`)
							.on('click', e => {

								_.hideContexts();
								e.stopPropagation();
								_.get.modal(_.url('people/edit'))
									.then(m => {

										m.find('input[name="mobile"]').val(_me.val());

										m.on('success', (e, d) => {

											if ('ack' == d.response) {

												_.fetch
													.post(_.url('<?= $this->route ?>'), {
														action: 'get-people-by-id',
														id: d.id
													})
													.then(d => {

														if ('ack' == d.response) {

															if (String(d.data.mobile).IsMobilePhone()) {

																$('<div class="input-group-text"></div>')
																	.html(d.data.name)
																	.insertAfter(_me);

																_me
																	.val(d.data.mobile)
																	.prop('readonly', true)
																	.removeClass('bg-warning');
															} else {

																_me.addClass('bg-warning');
															}
														} else {

															_me.addClass('bg-warning');
														}

														fixBtns();
													});
											}
										});
									});
							})
							.appendTo(ig);
					}

					$(`<button type="button" class="btn btn-light js-action">
								<i class="bi bi-person-dash"></i>
							</button>`)
						.on('click', function(e) {

							_.hideContexts();
							e.stopPropagation();
							$(this).closest('.row').remove();
						})
						.appendTo(ig);
				});
			};

			form.find('.js-add-recipient')
				.on('click', e => {

					_.hideContexts(e);
					e.stopPropagation();
					form.trigger('new-field');
				});

			form
				.on('add.recipient', (e, mobile) => {

					e.stopPropagation();

					newField($('input[name="to\[\]"]').last())
						.val(mobile)
						.trigger('change');
				})
				.on('new-field', e => newField())
				.on('submit', function() {

					_.fetch.post
						.form(_.url('<?= $this->route ?>'), this)
						.then(d => {

							_.growl(d);
							if ('ack' == d.response) {

								modal.trigger('success')
									.trigger('brayworth.success');
							}
							modal.modal('hide');
						});

					return (false);
				});

			msg
				.on('paste', function(e) {

					setTimeout(() => {

						// Do something immediately after the paste event
						let _ta = $(this);
						let val = _ta.val();
						let v = replaceWordCharacters(val);
						if (v != val) _ta.val(v);
						_ta.trigger('keyup.sms');
					}, 50);
				})
				.on('keyup.sms', function(e) {
					let m = $(this).val();
					let len = m.length;
					let max = $(this).data('maxlength');

					if (len > max) $(this).val(m.substring(0, max));

					let s = len + '/' + max;
					if (len > 160) {

						s += ' (' + String(Math.ceil(len / 153)) + ' credits)';
						fldLog.addClass('text-danger');
					} else {

						fldLog.removeClass('text-danger');
					}

					fldLog.html(s);
				});

			modal
				.on('add.recipient', (e, mobile) => {

					e.stopPropagation();
					form.trigger('add.recipient', mobile);
				})
				.on('shown.bs.modal', e => {

					newField().focus();
					msg.trigger('keyup.sms');
				});
		})(_brayworth_);
	</script>
</form>