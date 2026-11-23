<?php

// Custom Post Type Certificados ********************************************************************

function crear_cpt_certificados() {
    $labels = array(
        'name' => 'Certificados',
        'singular_name' => 'Certificado',
        'add_new' => 'Añadir Nuevo',
        'add_new_item' => 'Añadir Nuevo Certificado',
        'edit_item' => 'Editar Certificado',
        'new_item' => 'Nuevo Certificado',
        'view_item' => 'Ver Certificado',
        'search_items' => 'Buscar Certificados',
        'not_found' => 'No se encontraron certificados',
        'not_found_in_trash' => 'No se encontraron certificados en la papelera',
        'all_items' => 'Todos los Certificados',
        'menu_name' => 'Certificados',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
        'menu_position' => 5,
        'menu_icon' => 'dashicons-awards',
        'show_in_rest' => true, // Para soportar el editor de bloques
        'taxonomies' => array('categoria_certificados'), // Añadir la taxonomía
    );

    register_post_type('certificados', $args);
}
add_action('init', 'crear_cpt_certificados');

// Registrar taxonomía de categorías para el CPT "certificados" *********************************
function crear_taxonomia_certificados() {
    $labels = array(
        'name' => 'Categorías de Certificados',
        'singular_name' => 'Categoría de Certificado',
        'search_items' => 'Buscar Categorías de Certificados',
        'all_items' => 'Todas las Categorías de Certificados',
        'parent_item' => 'Categoría Principal',
        'parent_item_colon' => 'Categoría Principal:',
        'edit_item' => 'Editar Categoría de Certificado',
        'update_item' => 'Actualizar Categoría de Certificado',
        'add_new_item' => 'Añadir Nueva Categoría de Certificado',
        'new_item_name' => 'Nueva Categoría de Certificado',
        'menu_name' => 'Categorías de Certificados',
    );

    $args = array(
        'hierarchical' => true, // True para permitir subcategorías
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'categoria-certificados'),
        'show_in_rest' => true, // Para soportar el editor de bloques
    );

    register_taxonomy('categoria_certificados', array('certificados'), $args);
}
add_action('init', 'crear_taxonomia_certificados', 0);

