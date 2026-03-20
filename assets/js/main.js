document.addEventListener('DOMContentLoaded', function () {
    var deleteLinks = document.querySelectorAll('.js-confirm-delete');
    var toggleButtons = document.querySelectorAll('.js-toggle-password');

    deleteLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var confirmed = window.confirm('Are you sure you want to delete this listing?');
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var targetSelector = button.getAttribute('data-target');
            if (!targetSelector) {
                return;
            }

            var input = document.querySelector(targetSelector);
            if (!input) {
                return;
            }

            var nextType = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', nextType);
            button.textContent = nextType === 'password' ? 'Show' : 'Hide';
        });
    });
});
