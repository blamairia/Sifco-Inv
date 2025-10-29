# 📦 CartonStock MVP - Laravel 11 + Filament v4

**Status:** Slice 2 Complete ✅ | Ready for Slice 3 ⚡

## 🎯 Project Overview

CartonStock is an on-premise inventory management system for cardboard/box factories built on **Laravel 11** with **Filament v4** admin UI.

- **Tech Stack:** Laravel 11, Filament v4, MySQL 8.0+, PHP 8.2.28
- **UI Language:** French (Français)
- **Current Phase:** Slice 2 (Stock Storage) completed with architectural refactor
- **Next Phase:** Slice 3 (Receipt/Stock-In operations)

---

## 📚 Documentation

### Quick Start
- **[SOLUTION_SUMMARY.md](SOLUTION_SUMMARY.md)** - Executive summary (15 min read) ⭐ START HERE
- **[ARCHITECTURE_STATUS.md](ARCHITECTURE_STATUS.md)** - Status overview & next steps (10 min read)

### Detailed Guides
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Complete design documentation (20 min read)
- **[STRUCTURAL_SOLUTION.md](STRUCTURAL_SOLUTION.md)** - Problem & solution analysis (20 min read)
- **[VISUAL_ARCHITECTURE.md](VISUAL_ARCHITECTURE.md)** - Diagrams & query patterns (15 min read)
- **[Plan.md](Plan.md)** - Project roadmap & TODO items

### For Developers
- Models: `/app/Models/` (12 models with relationships)
- Migrations: `/database/migrations/` (14 migrations, all tested)
- Resources: `/app/Filament/Resources/` (11 Filament resources)

---

## 🏗️ Architecture: Three-Tier Roll Management

### The Problem Solved
Original design confused **Product specifications** with **Individual inventory items**. This architecture refactor properly separates them.

### The Solution: Three Tiers

```
1️⃣ PaperRollType (Attributes)
   └─ KL: 120 GSM, 1200mm laise, 500kg/roll
      TLB: 80 GSM, 1000mm laise, 400kg/roll
      etc.

2️⃣ RollSpecification (Receive-able Combination) ⭐ KEY
   └─ Product + Type + Supplier + Price
      "KRAFT 120 from Supplier A @ 450 DA"
      "KRAFT 120 from Supplier B @ 420 DA"
      ...multiple per product

3️⃣ Roll (Individual Inventory)
   └─ EAN='978...001' | qty=500kg | in_stock
      EAN='978...002' | qty=500kg | in_stock
      Each unique, each trackable
```

**Result:** Rolls are uniquely identified by EAN-13, quantities grouped by specification attributes, receipts unified in one workflow.

---

## 📊 Current Status

### ✅ Completed
- **Slice 1:** Core master data (Products, Warehouses, Suppliers)
- **Slice 2:** Stock storage structure with architectural refactor
  - Units, Categories, Subcategories
  - PaperRollTypes (KL, TLB, TLM, FL)
  - StockLevels & Rolls (with EAN-13)
  - **NEW:** RollSpecification (fixes architecture)
  - **NEW:** Receipt & ReceiptItem (infrastructure)

### 🔄 In Progress
- Slice 3: Receipts (Stock In) - Ready to implement
  - RollSpecificationResource (UI to configure)
  - ReceiptResource (main receipt workflow)

### ⏳ Pending
- Slice 4: Stock movement
- Slice 5: Manual adjustments
- Slice 6: Dashboard & alerts
- Slice 7: Valuation & CSV export

---

## 💻 Getting Started

### Prerequisites
- PHP 8.2.28+
- MySQL 8.0+
- Composer
- Laravel 11

### Installation
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

### Access Admin Panel
- **URL:** `http://127.0.0.1:8000/admin`
- **Email:** `admin@cartonstock.dz`
- **Password:** `admin123`

---

## 🗄️ Database Schema

### Core Tables (14 total)

**Master Data:**
- `products` - Product master data
- `warehouses` - Storage locations (including PRODUCTION_CONSUMED system warehouse)
- `suppliers` - Vendor information
- `units` - Measurement units (kg, pcs, rolls, L)
- `categories` - Product categories (Papiers, Consommables, Produits Finis)
- `subcategories` - Sub-categories
- `paper_roll_types` - Paper types (KL, TLB, TLM, FL)

