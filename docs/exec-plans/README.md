# Execution Plans

Working documents for multi-step changes (upgrades, refactorings, feature arcs) that span more than one PR.

- `active/` — plans currently being executed; each plan lists its remaining steps so any agent or human can resume it.
- `completed/` — finished plans, kept for the decision trail.

Create a plan as a Markdown file with: goal, constraints, ordered steps with completion state, and links to the issues/PRs that implement each step. Update the plan in the same PR that completes a step. Point-in-time architecture facts belong in `../ARCHITECTURE.md`, decisions in `../adr/` — plans only reference them.
