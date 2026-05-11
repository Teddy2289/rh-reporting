import {
    createRouter,
    createWebHistory,
    type RouteRecordRaw,
} from "vue-router";
import { useAuthStore } from "@/stores/auth";

// ─── Lazy-loaded views ────────────────────────────────────────────────────────

const LoginView = () => import("@/views/auth/LoginView.vue");
const DashboardView = () => import("@/views/DashboardView.vue");
const ForbiddenView = () => import("@/views/ForbiddenView.vue");

// Agents
const AgentsListView = () => import("@/views/agents/AgentsListView.vue");
const AgentDetailView = () => import("@/views/agents/AgentDetailView.vue");
const AgentFormView = () => import("@/components/agents/AgentFormView.vue");

// Planning
const PlanningView = () => import("@/views/planning/PlanningView.vue");
const PlanningGenerateView = () =>
    import("@/views/planning/PlanningGenerateView.vue");

// Leaves
const LeavesListView = () => import("@/views/leaves/LeavesListView.vue");
const LeaveRequestView = () => import("@/views/leaves/LeaveRequestView.vue");

// Departments
const DepartmentsView = () => import("@/views/departments/DepartmentsView.vue");

// Clients & Missions
const ClientsView = () => import("@/views/clients/ClientsView.vue");
const MissionsView = () => import("@/views/missions/MissionsView.vue");

// Reports
const ReportsView = () => import("@/views/reports/ReportsView.vue");

// Activity Logs
const ActivityLogView = () => import("@/views/activity-logs/Activitylogview.vue");

// ─── Route definitions ────────────────────────────────────────────────────────

const routes: RouteRecordRaw[] = [
    // Public
    {
        path: "/login",
        name: "login",
        component: LoginView,
        meta: { requiresGuest: true },
    },
    {
        path: "/forbidden",
        name: "forbidden",
        component: ForbiddenView,
    },

    // Protected
    {
        path: "/",
        redirect: "/dashboard",
        meta: { requiresAuth: true },
    },
    {
        path: "/dashboard",
        name: "dashboard",
        component: DashboardView,
        meta: { requiresAuth: true },
    },

    // Agents
    {
        path: "/agents",
        name: "agents",
        component: AgentsListView,
        meta: { requiresAuth: true, roles: ["admin", "rh", "manager"] },
    },
    {
        path: "/agents/new",
        name: "agents.create",
        component: AgentFormView,
        meta: { requiresAuth: true, roles: ["admin", "rh"] },
    },
    {
        path: "/agents/:id",
        name: "agents.show",
        component: AgentDetailView,
        meta: { requiresAuth: true },
    },
    {
        path: "/agents/:id/edit",
        name: "agents.edit",
        component: AgentFormView,
        meta: { requiresAuth: true, roles: ["admin", "rh"] },
    },

    // Planning
    {
        path: "/planning",
        name: "planning",
        component: PlanningView,
        meta: { requiresAuth: true },
    },
    {
        path: "/planning/generate",
        name: "planning.generate",
        component: PlanningGenerateView,
        meta: { requiresAuth: true, roles: ["admin", "rh"] },
    },

    // Leaves
    {
        path: "/leaves",
        name: "leaves",
        component: LeavesListView,
        meta: { requiresAuth: true },
    },
    {
        path: "/leaves/request",
        name: "leaves.request",
        component: LeaveRequestView,
        meta: { requiresAuth: true },
    },

    // Departments
    {
        path: "/departments",
        name: "departments",
        component: DepartmentsView,
        meta: { requiresAuth: true, roles: ["admin", "rh"] },
    },

    // Clients
    {
        path: "/clients",
        name: "clients",
        component: ClientsView,
        meta: { requiresAuth: true, roles: ["admin", "rh"] },
    },

    { path: '/activity-logs', name: 'activity-logs', component: ActivityLogView },

    // Missions
    {
        path: "/missions",
        name: "missions",
        component: MissionsView,
        meta: { requiresAuth: true, roles: ["admin", "rh"] },
    },

    // Reports
    {
        path: "/reports",
        name: "reports",
        component: ReportsView,
        meta: { requiresAuth: true, roles: ["admin", "rh", "manager"] },
    },

    // Catch-all
    {
        path: "/:pathMatch(.*)*",
        redirect: "/dashboard",
    },
];

// ─── Router instance ─────────────────────────────────────────────────────────

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});

// ─── Navigation guards ────────────────────────────────────────────────────────

router.beforeEach((to, _from, next) => {
    const auth = useAuthStore();

    // Route publique pour invités seulement
    if (to.meta.requiresGuest && auth.isAuthenticated) {
        return next({ name: "dashboard" });
    }

    // Route protégée
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return next({ name: "login", query: { redirect: to.fullPath } });
    }

    // Vérification des rôles
    const allowedRoles = to.meta.roles as string[] | undefined;
    if (allowedRoles?.length && !auth.hasAnyRole(...allowedRoles)) {
        return next({ name: "forbidden" });
    }

    next();
});

export default router;
