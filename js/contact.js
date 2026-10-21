$(function () {
    const $form = $('#contact-form');
    const $button = $('#submit-btn');
    const $buttonText = $('#submit-text');
    const $feedback = $('#form-message');

    if (!$form.length) {
        return;
    }

    $form.on('submit', function (event) {
        event.preventDefault();

        const form = this;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        $feedback
            .addClass('d-none')
            .removeClass('alert-success alert-danger');

        $button.prop('disabled', true);
        $buttonText.text('Sending...');

        $.ajax({
            url: 'send-message.php',
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            timeout: 20000
        })
        .done(function (response) {
            $feedback
                .removeClass('d-none alert-danger')
                .addClass('alert-success')
                .text(response.message || 'Message submitted.');

            if (response.success) {
                form.reset();
            } else {
                $feedback
                    .removeClass('alert-success')
                    .addClass('alert-danger');
            }
        })
        .fail(function (xhr, status) {
            let message = 'Unable to send your message. Please try again.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (status === 'timeout') {
                message = 'The request timed out. Please try again.';
            }

            $feedback
                .removeClass('d-none alert-success')
                .addClass('alert-danger')
                .text(message);
        })
        .always(function () {
            $button.prop('disabled', false);
            $buttonText.text('Send Message');
        });
    });
});