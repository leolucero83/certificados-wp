<?php

// ShortCode para mostrar el Formulario de Login Clientes *************************************************************

function ajax_login_enqueue_scripts() {
    // Registrar y encolar el script de login
    wp_enqueue_script('ajax-login-script', plugin_dir_url(__FILE__) . 'ajax-login.js', array('jquery'));

     // Registrar y encolar el estilo CSS
     wp_enqueue_style('ajax-login-style', plugin_dir_url(__FILE__) . 'ajax-login.css');

    // Pasar la variable ajaxurl al script de JavaScript
    wp_localize_script('ajax-login-script', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'ajax_login_enqueue_scripts');

// Agregar el shortcode para el formulario de login
function ajax_login_form_shortcode() {
    ob_start(); ?>
    <form id="login_form">
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Iniciar sesión</button>
        
        <p id="login_error" style="color: red;"></p> <!-- Aquí mostraremos los errores -->
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('ajax_login_form', 'ajax_login_form_shortcode');




// Manejar la solicitud de inicio de sesión AJAX
add_action('wp_ajax_nopriv_ajax_login', 'ajax_login');
add_action('wp_ajax_ajax_login', 'ajax_login');

function ajax_login() {
    // Revisamos que las credenciales se han enviado correctamente
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // Limpiamos los datos
        $username = sanitize_text_field($_POST['username']);
        $password = sanitize_text_field($_POST['password']);
        
        // Las credenciales de inicio de sesión
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true
        );

        // Intentamos iniciar sesión con las credenciales proporcionadas
        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            // Si ocurre un error, enviamos el mensaje al cliente

            wp_send_json([
                'success' => 0,
                'message' => 'Por favor, revise los datos ingresados.'
            ]);
        } else {
            // Si el inicio de sesión es exitoso, redirigimos al usuario
            wp_send_json([
                'success' => 1,
                'redirect_url' => home_url('/seccion-privada')  // Puedes cambiar la URL de redirección
            ]);
        }
    } else {
        // Si faltan datos, devolvemos un error
        wp_send_json([
            'success' => false,
            'message' => 'Por favor, complete todos los campos.'
        ]);
    }

    // Finalizamos la solicitud AJAX
    wp_die();
}



// Shortcode de LogOut *********************************************************

function logout_shortcode() {
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user(); // Obtiene la información del usuario actual
		$logout_url = wp_logout_url(home_url('/acceso-clientes'));
		$first_name = get_user_meta($current_user->ID, 'first_name', true);
        $last_name = get_user_meta($current_user->ID, 'last_name', true);
        $full_name = trim($first_name . ' ' . $last_name); // Combina nombre y apellido
        
        if (empty($full_name)) {
            $full_name = $current_user->user_email; // En caso de que no se hayan establecido los nombres
        }
        return "Usuario: " . esc_html($full_name) . ' <a href="' . esc_url($logout_url) . '">[Salir]</a>';
    }
    return ''; // No muestra nada si el usuario no está logueado
}
add_shortcode('logout_link', 'logout_shortcode');