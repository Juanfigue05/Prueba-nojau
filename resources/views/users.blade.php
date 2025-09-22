<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Users - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                body { font-family: 'Instrument Sans', system-ui, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                .container { max-width: 1200px; margin: 0 auto; }
                h1 { color: #333; margin-bottom: 30px; }
                table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
                th { background: #f8f9fa; font-weight: 600; color: #555; }
                tr:hover { background: #f8f9fa; }
                .user-count { margin-bottom: 20px; font-size: 16px; color: #666; }
                .btn-create {
                    background: #007bff;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: 500;
                    transition: background 0.3s;
                }
                .btn-create:hover { background: #0056b3; }
                .btn-edit {
                    background: #28a745;
                    color: white;
                    padding: 5px 10px;
                    text-decoration: none;
                    border-radius: 3px;
                    font-size: 12px;
                    margin-right: 5px;
                }
                .btn-edit:hover { background: #218838; }
                .btn-delete {
                    background: #dc3545;
                    color: white;
                    padding: 5px 10px;
                    border: none;
                    border-radius: 3px;
                    font-size: 12px;
                    cursor: pointer;
                }
                .btn-delete:hover { background: #c82333; }
            </style>
        @endif
    </head>
    <body>
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1 style="margin-bottom: 0;">Lista de Usuarios</h1>
                <a href="{{ route('users.create') }}" class="btn-create">
                    Crear usuarios
                </a>
            </div>

            <div class="user-count">
                Total de usuarios: {{ $users->count() }}
            </div>

            <table class="table">
                <thead class="table table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>DNI</th>
                        <th>Fecha de registro</th>
                        <th>Fecha de Edicion</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>{{ $user->dni ?? 'N/A' }}</td>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn-edit">Editar</a>
                                <form style="display: inline;" method="POST" action="{{ route('users.destroy', $user->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                No hay usuarios registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>