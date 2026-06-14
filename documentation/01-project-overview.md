# BusinessCmd POS — Project Overview

## Purpose

A Point of Sale (POS) web application for small and medium Colombian businesses (mipymes), administered by the business's own staff. Focused on sellers and administrative personnel managing clients, products, providers, transactions, inventory lifecycle, and financial reporting — including DIAN-compliant electronic invoicing.

---

## Stack Evaluation

**Laravel 12 + Svelte 5 via Inertia.js is appropriate for this system.** Reasoning:

| Concern | Fit |
|---|---|
| Complex business logic (pricing, taxes, DIAN) | Laravel services/actions — excellent |
| Relational data (transactions, products, people) | Eloquent ORM — excellent |
| Authentication + role-based access | Fortify + Gates/Policies — built-in |
| Async operations (PDF, DIAN submission, email) | Laravel Queues — built-in |
| Reactive POS interface (cart, stock) | Svelte 5 runes — excellent |
| Typed server-to-client data contract | Inertia.js props + TypeScript — seamless |
| Real-time stock sync (multiple cashiers) | Laravel Reverb (WebSocket) — add-on |

**Additional packages needed beyond the base starter kit:**

| Package | Purpose |
|---|---|
| `barryvdh/laravel-dompdf` | PDF invoice generation |
| `spatie/laravel-permission` | Role and permission management |
| `spatie/laravel-activitylog` | Audit trail |
| `spatie/laravel-query-builder` | Filterable/sortable API lists |
| `laravel/reverb` | WebSockets for real-time POS updates |
| `intervention/image` | Product image handling |
| `maatwebsite/laravel-excel` | Excel report exports |

---

## System Scope

### In scope
- Product catalog with inventory and lifecycle tracking
- Point of sale (cart → invoice → payment)
- DIAN-compliant electronic invoicing (facturas, notas crédito/débito)
- Customer management with accounts receivable (cartera)
- Supplier management with purchase orders and goods receipt
- Cash register sessions (turnos de caja) and multi-payment-method support
- Expense tracking
- Reports: sales, inventory, cartera, DIAN fiscal reports for mipymes
- Role-based access: Administrador, Vendedor, Cajero, Bodeguero

### Out of scope (v1)
- Multi-store / multi-branch
- Integration with external accounting software (Siigo, World Office)
- DIAN direct API submission (use certified operator middleware)
- Mobile native app (PWA via browser is sufficient)
- Payroll module

---

## User Roles

| Role | Description |
|---|---|
| **Administrador** | Full access. Configures system, manages users, views all reports |
| **Vendedor** | Creates sales, manages clients, views own transactions |
| **Cajero** | Opens/closes register, processes payments, prints receipts |
| **Bodeguero** | Manages inventory, receives goods, handles stock adjustments |

---

## Key Technical Decisions

- **Language**: Spanish for all UI and domain model names (consistent with Colombian business context)
- **Currency**: Colombian Peso (COP) — all monetary values stored as integers (centavos) or `decimal(15,2)`
- **Database**: PostgreSQL (better for financial data; supports partial indexes, JSONB for DIAN XML storage)
- **Electronic invoicing**: Generate CUFE, XML, and QR locally; submit to DIAN via a certified Operador Habilitado (e.g., Bizagi, Allegra, or custom middleware)
- **Audit trail**: All mutations on financial entities logged via `spatie/laravel-activitylog`
- **Soft deletes**: Enabled on all financial entities (facturas, productos, clientes, proveedores) — Colombian law requires retaining records
