<?php

// Crear un nuevo perfil de usuario "Cliente" *********************************************************

function crear_rol_cliente() {
    add_role(
        'cliente',
        'Cliente',
        array(
            'read' => true,              // Permite leer contenido en el sitio
            'edit_posts' => false,       // No permite editar publicaciones
            'delete_posts' => false,     // No permite borrar publicaciones
            'upload_files' => false,     // No permite subir archivos
        )
    );
}
add_action('init', 'crear_rol_cliente');