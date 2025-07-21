#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para procesar datos ferroviarios del MTC
"""

import pandas as pd
import os
import sys

def analizar_estructura():
    """Analiza la estructura del archivo Excel"""
    try:
        print("🚂 === ANALIZANDO ESTRUCTURA FERROVIARIA ===")
        
        # Leer archivo Excel
        excel_file = '../ferroviarias/Infraestructura_ferroviaria_RedFerroviaria_2022-2024.xlsx'
        
        print(f"📂 Leyendo archivo: {excel_file}")
        df = pd.read_excel(excel_file)
        
        print(f"📊 Total de registros: {len(df)}")
        print(f"📋 Total de columnas: {len(df.columns)}")
        
        print("\n🏷️ Columnas encontradas:")
        for i, col in enumerate(df.columns, 1):
            print(f"  {i}. {col}")
        
        print("\n🔍 Primeras 5 filas:")
        print(df.head().to_string())
        
        print("\n📈 Información de datos:")
        print(df.info())
        
        print("\n🔢 Estadísticas básicas:")
        print(df.describe(include='all'))
        
        print("\n❓ Valores nulos por columna:")
        print(df.isnull().sum())
        
        # Mostrar valores únicos de columnas categóricas
        categorical_cols = ['ELECTRIFICACION', 'ESTADO_CONSERVACION', 'TITULARIDAD']
        for col in categorical_cols:
            if col in df.columns:
                print(f"\n🏷️ Valores únicos en {col}:")
                print(df[col].value_counts())
        
        return df
        
    except Exception as e:
        print(f"❌ Error analizando estructura: {e}")
        return None

def convertir_a_csv(df):
    """Convierte DataFrame a CSV limpio"""
    try:
        print("\n🔄 === CONVIRTIENDO A CSV ===")
        
        # Limpiar datos
        df_limpio = df.copy()
        
        # Eliminar filas completamente vacías
        df_limpio = df_limpio.dropna(how='all')
        
        # Rellenar valores NaN con cadena vacía
        df_limpio = df_limpio.fillna('')
        
        # Limpiar espacios en blanco
        for col in df_limpio.columns:
            if df_limpio[col].dtype == 'object':
                df_limpio[col] = df_limpio[col].astype(str).str.strip()
        
        print(f"📊 Registros después de limpieza: {len(df_limpio)}")
        
        # Guardar como CSV
        csv_file = '../data/ferroviarias_limpio.csv'
        os.makedirs('../data', exist_ok=True)
        df_limpio.to_csv(csv_file, index=False, encoding='utf-8')
        
        print(f"✅ CSV creado: {csv_file}")
        
        # Mostrar muestra del CSV
        print("\n🔍 Muestra del CSV creado:")
        print(df_limpio.head(3).to_string())
        
        return csv_file
        
    except Exception as e:
        print(f"❌ Error convirtiendo a CSV: {e}")
        return None

def main():
    """Función principal"""
    print("🚂 === PROCESADOR DE DATOS FERROVIARIOS ===")
    
    # Verificar que existe el archivo Excel
    excel_file = '../ferroviarias/Infraestructura_ferroviaria_RedFerroviaria_2022-2024.xlsx'
    if not os.path.exists(excel_file):
        print(f"❌ No se encontró el archivo: {excel_file}")
        return
    
    # Analizar estructura
    df = analizar_estructura()
    if df is None:
        return
    
    # Convertir a CSV
    csv_file = convertir_a_csv(df)
    if csv_file:
        print(f"\n🎉 ¡Procesamiento completado!")
        print(f"📁 Archivo CSV listo: {csv_file}")
        print("🔄 Siguiente paso: Cargar a MongoDB usando cargar_ferroviarias.php")
    else:
        print("\n❌ Error en la conversión")

if __name__ == "__main__":
    main()