import pandas as pd

# Definir columnas a conservar para cada dataset
puertos_cols = [
    'NOMBRE_TERMINAL', 'LOCALIDAD', 'TIPO_TERMINAL', 'ESTADO', 'ADMINISTRADOR',
    'LATITUD', 'LONGITUD', 'USO', 'TRAFICO', 'ACTIVIDAD', 'AMBITO', 'ALCANCE', 'TITULARIDAD'
]
aeropuertos_cols = [
    'NOMBRE', 'TIPO_AERODROMO', 'CODIGO_OACI', 'ESTADO', 'ADMINISTRADOR',
    'LATITUD', 'LONGITUD', 'ESCALA', 'JERARQUIA', 'TITULARIDAD', 'DEPARTAMENTO', 'PROVINCIA', 'DISTRITO'
]

# Limpiar puertos
puertos = pd.read_csv('Infraestructura_portuaria2022-2024.csv', sep=';', dtype=str, encoding='latin1')
puertos_limpio = puertos[puertos_cols]
puertos_limpio.to_csv('puertos_limpio.csv', index=False, encoding='utf-8')

# Limpiar aeropuertos
aeropuertos = pd.read_csv('Infraestructura_aeroportuaria_aerodromos_2022-2024.csv', sep=';', dtype=str, encoding='latin1')
aeropuertos_limpio = aeropuertos[aeropuertos_cols]
aeropuertos_limpio.to_csv('aeropuertos_limpio.csv', index=False, encoding='utf-8')

print('¡Archivos limpios generados: puertos_limpio.csv y aeropuertos_limpio.csv!') 