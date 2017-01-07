AUTH
======

Es un sistema web desarrollado en PHP que tiene como objetivo el control de acceso a nuestros diversos aplicativos (`Gestión CUAD`, `Directorio`, `Reportes`).

**Versión 1.0**

----------

**Funcionalidades**

 - LogIn
 - Resetear Contraseña
 - Inicio
    - Listado de Aplicaciones
    - Cambiar Contraseña
 - [Logs](http://10.1.20.51:8000/Developers/logs) (`Esto es un proyecto independiente`)
 - Activar Email

----------

**Instalación del Proyecto**

 - git clone `http://10.1.20.51:8000/git/vvaldez/Auth.git`
 - composer update|install -d application
 
----------

**Configuración del Proyecto**

 - Ejecutar el siguiente comando: `git update-index --assume-unchanged ./application\module\Application\src\Application\OCISchema\Database\Table.php`.
 - Copiar el archivo `config/autoload/global.php` y guardarlo como `config/autoload/local.php` y reemplazar las conexiones por las de tu local.
    - server: Información de LDAP.
    - db: Datos de Conexión de la Base de Datos.    
    - auth: Credenciales de Provising y System para autenticarse al XSP.
    - token: Valor utilizado para ejecutar las tareas desde consola. (`Modificar si estas en ambiente de desarrollo local`)
    - Las siguientes claves no son necesarias copiarlas:
        - whiteList: Rutas para acceder sin una sessión activa. 
 - Configurar los `Cron Jobs` (`Si se modifica el TOKEN se tiene que modificar los cron jobs`):
    - `0 3 * * *` php `PATH`/index.php clean resetpwd `TOKEN`.
    - `15 3 * * *` php `PATH`/index.php notify resetpwd `TOKEN`.
    - `30 3 * * *` php `PATH`/index.php remove xlsx `TOKEN`.
        - `PATH`: Se debe especificar la ruta completa hasta llegar al proyecto.
        - `TOKEN`: Esta configurado en `application/config/autoload/global.php`.

Para mayor información ir al siguiente [http://10.1.20.51:8000/Developers/auth/tree/master/application/config/autoload](http://10.1.20.51:8000/Developers/auth/tree/master/application/config/autoload).

----------

 **Estructura del Proyecto**

 - `auth/application`: Contiene el core de AUTH el cual es un fork del Skeletoon Application de Zend Framework 2.
 - `auth/css|images|js|libs`: Son las rutas para almacenar las hojas de estilos, javascripts, imágenes y librerias extras del proyecto.
 - `index.php`: Es el encargado de procesar las solicitudes e iniciar las operaciones con el framework.

----------

 **Requerimientos para los Servidores de Desarrollo y Productivo**

- PHP 5.5
- MySQL Server 5.6
 - Habilitar las Extensiones de PHP:
    - php_mcrypt
    - php_xml 
    - php_zip
 - Permisos de escritura y lectura para el usuario de Apache a los siguientes directorios:
    - auth
    - application/data y los subdirectorios

----------

 **Actualización del Proyecto en el Servidor de Desarrollo o Productivo**

 - git pull origin master


----------

 **Editar /etc/apache2/sites-enabled/000-default.conf**

  <Directory />
   Options FollowSymLinks Indexes
   AllowOverride All
  </Directory>

  <Directory /var/www/>
   Options Indexes FollowSymLinks MultiViews
   AllowOverride All
   Order allow,deny
   allow from all
  </Directory>

----------


# cuadcisco
