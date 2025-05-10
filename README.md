
          
#  Darkkhaki

## Elementos Fundamentales del Código

### Variables y Tipos de Datos
- **Variables de Sesión**: Utilizando `$_SESSION` para gestionar el estado del usuario
- **Variables de Base de Datos**: 
  - `$db`: Objeto PDO para conexión SQLite
  - `$stmt`: Para preparar consultas SQL
  - `$horasDB`: Array asociativo para almacenar horas
- **Tipos de Datos**:
  - TEXT: Para nombres, emails, usuarios
  - INTEGER: Para IDs y claves primarias
  - REAL: Para almacenar horas
  - Booleanos: En configuraciones de diseño
  - Arrays: Para estructurar datos

### Constantes y Operadores
- **Operadores**:
  - Comparación: `===`, `==`, `!==`
  - Lógicos: `&&`, `||`
  - Concatenación: `.`
  - Operadores ternarios: `?:`
  - Operadores de asignación: `=`, `??`

## Estructuras de Control

### Estructuras de Selección
```php:c:\xampp\htdocs\darkkhaki\index.php
if (!isset($_SESSION['user'])) {
    // ... existing code ...
} else {
    // ... existing code ...
}

if (isset($_POST['login'])) {
    // ... existing code ...
} elseif (isset($_POST['register'])) {
    // ... existing code ...
}
```

### Estructuras de Repetición
```php:c:\xampp\htdocs\darkkhaki\index.php
foreach ($_POST['horas'] as $fecha => $horas) {
    // ... existing code ...
}

for ($dia = 1; $dia <= $dias; $dia++) {
    // ... existing code ...
}
```

## Control de Excepciones y Gestión de Errores
- Manejo de errores en la base de datos:
```php:c:\xampp\htdocs\darkkhaki\index.php
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```
- Validación de usuarios y mensajes de error:
```php:c:\xampp\htdocs\darkkhaki\index.php
if (!$user) {
    $_SESSION['error'] = "Usuario no encontrado";
    return false;
}
```

## Documentación del Código
El código incluye comentarios básicos para secciones importantes:
```php:c:\xampp\htdocs\darkkhaki\index.php
// Configuración SQLite
// Crear tablas si no existen
// Insertar usuario inicial si no existe
// Procesamiento de formularios
```

## Paradigma de Programación
El proyecto utiliza una combinación de:
- **Programación Estructurada**: Para el flujo principal del programa
- **Programación Orientada a Objetos**: Para la conexión a base de datos (PDO)

## Clases y Objetos Principales
- **PDO**: Clase principal para manejo de base de datos
- **Tablas en Base de Datos**:
  - usuarios
  - horas
  - plantillas

## Conceptos Avanzados
- **Herencia**: No se implementa directamente
- **Interfaces**: Se utiliza la interfaz PDO para base de datos
- **Polimorfismo**: No se implementa directamente

## Gestión de Información
- **Base de Datos**: SQLite para almacenamiento persistente
- **Interfaz Gráfica**: 
  - Formularios HTML para entrada de datos
  - CSS para estilización
  - Diseño responsivo y personalizable

## Estructuras de Datos
- **Arrays Asociativos**: Para manejar datos de usuarios y horas
- **Matrices**: Grid para visualización del calendario
- **JSON**: Para almacenar plantillas de horas

## Técnicas Avanzadas
- **Expresiones Regulares**: No se implementan directamente
- **Flujos de E/S**: 
  - Entrada: Formularios POST
  - Salida: Generación dinámica de HTML
- **Seguridad**:
  - Password hashing
  - Prepared statements para prevenir SQL injection
  - Control de sesiones




          
# Sistemas Informáticos - Análisis de Infraestructura

## Entorno de Desarrollo y Producción

### Características del Hardware
Basado en el análisis del código y la estructura del proyecto, podemos identificar:

- **Entorno de Desarrollo**:
  - Servidor XAMPP local
  - Requisitos mínimos para ejecutar PHP y SQLite
  - No se requiere hardware especializado debido a la ligereza de SQLite

- **Entorno de Producción**:
  - No se observa configuración específica para producción
  - El sistema está diseñado para funcionar en un servidor web básico con soporte PHP

### Sistema Operativo
- **Desarrollo**: 
  - Windows (evidenciado por la ruta `c:\xampp\htdocs\darkkhaki`)
  - XAMPP como stack de desarrollo
  - Elección justificada por:
    - Facilidad de instalación y configuración
    - Integración nativa con PHP y SQLite
    - Amplia comunidad de soporte

- **Producción**:
  - No se observa configuración específica para producción

### Configuración de Red
- **Protocolo**: HTTP (basado en la configuración XAMPP)
- **Seguridad**:
  - Implementación de sesiones PHP (`session_start()`)
  - Protección contra SQL injection mediante prepared statements
  - Hashing de contraseñas con `password_hash()`

### Sistema de Copias de Seguridad
- SQLite utiliza Write-Ahead Logging (WAL):
```php:c:\xampp\htdocs\darkkhaki\index.php
$db->exec('PRAGMA journal_mode=WAL');  // Enable Write-Ahead Logging
```
- No se observa implementación de backups automatizados
- La base de datos se almacena en `databases/darkkhaki.db`

