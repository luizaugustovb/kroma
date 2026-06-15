---
name: Industrial Precision
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#3e4850'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#6e7881'
  outline-variant: '#bdc8d1'
  surface-tint: '#00658d'
  primary: '#00658d'
  on-primary: '#ffffff'
  primary-container: '#00a3e0'
  on-primary-container: '#00354b'
  inverse-primary: '#81cfff'
  secondary: '#575e71'
  on-secondary: '#ffffff'
  secondary-container: '#dbe2f9'
  on-secondary-container: '#5d6477'
  tertiary: '#505f76'
  on-tertiary: '#ffffff'
  tertiary-container: '#8a9ab2'
  on-tertiary-container: '#223246'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#c6e7ff'
  primary-fixed-dim: '#81cfff'
  on-primary-fixed: '#001e2d'
  on-primary-fixed-variant: '#004c6b'
  secondary-fixed: '#dbe2f9'
  secondary-fixed-dim: '#bfc6dc'
  on-secondary-fixed: '#141b2b'
  on-secondary-fixed-variant: '#404759'
  tertiary-fixed: '#d3e4fe'
  tertiary-fixed-dim: '#b7c8e1'
  on-tertiary-fixed: '#0b1c30'
  on-tertiary-fixed-variant: '#38485d'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  display-lg:
    fontFamily: Hanken Grotesk
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Hanken Grotesk
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Hanken Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-md:
    fontFamily: Hanken Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-md:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
  code-md:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  container-max: 1440px
  gutter: 16px
  sidebar-width: 260px
---

## Brand & Style

The design system is engineered for high-performance industrial environments, specifically tailored for the complexities of printing and visual communication ERP/CRM workflows. The personality is **Efficient, Technological, and Robust**, reflecting a company that bridges digital precision with physical production.

The aesthetic follows an **Industrial Modern** approach:
- **Clean & High-Contrast:** Essential for legibility in fast-paced production environments.
- **Data-Dense but Readable:** Optimizing screen real estate to show complex order statuses and CRM pipelines without visual clutter.
- **Structured:** A rigid adherence to grid systems and clear information hierarchy to reduce cognitive load during multi-tasking.
- **Materiality:** Subtle use of depth and tonal layering to separate "management" views from "production" data.

## Colors

The palette is anchored by a vibrant **Process Cyan** (#00A3E0), a direct nod to the CMYK printing foundation. This primary color is used for key actions, progress indicators, and active states.

- **Primary (Cyan):** Used for primary buttons, active navigation markers, and focus states.
- **Secondary (Slate/Charcoal):** Provides a professional, grounded contrast for sidebars, headers, and text.
- **Semantic Colors:** Refined hues for Success (production complete), Warning (delayed), and Danger (error/overdue) to ensure instant status recognition in dense tables.
- **Background Tones:** In light mode, use a mix of pure white (#FFFFFF) for cards and light gray (#F1F5F9) for page backgrounds to create subtle separation.

## Typography

This design system utilizes a dual-font strategy to balance character with utility:
- **Hanken Grotesk** is used for headings and KPIs. Its sharp, contemporary geometry feels engineered and high-tech.
- **Inter** is the workhorse for body copy, data tables, and forms. It is chosen for its exceptional legibility at small sizes and high x-height, which is critical for reading technical specifications.

For data-heavy views (ERP tables), use `body-md` for row content and `label-md` (uppercase) for column headers to create a clear structural distinction.

## Layout & Spacing

The layout uses a **Fluid Grid** approach within a maximum container width to maintain efficiency on ultra-wide monitors common in production offices.

- **Base Unit:** A 4px baseline grid ensures tight, mathematical consistency.
- **Data Density:** Use the `md` (16px) spacing for standard padding, but scale down to `sm` (8px) within data tables and sidebars to maximize information density.
- **Sidebar:** A fixed 260px left-hand navigation provides persistent access to ERP modules (Inventory, Production, CRM, Finance).
- **Responsive Behavior:** On tablet, the sidebar collapses into an icon-only rail. On mobile, the layout shifts to a single column with horizontal scrolling enabled for large data tables.

## Elevation & Depth

To maintain the "Industrial Modern" feel, depth is achieved through **Tonal Layers** and **Low-Contrast Outlines** rather than heavy shadows.

- **Level 0 (Surface):** The main background color (#F8FAFC).
- **Level 1 (Cards/Sections):** Pure white (#FFFFFF) with a 1px border (#E2E8F0). This is the standard container for CRM kanban cards and data table rows.
- **Level 2 (Active/Floating):** Used for modals and dropdowns. Features a subtle ambient shadow: `0px 4px 12px rgba(0, 0, 0, 0.05)`.
- **Focus States:** High-contrast 2px cyan rings are used to indicate keyboard navigation and active input fields, essential for high-speed data entry.

## Shapes

The shape language is **Rounded (0.5rem)**, providing a modern and accessible feel that softens the industrial edges of the data-heavy interface.
- **Buttons and Inputs:** Use 8px (0.5rem) radius for a balanced, contemporary interactive state.
- **Cards:** Use 16px (1rem) for larger containers to create a distinct, nested appearance.
- **Badges/Chips:** Use 4px (0.25rem) radius or full-pill for status indicators to ensure they remain legible while appearing distinct from buttons.

## Components

### Buttons & Inputs
- **Primary Action:** Solid Cyan (#00A3E0) with white text and 8px corners.
- **Secondary Action:** Ghost style with Slate (#272E3F) borders.
- **Inputs:** High-contrast white backgrounds with 1px Slate-300 borders and 8px corners. Use fixed-width fonts for numeric inputs.

### Data Tables
- **Density:** Tight vertical padding (8px). 
- **Header:** Sticky headers with a subtle bottom border and `label-md` typography.
- **Striping:** Subtle zebra striping using #F8FAFC for better horizontal tracking.

### Kanban (CRM)
- **Cards:** Compact white containers with 16px corner radius and a left-edge color strip indicating lead priority.
- **Drag-and-Drop:** Visual lift effect using Level 2 elevation when a card is active.

### KPI Cards
- Large `headline-lg` numeric values in Primary Cyan.
- Small sparkline charts in the background to show 7-day trends.

### Sidebars
- **Background:** Deep Slate (#272E3F).
- **Icons:** Minimalist 20px line icons with a 2px stroke width.
- **Active State:** A vertical Cyan bar on the left edge with a subtle background tint and rounded selection indicator.