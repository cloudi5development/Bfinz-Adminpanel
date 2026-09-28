# Bfinz Admin Panel

This repo is being repurposed from a training-institute admin panel into the backend + admin panel for **Bfinz**, a free Indian personal-finance information app (gold/silver/forex/fuel rates, bank product comparisons, goal planning, calculators, RBI/safety content, banking utilities).

Full build spec: [`docs/BUILD_SPEC.md`](docs/BUILD_SPEC.md) — **read it before starting any Bfinz feature work**, including the amendment note at the top (this repo keeps its existing flat Laravel layout, hand-built `Backend` admin controllers, and `{status, data, message}` API envelope rather than the spec's original Filament/monorepo assumptions).

Current build status, decisions, and open questions: [`docs/PROGRESS.md`](docs/PROGRESS.md).

The legacy training-institute tables/controllers (courses, departments, events, blogs, testimonials, etc.) are still present and untouched — they are out of scope for Bfinz work unless explicitly asked about.
