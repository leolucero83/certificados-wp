jQuery(document).ready(function($) {
    $('#login_form').on('submit', function(e) {
        e.preventDefault(); // Prevenir el envío estándar del formulario

        // Recogemos los valores del formulario
        var username = $('#username').val();
        var password = $('#password').val();
        
        $.ajax({
            url: ajaxurl, // Esta variable viene definida por WordPress y apunta a admin-ajax.php
            type: 'POST',
            data: {
                action: 'ajax_login',  // El nombre del action que se usará en PHP
                username: username,
                password: password
            },
            success: function(response) {
                if (response.success) {
                    // Si el login es exitoso, redirigimos
                    window.location.href = response.redirect_url;
                } else {
                    // Si hay un error, mostramos el mensaje
                    $('#login_error').text(response.message);
                }
            }
        });
    });
});
