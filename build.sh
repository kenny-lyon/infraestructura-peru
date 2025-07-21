#!/bin/bash

echo "🚀 Preparando archivos para Docker build..."

# Limpiar archivos innecesarios para producción
echo "🧹 Limpiando archivos de desarrollo..."
rm -f migrar_a_atlas.php

echo "✅ Preparación completada - Docker se encargará del resto!"