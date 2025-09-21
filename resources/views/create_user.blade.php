<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  </head>
  <body>
    <div class="container">
        <h1 class="mt-4">Crear Usuario</h1>
        <form action="{{ route('users.store') }}" method="POST" class="mt-4">
            @csrf
            <div class="mb-2">
                <label for="name">Nombre</label>
                <input type="text" name="name" class="form-control" id="name" >
            </div>
            
            <div class="mb-2">
                <label for="email">Correo</label>
                <input type="email" name="email" class="form-control" id="email">
            </div>
            <div class="mb-2">
                <label for="phone">Teléfono</label>
                <input type="text" name="phone" class="form-control" id="phone" >
            </div>
            <div class="mb-2">
                <label for="dni">DNI</label>
                <input type="text" name="dni" class="form-control" id="dni" >
            </div>
            <div class="mb-2">
                <label for="password">Contraseña</label>
                <input type="password" name="password" class="form-control" id="password" >
            </div>
            <div class="mb-2">
                <label for="created_at">Fecha de registro</label>
                <input type="datetime-local" name="created_at" class="form-control" id="created_at" readonly>
            </div>
            <div class="mb-2">
                <label for="updated_at">Última actualización</label>
                <input type="datetime-local" name="updated_at" class="form-control" id="updated_at" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Crear</button>
            <a class="btn btn-warning" href="{{route('users.index')}}">Volver</a>
        </form>
    </div>


    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>