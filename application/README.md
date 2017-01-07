application
=======================

En este directorio se encuentra todo el core de Zend Framework 2. Aqui se encontraran archivos de configuracion, modulos, controladores, vistas, etc.

----------

**Estructura**

 - `config`:
    - Archivo de configuración (`application.config.php`) para nuestro proyecto.
    - Archivos de configuración para diversos entornos dentro de `autoload` (`global.php ó local.php`).
 - `data`
    - Directorio para almacenar archivos temporales.
 - `module`
    - Directorio para ir agregando nuestras módulos.
 - `vendor`
    - Directorio para almacenar todas las dependencias descargadas desde `composer`.

----------

**Actualizar Dependencias**

Para agregar o eliminar dependencias se hace directamente en el archivo `composer.json` y posteriormente ejecutar el comando `composer update`.

----------

**Generar Classmap**

 - `./application\vendor\bin\classmap_generator.php ./application\module\Application`