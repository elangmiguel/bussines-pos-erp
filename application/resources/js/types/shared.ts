import type { User, Configuracion, TurnoCaja } from './models'

export interface PageProps {
  auth: {
    user: User | null
    permisos: Record<string, boolean>
  }
  empresa: Configuracion | null
  turno_activo: { id: number; caja_id: number; apertura: string } | null
  flash: {
    success?: string
    error?: string
    warning?: string
  }
  name: string
  sidebarOpen: boolean
}
