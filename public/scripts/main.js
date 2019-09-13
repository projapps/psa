$(document).ready(function() {
    if (username === '') {
        $('#login').click(function() {
            $('#modalLogin').modal('toggle');
        });
    }
    else {
        $('#login').attr('href', 'logout_user');
        $('#login').text('Logout ' + username);
    }
});