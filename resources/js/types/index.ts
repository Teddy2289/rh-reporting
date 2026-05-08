// ─── Enums ───────────────────────────────────────────────────────────────────

export enum ContractType {
  CDI = 'cdi',
  CDD = 'cdd',
  Freelance = 'freelance',
  Intern = 'intern',
}

export enum LeaveStatus {
  Pending = 'pending',
  Approved = 'approved',
  Refused = 'refused',
  Cancelled = 'cancelled',
}

export enum LeaveType {
  Annual = 'annual',
  Sick = 'sick',
  Unpaid = 'unpaid',
  Maternity = 'maternity',
  Paternity = 'paternity',
  Special = 'special',
}

export enum SlotType {
  Work = 'work',
  Pause = 'pause',
  Leave = 'leave',
  Holiday = 'holiday',
}

// ─── Auth ─────────────────────────────────────────────────────────────────────

export interface AuthUser {
  id: number
  name: string
  email: string
  roles: string[]
  permissions: string[]
  agent: AgentSummary | null
}

export interface AgentSummary {
  id: number
  full_name: string
  avatar_url: string | null
  department: string | null
  manager_id: number | null
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface AuthResponse {
  access_token: string
  token_type: string
  expires_in: number
  user: AuthUser
}

// ─── Pagination ───────────────────────────────────────────────────────────────

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
  }
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
}

// ─── Department ───────────────────────────────────────────────────────────────

export interface Department {
  id: number
  name: string
  code: string
  color: string | null
  description: string | null
  is_active: boolean
  agents_count?: number
  created_at: string
  updated_at: string
}

export interface DepartmentForm {
  name: string
  code: string
  color?: string | null
  description?: string | null
  is_active?: boolean
}

// ─── Client ───────────────────────────────────────────────────────────────────

export interface Client {
  id: number
  name: string
  code: string
  color: string | null
  contact_email: string | null
  contact_phone: string | null
  notes: string | null
  is_active: boolean
  missions?: Mission[]
}

export interface ClientForm {
  name: string
  code: string
  color?: string | null
  contact_email?: string | null
  contact_phone?: string | null
  notes?: string | null
}

// ─── Mission ─────────────────────────────────────────────────────────────────

export interface Mission {
  id: number
  client_id: number
  name: string
  code: string | null
  description: string | null
  is_active: boolean
  client?: Client
}

export interface MissionForm {
  client_id: number
  name: string
  code?: string | null
  description?: string | null
}

// ─── Agent ────────────────────────────────────────────────────────────────────

export interface Agent {
  id: number
  user_id: number
  department_id: number
  manager_id: number | null
  employee_code: string
  first_name: string
  last_name: string
  full_name: string
  phone: string | null
  avatar_url: string | null
  contract_type: ContractType
  hire_date: string
  contract_end_date: string | null
  weekly_hours: number
  annual_leave_days: number
  is_active: boolean
  department?: Department
  manager?: Agent | null
  user?: { id: number; email: string; name: string }
  planning_slots_count?: number
  leaves_count?: number
}

export interface AgentForm {
  first_name: string
  last_name: string
  email: string
  password?: string
  employee_code: string
  department_id: number
  manager_id?: number | null
  phone?: string | null
  contract_type: ContractType
  hire_date: string
  contract_end_date?: string | null
  weekly_hours: number
  annual_leave_days: number
}

export interface AgentFilters {
  department_id?: number
  manager_id?: number
  active_only?: boolean
  search?: string
  per_page?: number
  page?: number
}

// ─── Leave ────────────────────────────────────────────────────────────────────

export interface Leave {
  id: number
  agent_id: number
  type: LeaveType
  status: LeaveStatus
  date_start: string
  date_end: string
  working_days: number
  reason: string | null
  refusal_reason: string | null
  approved_by: number | null
  approved_at: string | null
  agent?: Agent
  approved_by_user?: AuthUser
}

export interface LeaveForm {
  agent_id: number
  type: LeaveType
  date_start: string
  date_end: string
  reason?: string | null
}

export interface LeaveBalance {
  year: number
  allocated_days: number
  used_days: number
  pending_days: number
  carried_over_days: number
  remaining_days: number
}

export interface LeaveFilters {
  agent_id?: number
  status?: LeaveStatus
  year?: number
  department_id?: number
  per_page?: number
  page?: number
}

// ─── Planning Slot ────────────────────────────────────────────────────────────

export interface PlanningSlot {
  id: number
  agent_id: number
  client_id: number | null
  mission_id: number | null
  date: string
  time_start: string
  time_end: string
  type: SlotType
  note: string | null
  is_confirmed: boolean
  duration_minutes: number
  duration_hours: number
  agent?: Agent
  client?: Client | null
  mission?: Mission | null
}

export interface PlanningSlotForm {
  agent_id: number
  client_id?: number | null
  mission_id?: number | null
  date: string
  time_start: string
  time_end: string
  type: SlotType
  note?: string | null
  is_confirmed?: boolean
}

export interface PlanningFilters {
  agent_id?: number
  department_id?: number
  date_from?: string
  date_to?: string
  year?: number
  month?: number
  date?: string
  client_id?: number
}

export interface GeneratePlanningForm {
  year: number
  agent_id?: number | null
  overwrite?: boolean
}

export interface BulkUpdateForm {
  slot_ids: number[]
  client_id?: number | null
  mission_id?: number | null
}

// ─── Reports ─────────────────────────────────────────────────────────────────

export interface HourReport {
  agent_id: number
  agent_name: string
  period: string
  worked_hours: number
  expected_hours: number
  overtime_hours: number
  details?: HourReportDetail[]
}

export interface HourReportDetail {
  date: string
  worked_minutes: number
  expected_minutes: number
  overtime_minutes: number
}

export interface DashboardStats {
  total_agents: number
  active_agents: number
  pending_leaves: number
  approved_leaves_month: number
  total_hours_month: number
  overtime_hours_month: number
}

export interface PlanningFilters {
  agent_id?: number
  department_id?: number
  date?: string
  date_from?: string
  date_to?: string
  year?: number
  month?: number
  client_id?: number
  slot_ids?: string
}

export interface BulkUpdatePayload {
  slot_ids: number[]
  client_id?: number | null
  mission_id?: number | null
}

export interface GeneratePlanningPayload {
  year: number
  agent_id?: number
  overwrite?: boolean
}

export interface HourLog {
  agent_id: number
  date: string
  worked_minutes: number
  expected_minutes: number
  overtime_minutes: number
}
