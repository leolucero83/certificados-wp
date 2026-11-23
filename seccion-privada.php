<?php

// Oculta la barra de wordpress
function disable_admin_bar_for_non_admins() {
    if (!current_user_can('administrator')) {
        add_filter('show_admin_bar', '__return_false');
    }
}
add_action('after_setup_theme', 'disable_admin_bar_for_non_admins');

// Crear el shortcode 'certificados_cliente' ************************************************************************

function seccion_privada_shortcode($atts) {
    // Verificar si el usuario está logueado
    if (!is_user_logged_in()) {
        return '<p>Debe estar logueado para ver sus certificados. <a href="' . esc_url(home_url('/acceso-clientes')) . '">Acceso Clientes</a></p>';
    }

    // Registrar y encolar el estilo CSS
    wp_enqueue_style('seccion-privada-style', plugin_dir_url(__FILE__) . 'seccion-privada.css');

    // Obtener el ID del usuario logueado
    $user_id = get_current_user_id();

    // Iniciar el formulario para el filtro de categorías
    $output = '<form action="" method="get" class="filtroCertificados">';
    $output .= '<label for="categoria">Filtrar por Categoría: </label>';

    // Desplegable de categorías
    $output .= wp_dropdown_categories(array(
        'taxonomy'     => 'categoria_certificados', // Usar la taxonomía correcta
        'name'         => 'categoria', // El campo del formulario
        'selected'     => isset($_GET['categoria']) ? $_GET['categoria'] : '', // Si hay una categoría seleccionada
        'hierarchical' => true, // Mostrar jerarquía
        'show_option_all' => 'Todas las categorías', // Opción para mostrar todas
        'hide_empty'   => false, // Incluir categorías sin productos
        'echo'         => false, // No imprimir directamente, para añadir al output
        'class'        => 'filter-categoria',
    ));

    // Botón de filtro
    $output .= '<input type="submit" value="Filtrar" class="submitBoton">';
    $output .= '</form>';

    // Obtener la categoría seleccionada (si existe)
    $categoria_seleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : '';

    // Configurar los argumentos de la consulta
    $args = array(
        'post_type'      => 'certificados', // El custom post type
        'posts_per_page' => 50, // Mostrar solo 50 resultados
        'paged'          => get_query_var('paged') ? get_query_var('paged') : 1, // Paginación
        'post_status'    => 'publish', // Solo los publicados
        'meta_query'     => array( // Filtrar por el campo personalizado "_usuario_asignado"
            array(
                'key'     => '_usuario_asignado',
                'value'   => $user_id,
                'compare' => '='
            )
        ),
    );

    // Si hay una categoría seleccionada, agregar la taxonomía a la consulta
    if ($categoria_seleccionada) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'categoria_certificados', // Taxonomía personalizada
                'field'    => 'id',
                'terms'    => $categoria_seleccionada, // Filtrar por la categoría seleccionada
                'operator' => 'IN'
            )
        );
    }

    // Realizar la consulta
    $query = new WP_Query($args);

    // Verificar si hay resultados
    if ($query->have_posts()) {
        $output .= '<table class="listadoCertificados">';
        $output .= '<thead><tr><th>Título</th><th>Categoría</th><th>Fecha de Carga</th><th>Vencimiento</th><th>PDF</th></tr></thead>';
        $output .= '<tbody>';

        // Recorrer los resultados
        while ($query->have_posts()) {
            $query->the_post();
            $pdf_file = get_post_meta(get_the_ID(), '_pdf_certificado', true); // Obtener el archivo PDF desde el custom field
            $fecha_vencimiento = get_post_meta(get_the_ID(), '_fecha_vencimiento', true); // Obtener la fecha de vencimiento
            $categorias = get_the_terms(get_the_ID(), 'categoria_certificados'); // Obtener categorías asociadas

            // Calcular la diferencia de días entre la fecha de vencimiento y la fecha actual
            $current_date = current_time('Y-m-d');
            $vencimiento_date = date('Y-m-d', strtotime($fecha_vencimiento));
            $date_diff = (strtotime($vencimiento_date) - strtotime($current_date)) / (60 * 60 * 24); // Diferencia en días

            // Determinar la clase a aplicar a la fecha de vencimiento
            if ($date_diff > 90) {
                $class = 'verde'; // Más de 90 días
            } elseif ($date_diff <= 90 && $date_diff > 0) {
                $class = 'amarillo'; // Menos de 90 días
            } else {
                $class = 'rojo'; // Ya venció
            }

            // Formatear la salida
            $output .= '<tr>';
            $output .= '<td>' . get_the_title() . '</td>';
            $output .= '<td>';
            if ($categorias && !is_wp_error($categorias)) {
                $categoria_nombres = wp_list_pluck($categorias, 'name');
                $output .= implode(', ', $categoria_nombres); // Listar categorías separadas por coma
            }
            $output .= '</td>';
            $output .= '<td>' . get_the_date('d/m/Y') . '</td>'; // Mostrar la fecha de carga con el formato 'd/m/Y'
            $output .= '<td class="' . $class . '">' . date('d/m/Y', strtotime($fecha_vencimiento)) . '</td>'; // Mostrar la fecha de vencimiento con la clase aplicada
            if($pdf_file){
                $output .= '<td><a href="' . esc_url($pdf_file) . '" download>↓ Descargar</a></td>';
            }
            else
            {
                $output .= '<td> </td>';
            }
            $output .= '</tr>';
        }

        $output .= '</tbody>';
        $output .= '</table>';

        // Paginación
        $output .= '<div class="pagination">';
        $output .= paginate_links(array(
            'total' => $query->max_num_pages
        ));
        $output .= '</div>';

        wp_reset_postdata(); // Restablecer la consulta

    } else {
        $output .= '<p>No tienes certificados disponibles.</p>';
    }

    return $output;
}
add_shortcode('seccion_privada', 'seccion_privada_shortcode');