<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    /**
     * Lista paginada de usuarios con su persona y rol asociados.
     */
    public function index(): Response
    {
        $usuarios = User::with(['persona', 'rol'])
            ->orderBy('name')
            ->paginate(15)->withQueryString()->toInertia();

        return Inertia::render('settings/Usuarios/Index', [
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Formulario de creación. Se pasan los roles disponibles y el ID del
     * usuario autenticado para que el frontend pueda deshabilitar el
     * selector de rol cuando el usuario edita su propio perfil.
     */
    public function create(): Response
    {
        return Inertia::render('settings/Usuarios/Form', [
            'usuario'      => null,
            'roles'        => Rol::orderBy('nombre')->get(),
            'auth_user_id' => auth()->id(),
        ]);
    }

    /**
     * Persiste un nuevo usuario junto con su persona.
     *
     * La contraseña es obligatoria en la creación.
     * Ambos modelos se crean dentro de una transacción para garantizar
     * consistencia: si cualquiera de los dos falla, ninguno se guarda.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_identificacion'   => ['required', 'in:CC,CE,TI,PAS,NIT,RC'],
            'numero_identificacion' => ['required', 'string', 'max:20'],
            'nombres'               => ['required', 'string', 'max:100'],
            'apellidos'             => ['nullable', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono'              => ['nullable', 'string', 'max:20'],
            'celular'               => ['nullable', 'string', 'max:20'],
            'rol_id'                => ['required', 'exists:roles,id'],
            'password'              => ['required', 'confirmed', Password::min(8)],
        ]);

        DB::transaction(function () use ($validated): void {
            $persona = Persona::create([
                'tipo_identificacion'   => $validated['tipo_identificacion'],
                'numero_identificacion' => $validated['numero_identificacion'],
                'nombres'               => $validated['nombres'],
                'apellidos'             => $validated['apellidos'] ?? null,
                'email'                 => $validated['email'],
                'telefono'              => $validated['telefono'] ?? null,
                'celular'               => $validated['celular'] ?? null,
                'pais'                  => 'Colombia',
            ]);

            User::create([
                'name'       => trim("{$validated['nombres']} " . ($validated['apellidos'] ?? '')),
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'persona_id' => $persona->id,
                'rol_id'     => $validated['rol_id'],
                'activo'     => true,
            ]);
        });

        return redirect()->route('settings.usuarios.index')
            ->with('flash', ['success' => 'Usuario creado correctamente.']);
    }

    /**
     * Formulario de edición. Carga persona y rol antes de pasarlos al
     * frontend para evitar queries N+1 al serializar el modelo.
     */
    public function edit(User $usuario): Response
    {
        $usuario->load(['persona', 'rol']);

        return Inertia::render('settings/Usuarios/Form', [
            'usuario'      => $usuario,
            'roles'        => Rol::orderBy('nombre')->get(),
            'auth_user_id' => auth()->id(),
        ]);
    }

    /**
     * Actualiza los datos de un usuario existente.
     *
     * Reglas de negocio:
     *   - Nadie puede cambiar su propio rol. Si el usuario autenticado
     *     coincide con el usuario a editar, se descarta el rol_id recibido
     *     y se conserva el actual. La UI ya deshabilita el campo, pero esta
     *     validación server-side es la línea de defensa real contra bypasses.
     *   - La contraseña es opcional; si llega vacía se conserva la existente.
     *   - Si por alguna razón el usuario no tiene persona asociada (datos
     *     inconsistentes), se crea una nueva y se vincula en lugar de fallar.
     *
     * Se carga explícitamente la relación persona antes de validar para
     * evitar que route model binding entregue un modelo sin relaciones y
     * el bloque condicional la salte silenciosamente.
     */
    public function update(Request $request, User $usuario): RedirectResponse
    {
        // Carga explícita necesaria: route model binding no eager-load relaciones.
        $usuario->load('persona');

        $validated = $request->validate([
            'tipo_identificacion'   => ['required', 'in:CC,CE,TI,PAS,NIT,RC'],
            'numero_identificacion' => ['required', 'string', 'max:20'],
            'nombres'               => ['required', 'string', 'max:100'],
            'apellidos'             => ['nullable', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'telefono'              => ['nullable', 'string', 'max:20'],
            'celular'               => ['nullable', 'string', 'max:20'],
            'rol_id'                => ['required', 'exists:roles,id'],
            // nullable porque el checkbox puede no enviarse cuando está desmarcado.
            'activo'                => ['nullable', 'boolean'],
            'password'              => ['nullable', 'confirmed', Password::min(8)],
        ]);

        // Nadie puede cambiar su propio rol (ver docblock).
        if ($request->user()->id === $usuario->id) {
            $validated['rol_id'] = $usuario->rol_id;
        }

        DB::transaction(function () use ($validated, $usuario): void {
            if ($usuario->persona) {
                $usuario->persona->update([
                    'tipo_identificacion'   => $validated['tipo_identificacion'],
                    'numero_identificacion' => $validated['numero_identificacion'],
                    'nombres'               => $validated['nombres'],
                    'apellidos'             => $validated['apellidos'] ?? null,
                    'email'                 => $validated['email'],
                    'telefono'              => $validated['telefono'] ?? null,
                    'celular'               => $validated['celular'] ?? null,
                ]);
            } else {
                // Rama defensiva: no debería ocurrir en condiciones normales,
                // pero evita perder los datos del formulario si la persona
                // fue eliminada o nunca se creó correctamente.
                $persona = Persona::create([
                    'tipo_identificacion'   => $validated['tipo_identificacion'],
                    'numero_identificacion' => $validated['numero_identificacion'],
                    'nombres'               => $validated['nombres'],
                    'apellidos'             => $validated['apellidos'] ?? null,
                    'email'                 => $validated['email'],
                    'telefono'              => $validated['telefono'] ?? null,
                    'celular'               => $validated['celular'] ?? null,
                    'pais'                  => 'Colombia',
                ]);
                $usuario->update(['persona_id' => $persona->id]);
            }

            $userUpdate = [
                'name'   => trim("{$validated['nombres']} " . ($validated['apellidos'] ?? '')),
                'email'  => $validated['email'],
                'rol_id' => $validated['rol_id'],
                // Cast explícito: 'activo' llega como nullable boolean desde
                // Inertia; si no se envía (checkbox desmarcado sin valor),
                // se conserva el valor actual del modelo.
                'activo' => (bool) ($validated['activo'] ?? $usuario->activo),
            ];

            // Solo se rehashea si se envió una contraseña nueva.
            if (!empty($validated['password'])) {
                $userUpdate['password'] = Hash::make($validated['password']);
            }

            $usuario->update($userUpdate);
        });

        return redirect()->route('settings.usuarios.index')
            ->with('flash', ['success' => 'Usuario actualizado correctamente.']);
    }

    /**
     * Desactiva un usuario en lugar de eliminarlo físicamente.
     *
     * El borrado lógico preserva la integridad referencial con registros
     * históricos (ventas, movimientos, auditoría) que referencian al usuario.
     */
    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        abort_if(
            $request->user()->id === $usuario->id,
            403,
            'No puedes desactivar tu propia cuenta.'
        );

        $usuario->update(['activo' => false]);

        return redirect()->route('settings.usuarios.index')
            ->with('flash', ['success' => 'Usuario desactivado correctamente.']);
    }
}