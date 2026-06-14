# Mesh Photography — Implementation Prompts

> **Version:** 1.0  
> **Date:** 2026-06-14

---

## Overview

This document provides an index and usage guide for the two detailed AI-agent implementation prompts that accompany this planning suite:

| File | Target | Description |
|---|---|---|
| [`docs/prompts/backend.md`](prompts/backend.md) | PHP Developer / AI Coding Agent | Full implementation prompt for the PHP REST API backend |
| [`docs/prompts/frontend.md`](prompts/frontend.md) | React Developer / AI Coding Agent | Full implementation prompt for the React SPA frontend |

---

## How to Use These Prompts

Each prompt file is written as a self-contained briefing document that can be handed directly to:

1. **An AI coding assistant** (Claude Code, GitHub Copilot Workspace, Cursor, etc.) as a project-level system prompt or context document
2. **A human developer** as a complete technical specification for starting implementation from scratch

### For AI Coding Assistants

Load the relevant prompt file at the start of a new session:
```
"Here is the full implementation specification for this project. Please implement it according to the instructions."
[paste content of backend.md or frontend.md]
```

Or reference it as a project context file if your tooling supports that.

### For Human Developers

Read the prompt end-to-end before starting. Each section builds on the previous. Follow the prescribed order to avoid dependency issues.

---

## Separation of Concerns

The prompts are designed so that **backend and frontend can be implemented independently** by different engineers:

### Backend Engineer receives:
- `docs/prompts/backend.md`
- Source of truth: `docs/blueprint.md`, `docs/04-backend-architecture.md`, `docs/06-api-design.md`
- Deliverable: A running PHP API at `http://localhost:8000/api/v1/` that passes the integration test suite

### Frontend Engineer receives:
- `docs/prompts/frontend.md`
- Source of truth: `docs/05-frontend-architecture.md`, `docs/06-api-design.md`, `docs/blueprint.md` (design system section)
- Deliverable: A running React SPA at `http://localhost:5173/` that connects to the backend API

### Coordination Points

The two engineers share:
1. **API contract** (`docs/06-api-design.md`) — agreed response shapes and endpoint paths
2. **TypeScript types** (`frontend/src/types/`) — both teams reference the same model definitions
3. **Brand tokens** — design system from `docs/blueprint.md` §21 (colors, fonts, spacing)

The frontend can start against MSW (Mock Service Worker) mock data until the backend endpoints are ready.

---

## Prompt Update Policy

If the API contract or architecture changes during implementation:
1. Update `docs/06-api-design.md` first (single source of truth)
2. Update `frontend/src/types/` to match
3. Update both prompt files if the change affects implementation approach
4. Communicate the change to both workstreams before they implement against the old spec

---

## Document Index

| Document | Purpose |
|---|---|
| `docs/blueprint.md` | Original master technical blueprint |
| `docs/01-business_requirement_docs.md` | Business objectives, user stories, functional requirements |
| `docs/02-build_blueprint.md` | Technology stack, ADRs, directory structure, coding standards |
| `docs/03-system-architecture.md` | High-level system design, data flows, infrastructure |
| `docs/04-backend-architecture.md` | PHP MVC architecture, JWT, middleware, services, models |
| `docs/05-frontend-architecture.md` | React SPA architecture, routing, components, hooks |
| `docs/06-api-design.md` | Complete REST API endpoint reference with schemas |
| `docs/07-implementation-plan.md` | Phased task breakdown for backend and frontend |
| `docs/08-delivery_roadmap.md` | Timeline, milestones, release strategy |
| `docs/09-implementation_prompts.md` | This document — prompt index and usage guide |
| `docs/prompts/backend.md` | Full backend implementation prompt |
| `docs/prompts/frontend.md` | Full frontend implementation prompt |
