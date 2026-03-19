document.addEventListener('DOMContentLoaded', function () {
    var deleteLinks = document.querySelectorAll('.js-confirm-delete');

    deleteLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var confirmed = window.confirm('Are you sure you want to delete this listing?');
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
});
