<?php
/*
Plugin Name: Certificados
Description: Plugin que permite iniciar sesión a los clientes para acceder a la seccion privada de descarga de certificados
Version: 1.0
Author: Leandro Lucero
*/

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Incluir archivos
include_once(plugin_dir_path(__FILE__) . 'perfil-cliente.php');

include_once(plugin_dir_path(__FILE__) . 'CPT-certificados.php');

include_once(plugin_dir_path(__FILE__) . 'ajax_login_form.php');

include_once(plugin_dir_path(__FILE__) . 'seccion-privada.php');




?>