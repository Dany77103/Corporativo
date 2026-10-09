<?php
// ==========================================
// DEFINICIÓN DE ROLES DEL SISTEMA
// ==========================================
define('ROL_SUPERADMIN', 'superadmin');
define('ROL_ADMIN',      'admin');
define('ROL_SOPORTE',    'soporte');
define('ROL_LECTOR',     'lector'); // Rol con permisos de solo lectura
define('ROL_EMPLEADO',   'empleado');

/**
 * Matriz de Permisos por Rol:
 * - ver: Consultar registros y reportes.
 * - editar: Modificar datos de equipos.
 * - borrar: Eliminar registros.
 * - crear_equipos: Dar de alta activos (Manual o Excel).
 * - crear_usuarios: Crear cuentas de nivel admin/soporte/lector/empleado.
 * - gestionar_admins: Crear o modificar usuarios con rol Superadmin.
 * - solo_propios: Solo ve los equipos asignados a su propia cuenta (cuenta_id).
 */
function obtenerPermisosPorRol($rol) {
    // Normalizar la cadena del rol a minúsculas para evitar discordancias
    $rol = strtolower(trim($rol));

    $permisos = [
        ROL_SUPERADMIN => [
            'ver'              => true,
            'editar'           => true,
            'borrar'           => true,
            'crear_equipos'    => true,
            'crear_usuarios'   => true,
            'gestionar_admins' => true,
            'solo_propios'     => false,
        ],
        ROL_ADMIN => [
            'ver'              => true,
            'editar'           => true,
            'borrar'           => true,
            'crear_equipos'    => true,
            'crear_usuarios'   => true,
            'gestionar_admins' => false,
            'solo_propios'     => false,
        ],
        ROL_SOPORTE => [
            'ver'              => true,
            'editar'           => true,
            'borrar'           => true,
            'crear_equipos'    => true,
            'crear_usuarios'   => false,
            'gestionar_admins' => false,
            'solo_propios'     => false,
        ],
        ROL_LECTOR => [
            'ver'              => true,
            'editar'           => false,
            'borrar'           => false,
            'crear_equipos'    => false,
            'crear_usuarios'   => false,
            'gestionar_admins' => false,
            'solo_propios'     => false,
        ],
        ROL_EMPLEADO => [
            'ver'              => true,
            'editar'           => false,
            'borrar'           => false,
            'crear_equipos'    => false,
            'crear_usuarios'   => false,
            'gestionar_admins' => false,
            'solo_propios'     => true,
        ]
    ];

    return $permisos[$rol] ?? [
        'ver'              => false,
        'editar'           => false,
        'borrar'           => false,
        'crear_equipos'    => false,
        'crear_usuarios'   => false,
        'gestionar_admins' => false,
        'solo_propios'     => false
    ];
}

/**
 * Verifica si el usuario autenticado en la sesión tiene un permiso específico.
 */
function tienePermiso($permisoRequerido) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['rol']) || empty($_SESSION['rol'])) {
        return false;
    }

    $permisos = obtenerPermisosPorRol($_SESSION['rol']);

    return isset($permisos[$permisoRequerido]) && $permisos[$permisoRequerido] === true;
}

/**
 * Helper rápido para comprobar si el usuario tiene permiso de edición/escritura.
 */
function tienePermisoEscritura() {
    return tienePermiso('editar') || tienePermiso('crear_equipos');
}

/**
 * Redirecciona al usuario si no cuenta con la autorización requerida.
 */
function requerirPermiso($permisoRequerido) {
    if (!tienePermiso($permisoRequerido)) {
        header("Location: index.php?error=acceso_denegado");
        exit();
    }
}

/**
 * ¿El usuario solo debe ver los equipos asignados a su cuenta?
 */
function esSoloPropios() {
    return tienePermiso('solo_propios');
}

/**
 * ID de la cuenta en sesión (0 si no existe).
 */
function miCuentaId() {
    return (int)($_SESSION['usuario_id'] ?? 0);
}
?>