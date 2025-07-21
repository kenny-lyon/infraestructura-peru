#!/bin/bash
# Script de deployment para producción

echo "🚀 Desplegando a producción..."

# Crear carpeta de deployment
mkdir -p deployment

# Copiar archivos esenciales
cp dashboard_produccion.html deployment/index.html
cp api_produccion.php deployment/
cp datos_produccion.json deployment/
cp config.json deployment/

# Crear .htaccess para Apache
echo "RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [QSA,L]" > deployment/.htaccess

echo "✅ Archivos listos en carpeta deployment/"
echo "📦 Subir contenido de deployment/ a tu hosting"