### Seguridad de Datos
1. **Autenticación**:
   - Sistema de login/registro implementado
   - Almacenamiento seguro de contraseñas con hash
   - Control de sesiones

2. **Base de Datos**:
   - Uso de prepared statements para prevenir SQL injection
   - Timeout configurado para transacciones:
```php:c:\xampp\htdocs\darkkhaki\index.php
$db->exec('PRAGMA busy_timeout=5000'); // Set timeout to 5 seconds
```

3. **Validación de Datos**:
   - Verificación de usuarios existentes
   - Validación de formularios con atributos HTML required

### Configuración de Usuarios y Permisos
1. **Sistema de Usuarios**:
   - Usuario administrador predefinido ('danielcreux')
   - Roles diferenciados (admin puede ver calendarios de otros usuarios)
   - Permisos basados en sesión

2. **Estructura de Base de Datos**:
```sql
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT,
    email TEXT,
    usuario TEXT UNIQUE,
    contrasena TEXT
);
```

### Documentación Técnica
- El código incluye comentarios básicos para secciones principales




          
# Entornos de Desarrollo:

## IDE y Configuración:
- El proyecto está desarrollado en un entorno XAMPP, como se evidencia en la ruta `c:\xampp\htdocs\darkkhaki`
- No se observa un IDE específico en el código, aunque el proyecto es compatible con cualquier editor PHP

## Automatización de Tareas:
- No se observan herramientas de automatización implementadas
- Las tareas principales se manejan mediante PHP nativo

## Control de Versiones:
- No se observa implementación de control de versiones en el proyecto
- No hay archivos de configuración de Git u otros sistemas de control de versiones

## Estrategia de Refactorización:
- El código muestra una estructura modular con funciones definidas
- Separación clara de responsabilidades entre autenticación, gestión de datos y presentación

## Documentación Técnica:
- Se utiliza documentación básica en forma de comentarios en el código:
```php:c:\xampp\htdocs\darkkhaki\index.php
// Configuración SQLite
// Crear tablas si no existen
// Insertar usuario inicial si no existe
// Procesamiento de formularios
```
- Existe un archivo README.md con documentación estructurada

## Diagramas:
- No se observan diagramas de clases o comportamiento en el proyecto

# Bases de Datos:

## Sistema Gestor:
- SQLite como sistema de base de datos:
```php:c:\xampp\htdocs\darkkhaki\index.php
$db = new PDO('sqlite:databases/darkkhaki.db');
```
- Elección justificada por:
  - Ligereza y portabilidad
  - No requiere servidor dedicado
  - Ideal para aplicaciones de escala media

## Modelo Entidad-Relación:
- Tres tablas principales:
  - usuarios (gestión de usuarios)
  - horas (registro de horas)
  - plantillas (almacenamiento de plantillas)
- Relaciones establecidas mediante FOREIGN KEY

## Características Avanzadas:
- Uso de Write-Ahead Logging (WAL):
```php:c:\xampp\htdocs\darkkhaki\index.php
$db->exec('PRAGMA journal_mode=WAL');
```
- Timeout configurado para transacciones:
```php:c:\xampp\htdocs\darkkhaki\index.php
$db->exec('PRAGMA busy_timeout=5000');
```

## Protección y Recuperación:
- Prepared statements para prevenir SQL injection
- Validación de datos de entrada
- No se implementan backups automatizados

# Lenguajes de Marcas y Sistemas de Gestión:

## Estructura HTML:
- Generación dinámica de HTML mediante PHP
- Formularios estructurados para login y registro
- Implementación de validación mediante atributos HTML5

## Frontend:
- CSS para estilización (archivo estilos.css)
- JavaScript no se observa implementado directamente
- Diseño responsivo mediante clases CSS

## Interacción DOM:
- No se observa manipulación directa del DOM con JavaScript
- La interactividad se maneja mediante PHP y formularios

## Validación:
- Validación del lado del servidor implementada
- Validación HTML5 mediante atributos required

## Conversión de Datos:
- Uso de JSON para almacenar plantillas:
```php:c:\xampp\htdocs\darkkhaki\index.php
$horas = json_encode($_POST['horas']);
```

## Gestión Empresarial:
- La aplicación es un sistema de gestión de horas
- Funcionalidades de gestión empresarial:
  - Control de usuarios
  - Registro de horas
  - Generación de plantillas
  - Reportes de horas totales

# Proyecto Intermodular:

## Objetivo:
- Sistema de gestión y control de horas
- Calendario personalizable para registro de tiempo

## Necesidad Cubierta:
- Registro y seguimiento de horas trabajadas
- Gestión de múltiples usuarios
- Visualización personalizada de datos

## Stack Tecnológico:
- Backend: PHP
- Base de Datos: SQLite
- Frontend: HTML, CSS
- Servidor: XAMPP

## Desarrollo por Versiones:
- Versión actual implementa funcionalidades básicas:
  - Gestión de usuarios
  - Registro de horas
  - Plantillas
  - Personalización de vista

        
        