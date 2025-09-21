# Prueba Técnica - Gestión de Usuarios

Esta es una prueba técnica para evaluar habilidades de desarrollo con Laravel. El proyecto consiste en un sistema de gestión de usuarios con funcionalidades específicas que deben ser implementadas.

##  Antes de Empezar

**IMPORTANTE: Lee el archivo `user_requirements_doc.md` antes de comenzar a desarrollar.**

Este archivo contiene todos los requerimientos detallados de la prueba, incluyendo:
- Bugs que deben ser corregidos
- Nuevas funcionalidades a implementar
- Criterios de aceptación

## Instalación y Configuración

### Clonar el repositorio
```bash
git clone git@github.com:nojau/prueba_tecnica.git
cd prueba
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar el entorno
```bash
# Copiar el archivo de configuraci�n
cp .env.example .env

```

### 4. Configurar la base de datos
Edita el archivo `.env` con tu configuración de base de datos, utilizar mysql:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prueba_usuarios
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```


### 5. Ejecutar seeders
```bash
php artisan db:seed
```

### 6. Levantar el servidor de desarrollo
```bash
php artisan serve
```

La aplicación estará disponible en: `http://localhost:8000`

---

## Tips opcionales

Si tienes experiencia con testing automatizado en Laravel, puedes explorar cómo configurar un entorno de pruebas utilizando PHPUnit y un archivo `.env.testing`. No es obligatorio para completar la prueba ni excluyente en la evaluación, pero puede ayudarte a validar tu solución de forma más profesional.

Pista: Investiga sobre el uso de `php artisan test` y la configuración de variables en `.env.testing` para entornos de test.

**🚀 Para Configurar Testing:**
```bash
# Copiar template seguro
cp .env.testing.example .env.testing

# Generar APP_KEY para testing
php artisan key:generate --env=testing
```

---

# **Buena suerte con la prueba! - Equipo de Desarrollo nojau**
