$(document).ready(function() {
    if (username === '') {
        $('#login').click(function() {
            $('#modalLogin').modal('toggle');
        });
        if (hasErrors) {
            $('#modalLogin').modal('show');
        }
    }
    else {
        $('#login').attr('href', '/logout');
        $('#login').text('Logout ' + username);
    }
});