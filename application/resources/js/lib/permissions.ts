import { page } from '@inertiajs/svelte'

/** Check if the authenticated user has a given permission */
export function puedeHacer(permiso: string): boolean {
  return (page.props as any).auth?.permisos?.[permiso] === true
}

/** Check if user has any of the given permissions */
export function puedeHacerAlguno(...permisos: string[]): boolean {
  return permisos.some((p) => puedeHacer(p))
}

/** Check if user has all of the given permissions */
export function puedeHacerTodo(...permisos: string[]): boolean {
  return permisos.every((p) => puedeHacer(p))
}
