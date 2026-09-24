<?php
// Roles del sistema
define('ROL_SUPERADMIN', 'superadmin');
define('ROL_ADMIN',      'admin');
define('ROL_SOPORTE',    'soporte');
define('ROL_LECTOR',     'lector');

/**
 * Matriz de Permisos por Rol:
 * - ver: Consultar registros y reportes.
 * - editar: Modificar datos de equipos.
 * - borrar: Eliminar registros.
 * - crear_equipos: Dar de alta activos.
 * - crear_usuarios: Crear cuentas de nivel admin/soporte/lector.
 * - gestionar_admins: Crear o modificar usuarios con rol Superadmin/Admin.
 */
function obtenerPermisosPorRol($rol) {
    $permisos = [
        ROL_SUPERADMIN => [
            'ver'              => true,
            'editar'           => true,
            'borrar'           => true,
            'crear_equipos'    => true,
            'crear_usuarios'   => true,
            'gestionar_admins' => true,
        ],
        ROL_ADMIN => [
            'ver'              => true,
            'editar'           => true,
            'borrar'           => true,
            'crear_equipos'    => true,
            'crear_usuarios'   => true,
            'gestionar_admins' => false,
        ],
        ROL_SOPORTE => [
            'ver'              => true,
            'editar'           => true,
            'borrar'           => true,
            'crear_equipos'    => true,
            'crear_usuarios'   => false,
            'gestionar_admins' => false,
        ],
        ROL_LECTOR => [
            'ver'              => true,
            'editar'           => false,
            'borrar'           => false,
            'crear_equipos'    => false,
            'crear_usuarios'   => false,
            'gestionar_admins' => false,
        ]
    ];

    return $permisos[$rol] ?? [
        'ver'              => false, 
        'editar'           => false, 
        'borrar'           => false, 
        'crear_equipos'    => false, 
        'crear_usuarios'   => false, 
        'gestionar_admins' => false
    ];
}

/**
 * Función para verificar si el usuario logueado tiene un permiso específico.
 */
function tienePermiso($permisoRequerido) {
    if (!isset($_SESSION['rol'])) {
        return false;
    }
    $permisos = obtenerPermisosPorRol($_SESSION['rol']);
    return isset($permisos[$permisoRequerido]) && $permisos[$permisoRequerido] === true;
}

/**
 * Redirecciona si el usuario no cuenta con la autorización requerida.
 */
function requerirPermiso($permisoRequerido) {
    if (!tienePermiso($permisoRequerido)) {
        header("Location: index.php?error=acceso_denegado");
        exit();
    }
}
?>