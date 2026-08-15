# 02 - Team Structure & Responsibilities

## 1. Team Hierarchy

```
Coordinator (tier 1)
├── Backend Specialist   (tier 2)  ── Laravel Specialist, PHP Specialist
├── Frontend Specialist  (tier 2)  ── jQuery, Vue, React, CSS, Bootstrap 5,
│                                     Tailwind 5, UI-UX Designer
├── DB Admin             (tier 2)  ── MySQL, MariaDB, MS SQL Server
├── App Designer
├── Tester
├── Bug Fixer
└── Doc Writer
```

Only the **Coordinator** may communicate with the user. Every other agent
communicates exclusively over the A2A mesh (see `docs/04-communication-a2a.md`).

## 2. Roles at a Glance

| Role | Tier | Reports To | Core Deliverables |
|------|------|------------|-------------------|
| Coordinator | 1 | User | Task decomposition, dispatch, monitoring, final results |
| Backend Specialist | 2 | Coordinator | Backend architecture, API/auth/validation/security review, backend completion |
| Laravel Specialist | — | Backend Specialist | Laravel features, migrations, models, controllers, APIs, queues |
| PHP Specialist | — | Backend Specialist | PHP-level design, code review, runtime/composer analysis |
| Frontend Specialist | 2 | Coordinator | Frontend architecture, tech selection, integration review |
| jQuery Specialist | — | Frontend Specialist | jQuery/AJAX, event handling, DOM manipulation, plugins |
| Vue Specialist | — | Frontend Specialist | Vue components, state, forms, routing |
| React Specialist | — | Frontend Specialist | React components, hooks, state, API integration |
| CSS Specialist | — | Frontend Specialist | CSS architecture, layouts, responsive, typography |
| Bootstrap 5 Specialist | — | Frontend Specialist | Bootstrap layouts/components/utilities |
| Tailwind 5 Specialist | — | Frontend Specialist | Tailwind config, utilities, component patterns |
| UI-UX Designer | — | Frontend Specialist | User flows, IA, layouts, states, UI specs, design review |
| DB Admin | 2 | Coordinator | Database architecture, schema/query/index review, security |
| MySQL Specialist | — | DB Admin | Schemas, indexes, query optimization, slow-query analysis |
| MariaDB Specialist | — | DB Admin | MariaDB behavior, SQL differences, engine/config |
| MS SQL Server Specialist | — | DB Admin | T-SQL, execution plans, locking/deadlocks, stored procs |
| App Designer | — | Coordinator | Application architecture, modules, workflows, roles/permissions, boundaries |
| Tester | — | Coordinator | Test plans/cases, testing, defect identification, fix verification |
| Bug Fixer | — | Coordinator | Defect reproduction, root cause, fixes, regression prevention |
| Doc Writer | — | Coordinator | Architecture/API/DB/config docs, troubleshooting, accuracy review |

## 3. Communication Rules

1. **Only Coordinator ↔ User.** Specialists never communicate with the user
   directly (e.g. the Laravel Specialist is explicitly forbidden from direct
   user contact).
2. **Agents may talk to each other** over A2A as required by their role
   (e.g. Laravel Specialist ↔ MySQL Specialist for schema/query needs).
3. **The Coordinator filters what reaches the user** — internal agent chatter is
   never exposed verbatim; the Coordinator summarizes.
4. **Never invent undocumented behavior** — the Doc Writer asks the responsible
   specialist when information is missing.

## 4. Coordinator Duties (abridged from spec)

- Receive and understand all user instructions; ask clarifying questions when ambiguous.
- Interpret the request and convert it into one or more executable tasks.
- Maintain the global task list; break large tasks into subtasks.
- Identify required agent capabilities and dispatch tasks to the matching agents.
- Monitor agent and task status; track dependencies; coordinate parallel and sequential work.
- Ensure no agent is idle by dispatching work within their specialty; **never dispatch a new task to an agent that is still working**.
- Collect results; resolve conflicts; reassign work on failure; ensure every task has an owner.
- Report progress and present final results to the user.

## 5. Tester ↔ Bug Fixer Workflow (bug loop)

```
Tester
  │  Bug Report (documented defect)
  ▼
Bug Fixer
  │  root cause → route to owner:
  │    Backend problem   → Backend Specialist
  │    Frontend problem  → Frontend Specialist
  │    DB problem        → DB Admin
  │    Architecture      → App Designer
  ▼
Fix
  ▼
Tester  (regression gate: re-runs affected suite + full suite)
  ├── PASS → verified, done
  └── FAIL → reopened → Bug Fixer (regression loop)
```

- The **Tester does not fix bugs**; it identifies and documents them.
- The **Bug Fixer** implements fixes, avoids unnecessary changes and regressions,
  then requests Tester verification.
- On fix submit the **regression gate auto-triggers**: affected suite first, full
  suite second; only then is the defect marked `verified`.

### Defect Report contract (Tester → Bug Fixer)

The Tester emits structured defects (a delimited JSON block in its output) so the
bug loop can be automated:

```json
{
  "defectId": "def_001",
  "title": "Login fails for unverified email",
  "severity": "high",
  "component": "backend",
  "stepsToReproduce": ["visit /login", "submit unverified email"],
  "expected": "Redirect to verification notice",
  "actual": "500 error",
  "evidence": { "suite": "AuthTest", "output": "logs/tasks/def_001.out" },
  "status": "open"
}
```

`component` is how Bug Fixer routes to the correct owner:
backend / frontend / db / config / architecture.

Test execution is **delegated to the Tester CLI agent** (it runs project tests in
its own environment); frameworks are auto-detected from project files and injected
into its prompt.

## 6. Cross-Team Coordination Points

| From | To | Why |
|------|----|-----|
| Backend Specialist | DB Admin | Schema/query requirements |
| Backend Specialist | Frontend Specialist | API/data contracts |
| Backend Specialist | Tester / Bug Fixer | Backend testing / defects |
| Frontend Specialist | Backend Specialist | Endpoint integration |
| Frontend Specialist | UI-UX Designer | Design ↔ implementation fidelity |
| DB Admin | Backend Specialist | Query optimization, migrations |
| App Designer | Backend / Frontend / DB Admin | Architecture consistency |
| Doc Writer | Every specialist | Accurate documentation |
