# CodeIgniter 4 Application Starter

# API RESTful con JWT en CodeIgniter 4

## Descripción

Esta API RESTful en CodeIgniter 4 se encarga de gestionar usuarios y autenticación mediante tokens JWT. Utiliza una base de datos MySQL para almacenar los datos de usuario.

## Requisitos

Asegúrate de tener los siguientes requisitos antes de ejecutar la API:

1. Tener una base de datos MySQL configurada.
2. Ejecutar las migraciones y seeders para crear tablas y usuarios iniciales.

   ```bash
   php spark migrate
   php spark db:seed UsersSeeder
   ```

# Endpoints

## Autenticación

### Login de Usuario

Método: POST
Descripción: Iniciar sesión de usuario proporcionando credenciales.
Ruta: /auth/login
Registro de Usuario (Solo para administradores)

Método: POST
Descripción: Registrar un nuevo usuario proporcionando datos requeridos.
Ruta: /auth/register
Usuarios
Listar Usuarios

Método: GET
Descripción: Obtener una lista de usuarios registrados.
Ruta: /users
Ver Detalles de Usuario (Solo para administradores)

Método: GET
Descripción: Obtener detalles de un usuario por su ID.
Ruta: /users/{id}
Actualizar Usuario (Solo para administradores)

Método: POST
Descripción: Actualizar parcialmente los detalles de un usuario por su ID.
Ruta: /users/{id}
Eliminar Usuario (Solo para administradores)

Método: DELETE
Descripción: Eliminar parcialmente la cuenta de un usuario por su ID.
Ruta: /users/{id}
Restaurar Usuario Eliminado (Solo para administradores)

Método: POST
Descripción: Restaurar un usuario que ha sido eliminado previamente proporcionando su ID.
Ruta: /users/restore/{id}
Generar PDF de Lista de Usuarios (Solo para administradores)

Método: GET
Descripción: Generar un archivo PDF que contiene una lista de usuarios.
Ruta: /users/pdf
Perfil de Usuario
Obtener Perfil de Usuario

Método: GET
Descripción: Obtener detalles del usuario autenticado.
Ruta: /user/profile
Actualizar Perfil de Usuario

Método: POST
Descripción: Actualizar el perfil del usuario autenticado.
Ruta: /user/profile/update

Autor
Oscar Muñoz Franco
