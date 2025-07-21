# 🚀 Sistema de Infraestructura - Instalación

## 📋 Requisitos Previos

1. **XAMPP** instalado y funcionando
2. **MongoDB** instalado y corriendo en puerto 27017
3. **PHP 8.0+** (viene con XAMPP)
4. **Composer** instalado globalmente

## 🔧 Instalación Paso a Paso

### 1. Descargar el Proyecto
```bash
# Descomprimir el ZIP en:
C:\xampp\htdocs\proyecto_bd\
```

### 2. Instalar Dependencias MongoDB
```bash
# Abrir terminal en la carpeta del proyecto
cd C:\xampp\htdocs\proyecto_bd

# Instalar dependencias
composer install --ignore-platform-req=ext-mongodb
```

### 3. Configurar MongoDB
```bash
# Asegurar que MongoDB esté corriendo
mongod --version

# Importar datos (si tienes archivos CSV)
# Los datos se cargan automáticamente desde el dashboard
```

### 4. Configurar PHP
```ini
# En php.ini, asegurar que esté habilitado:
extension=mongodb
```

### 5. Iniciar Servicios
```bash
# Iniciar Apache desde XAMPP
# Iniciar MongoDB
net start MongoDB
```

## 🌐 Acceder al Sistema

### URLs Principales:
- **Dashboard Principal:** `http://localhost/proyecto_bd/dashboard.html`
- **Panel Admin:** `http://localhost/proyecto_bd/admin.php`
- **Login Admin:** `http://localhost/proyecto_bd/login_admin.html`

### URLs de Funcionalidades Avanzadas:
- **Transacciones:** `http://localhost/proyecto_bd/transacciones_simuladas.php`
- **Procedimientos:** `http://localhost/proyecto_bd/procedimientos.php`
- **Auditoría:** `http://localhost/proyecto_bd/sistema_auditoria.php`
- **Test JSON:** `http://localhost/proyecto_bd/test_json.php`

## 🎯 Funcionalidades Implementadas

### ✅ Funcionalidades Básicas:
- Dashboard interactivo con mapas
- Filtros avanzados (departamento, estado, titularidad)
- Gráficos con Chart.js (barras, circular, pastel)
- Visualización de puertos, aeropuertos y ferroviarias

### ✅ Funcionalidades Avanzadas de BD:
- **🔄 Transacciones Simuladas** - Operaciones ACID simuladas
- **🔍 Validación de Integridad** - Análisis de calidad de datos
- **💾 Backup Automático** - Respaldo de colecciones
- **⚡ Medición de Rendimiento** - Benchmarking de consultas
- **📄 Logs y Auditoría** - Trazabilidad completa
- **⚙️ Procedimientos Almacenados** - Agregaciones complejas

### ✅ Sistema de Administración:
- Login con Google OAuth
- CRUD completo para puertos y aeropuertos
- Interfaz moderna con validaciones

## 🗂️ Estructura del Proyecto

```
proyecto_bd/
├── dashboard.html              # Dashboard principal
├── admin.php                   # Panel de administración
├── conexion.php               # Conexión a MongoDB
├── api_puntos.php             # API para datos del mapa
├── api_admin.php              # API para administración
├── transacciones_simuladas.php # Sistema de transacciones
├── procedimientos.php          # Procedimientos almacenados
├── sistema_auditoria.php       # Logs y auditoría
├── triggers_explicacion.md     # Documentación técnica
└── vendor/                     # Dependencias MongoDB
```

## 🚨 Solución de Problemas

### Error: "Class 'MongoDB\Client' not found"
```bash
composer install --ignore-platform-req=ext-mongodb
```

### Error: "Connection refused"
```bash
# Verificar que MongoDB esté corriendo
mongod --config "C:\Program Files\MongoDB\Server\7.0\bin\mongod.cfg"
```

### Error: JSON no válido
```bash
# Verificar que no haya error_log en archivos PHP
# Revisar test_json.php primero
```

## 🎯 Para Demostrar al Ingeniero

### 1. **Mostrar Dashboard Principal**
- Filtros interactivos
- Gráficos dinámicos
- Mapa con diferentes tipos de infraestructura

### 2. **Ejecutar Funcionalidades Avanzadas**
- Transacciones simuladas (validaciones + auditoría)
- Validación de integridad (calidad de datos)
- Medición de rendimiento (benchmarking)
- Backup automático

### 3. **Argumentos Técnicos**
- "No son simples consultas SELECT"
- "Implementamos transacciones, validaciones, auditoría"
- "Sistema completo de data quality"
- "Benchmarking automático como MongoDB Atlas"

## 🏆 Características Destacadas

- ✅ **Transacciones ACID simuladas**
- ✅ **Validación de integridad automática**
- ✅ **Sistema de auditoría completo**
- ✅ **Backup y restore automático**
- ✅ **Benchmarking de rendimiento**
- ✅ **Procedimientos almacenados complejos**
- ✅ **Interfaz moderna y responsive**
- ✅ **Autenticación con Google OAuth**

## 📞 Soporte

Si tienes problemas:
1. Verificar que MongoDB esté corriendo
2. Revisar `test_json.php` para conexión
3. Ejecutar `composer install` si faltan dependencias
4. Verificar logs de PHP en XAMPP

¡El sistema está listo para impresionar al ingeniero! 🚀