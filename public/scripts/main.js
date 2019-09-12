$(document).ready(function() {
    if (username === ''){
        $('#login').click(function(){
            $('#formLogin').toggle();
            $('#error').toggle();
        });
    }
    else{
        $('#login').attr('href', 'logout_user');
        $('#login').text('Logout ' + username);
    }
    $('#formLogin').hide();
});