**Inventory:**
- `stock_levels` - Qty per product per warehouse
- `rolls` - Individual rolls with EAN-13 tracking
- `roll_specifications` - Define receive-able combinations
- `receipts` - Stock-in master record
- `receipt_items` - Receipt line items

**Framework:**
- `users` - System users
- `cache`, `jobs` - Laravel framework

---

## 🎨 Filament Resources (11 total)

| Resource | Status | Purpose |
|----------|--------|---------|
| Products | ✅ | Manage products |
| Warehouses | ✅ | Manage warehouses |
| Suppliers | ✅ | Manage suppliers |
| Units | ✅ | Manage measurement units |
| Categories | ✅ | Manage categories |
| Subcategories | ✅ | Manage subcategories |
| PaperRollTypes | ✅ | Manage paper types |
| StockLevels | ✅ | View stock by warehouse |
| Rolls | ✅ | View individual rolls |
| RollSpecifications | 🔄 | Admin setup (Slice 3) |
| Receipts | 🔄 | Receipt workflow (Slice 3) |

---

## 🚀 Next Steps (Slice 3)

### Phase 1: Setup (2-3 hrs)
- [ ] Configure RollSpecificationResource
- [ ] Add sample specifications
- [ ] Test admin UI

### Phase 2: Receipt Entry (3-4 hrs)
- [ ] Configure ReceiptResource
- [ ] Implement ReceiptItem repeater
- [ ] Test receipt creation

### Phase 3: Processing (4-5 hrs)
- [ ] EAN-13 generation
- [ ] Receipt confirmation logic
- [ ] Stock updates
- [ ] Cost recalculation

### Phase 4: Testing (2-3 hrs)
- [ ] End-to-end workflows
- [ ] Edge cases
- [ ] Performance

**Estimated Total:** 12-15 hours

---

## 📈 Key Metrics

- **Models:** 12 (all with relationships)
- **Migrations:** 14 (all tested)
- **Filament Resources:** 11 (5 configured, 2 scaffolded, 4 others)
- **Database Tables:** 14
- **Foreign Keys:** 30+
- **Documentation Pages:** 6
- **Lines of Documentation:** 1,800+

---

## 📖 Documentation Files

| File | Purpose | Best For |
|------|---------|----------|
| SOLUTION_SUMMARY.md | Executive summary | Decision makers |
| ARCHITECTURE.md | Technical design | Architects |
| STRUCTURAL_SOLUTION.md | Problem analysis | Understanding why |
| VISUAL_ARCHITECTURE.md | Diagrams & queries | Developers |
| ARCHITECTURE_STATUS.md | Status & roadmap | Project managers |
| Plan.md | Slice tracking | Progress monitoring |

---

## 🔍 Key Features

✅ **Unique Roll Tracking** - Each roll has unique EAN-13 barcode
✅ **Flexible Specifications** - Multiple receive combinations per product
✅ **Grouped Quantities** - Query by grammage, laise, weight attributes
✅ **Unified Receipt Workflow** - All product types in one process
✅ **Cost Tracking** - Weighted average per specification
✅ **System Protection** - PRODUCTION_CONSUMED warehouse protected
✅ **French UI** - Complete French translation

---

## 🐛 Known Issues

None currently. All core functionality tested and working.

---

## 📝 Git History

```
b9b122e - ARCHITECTURE_STATUS.md (final overview)
3992a95 - SOLUTION_SUMMARY.md (executive summary)
7c89e6b - VISUAL_ARCHITECTURE.md (diagrams)
c168d9a - STRUCTURAL_SOLUTION.md (solution analysis)
8dcda47 - Plan.md updated
1d7d44d - Architectural refactor (models, migrations)
a2e98b7 - Seeding with test data
8f88f35 - Slice 2 infrastructure
...
```

View full history: `git log --oneline`

---

## 🤝 Contributing

This is an internal project. For questions or contributions:
1. Review relevant documentation files
2. Check Plan.md for current phase
3. Reference ARCHITECTURE.md for design decisions

---

## 📞 Support

### Questions about...
- **Architecture?** → See ARCHITECTURE.md
- **Implementation?** → See ARCHITECTURE_STATUS.md (Next Steps)
- **Data Model?** → See VISUAL_ARCHITECTURE.md (Diagrams)
- **Status?** → See Plan.md

---

## 📄 License

Proprietary - CartonStock MVP Project

---

**Last Updated:** 2025-10-29
**Slice Status:** 2/7 complete ✅
**Ready for:** Slice 3 implementation ⚡