// Agregar Metaboxes Personalizados ************************************************************
function agregar_metabox_certificados() {
    add_meta_box(
        'metabox_certificados',
        'Detalles del Certificado',
        'mostrar_metabox_certificados',
        'certificados',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'agregar_metabox_certificados');

// Campos personalizados en la metabox
function mostrar_metabox_certificados($post) {
    // Campo para cargar archivo PDF
    $pdf_certificado = get_post_meta($post->ID, '_pdf_certificado', true);
    echo '<table cellpadding="5"><tr><td>Archivo PDF:</td>';
    echo '<td><input type="file" name="pdf_certificado" id="pdf_certificado" />';
    if ($pdf_certificado) {
        echo ' <em style="background: yellow"><b>Archivo actual</b>: <a href="' . esc_url($pdf_certificado) . '" target="_blank">Ver PDF</a></em>';
    }
    echo '</td></tr>';

    // Campo para fecha de vencimiento
    $fecha_vencimiento = get_post_meta($post->ID, '_fecha_vencimiento', true);
    echo '<tr><td>Vencimiento:</td>';
    echo '<td><input type="date" name="fecha_vencimiento" value="' . esc_attr($fecha_vencimiento) . '" /></td></tr>';

    // Campo para asignar usuario de WordPress
    $usuario_asignado = get_post_meta($post->ID, '_usuario_asignado', true);
    echo '<tr><td>Cliente Asignado:</td><td>';
    wp_dropdown_users(array(
        'name' => 'usuario_asignado',
        'selected' => $usuario_asignado,
        'show_option_none' => 'Seleccionar Cliente',
        'show' => 'display_name', // Muestra el nombre de usuario user_login
        'role' => 'Cliente',
    ));
    echo '</td></tr></table>';
}

// Guardar los valores de los campos personalizados
function guardar_campos_certificados($post_id) {
    if (array_key_exists('fecha_vencimiento', $_POST)) {
        update_post_meta($post_id, '_fecha_vencimiento', sanitize_text_field($_POST['fecha_vencimiento']));
    }

    if (array_key_exists('usuario_asignado', $_POST)) {
        update_post_meta($post_id, '_usuario_asignado', sanitize_text_field($_POST['usuario_asignado']));
    }

    if (!empty($_FILES['pdf_certificado']['name'])) {
        // Depuración
    error_log(print_r($_FILES['pdf_certificado'], true));
        $file = $_FILES['pdf_certificado'];
        $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
        if (isset($upload['url']) && $upload['url'] != '') {
            update_post_meta($post_id, '_pdf_certificado', esc_url($upload['url']));
        }
    }
}
add_action('save_post', 'guardar_campos_certificados');

function agregar_enctype_formulario() {
    echo ' enctype="multipart/form-data"';
}
add_action('post_edit_form_tag', 'agregar_enctype_formulario');


/*  LISTADO DE CERTIFICADOS PARA EL ADMIN *************************************************************** */

// Agregar columnas personalizadas al listado de Certificados
function agregar_columnas_certificados($columns) {
    $columns['usuario_asignado'] = 'Cliente';
    $columns['fecha_vencimiento'] = 'Fecha de Vencimiento';
    return $columns;
}
add_filter('manage_edit-certificados_columns', 'agregar_columnas_certificados');

// Mostrar datos en las columnas personalizadas
function mostrar_datos_columnas_certificados($column, $post_id) {
    switch ($column) {
        case 'usuario_asignado':
            $usuario_id = get_post_meta($post_id, '_usuario_asignado', true);
            if ($usuario_id) {
                $usuario = get_userdata($usuario_id);
                $nombre_usuario = esc_html($usuario->display_name);
                $url = add_query_arg(array(
                    'post_type' => 'certificados',
                    'usuario_asignado' => $usuario_id
                ), admin_url('edit.php'));
                echo '<a href="' . esc_url($url) . '">' . $nombre_usuario . '</a>';
            } else {
                echo 'Sin asignar';
            }
            break;

        case 'fecha_vencimiento':
            $fecha_vencimiento = get_post_meta($post_id, '_fecha_vencimiento', true);

            if ($fecha_vencimiento) {
                $fecha_vencimiento_timestamp = strtotime($fecha_vencimiento);
                $fecha_actual_timestamp = strtotime(date('Y-m-d'));
                $dias_restantes = ($fecha_vencimiento_timestamp - $fecha_actual_timestamp) / DAY_IN_SECONDS;

                // Determinar el color del círculo
                if ($dias_restantes <= 0) {
                    $color = 'red'; // Fecha vencida
                } elseif ($dias_restantes <= 90) {
                    $color = 'yellow'; // Menos de 90 días
                } else {
                    $color = 'green'; // Más de 90 días
                }

                // Mostrar fecha con círculo de color
                echo '<span>' . esc_html(date('d/m/Y', $fecha_vencimiento_timestamp)) . '</span>';
                echo '<span style="display: inline-block; width: 12px; height: 12px; margin-left: 8px; border-radius: 50%; background-color: ' . esc_attr($color) . ';"></span>';
            } else {
                echo 'No definida';
            }
            break;
    }
}
add_action('manage_certificados_posts_custom_column', 'mostrar_datos_columnas_certificados', 10, 2);

// Agregar filtro por usuario asignado en el listado
function filtrar_certificados_por_usuario($query) {
    global $pagenow;
    if ($pagenow == 'edit.php' && isset($_GET['usuario_asignado']) && $_GET['post_type'] == 'certificados') {
        $usuario_id = sanitize_text_field($_GET['usuario_asignado']);
        $meta_query = array(
            array(
                'key' => '_usuario_asignado',
                'value' => $usuario_id,
                'compare' => '='
            )
        );
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'filtrar_certificados_por_usuario');



// Hacer las columnas "sortable" (ordenables)
function columnas_certificados_sortable($columns) {
    $columns['usuario_asignado'] = 'usuario_asignado';
    $columns['fecha_vencimiento'] = 'fecha_vencimiento';
    return $columns;
}
add_filter('manage_edit-certificados_sortable_columns', 'columnas_certificados_sortable');

// Ordenar por columnas personalizadas
function ordenar_certificados_por_columnas($query) {
    if (!is_admin()) {
        return;
    }

    $orderby = $query->get('orderby');
    if ('usuario_asignado' === $orderby) {
        $query->set('meta_key', '_usuario_asignado');
        $query->set('orderby', 'meta_value');
    } elseif ('fecha_vencimiento' === $orderby) {
        $query->set('meta_key', '_fecha_vencimiento');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'ordenar_certificados_por_columnas');
