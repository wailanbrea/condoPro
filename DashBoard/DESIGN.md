---
name: Premium Condominium Financial System
colors:
  surface: '#f9f9ff'
  surface-dim: '#cadbfc'
  surface-bright: '#f9f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f0f3ff'
  surface-container: '#e7eeff'
  surface-container-high: '#dfe8ff'
  surface-container-highest: '#d6e3ff'
  on-surface: '#091c35'
  on-surface-variant: '#434654'
  inverse-surface: '#20314b'
  inverse-on-surface: '#ecf0ff'
  outline: '#737685'
  outline-variant: '#c3c6d6'
  surface-tint: '#0c56d0'
  primary: '#003d9b'
  on-primary: '#ffffff'
  primary-container: '#0052cc'
  on-primary-container: '#c4d2ff'
  inverse-primary: '#b2c5ff'
  secondary: '#006c47'
  on-secondary: '#ffffff'
  secondary-container: '#82f9be'
  on-secondary-container: '#00734c'
  tertiary: '#5e3c00'
  on-tertiary: '#ffffff'
  tertiary-container: '#7d5200'
  on-tertiary-container: '#ffca81'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2ff'
  primary-fixed-dim: '#b2c5ff'
  on-primary-fixed: '#001848'
  on-primary-fixed-variant: '#0040a2'
  secondary-fixed: '#82f9be'
  secondary-fixed-dim: '#65dca4'
  on-secondary-fixed: '#002113'
  on-secondary-fixed-variant: '#005235'
  tertiary-fixed: '#ffddb3'
  tertiary-fixed-dim: '#ffb950'
  on-tertiary-fixed: '#291800'
  on-tertiary-fixed-variant: '#624000'
  background: '#f9f9ff'
  on-background: '#091c35'
  surface-variant: '#d6e3ff'
typography:
  display-xl:
    fontFamily: Inter
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
    letterSpacing: '0'
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
    letterSpacing: '0'
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
    letterSpacing: '0'
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
  mono-data:
    fontFamily: JetBrains Mono
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
    letterSpacing: -0.01em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  gutter: 24px
  margin: 40px
---

## Brand & Style

The brand personality is authoritative yet accessible, designed to instill a sense of financial security and operational efficiency. The target audience includes property managers, condominium boards, and residents who require clarity in complex financial data. 

The style is **Corporate Modern**, drawing heavily from the precision of high-end fintech platforms. It utilizes a "density-aware" approach: high-density for data tables and financial ledgers to maximize information visibility, and low-density for resident-facing dashboards to reduce cognitive load. The aesthetic is defined by high-contrast typography, purposeful whitespace, and a meticulous attention to alignment that reflects the organized nature of professional property management.

## Colors

The color palette is functionally driven, using semantic signals to communicate financial status instantly. 

- **Primary (Corporate Blue):** Used for navigation, primary actions, and branding. It represents stability and professional oversight.
- **Success (Emerald Green):** Reserved exclusively for "Paid" or "Confirmed" statuses. It provides a positive reinforcement for financial reconciliation.
- **Warning (Amber Yellow):** Indicates "Pending" or "In Review" states. It draws attention without causing alarm.
- **Danger (Rose Red):** Highlights "Debts," "Overdue," or "Critical" issues. It is used sparingly to maintain its psychological impact.
- **Backgrounds:** A tiered system using #F4F5F7 for the application canvas and #FFFFFF for content cards and data surfaces to create subtle depth.

## Typography

This design system utilizes **Inter** for its exceptional legibility in data-heavy interfaces. The typographic scale is optimized for bilingual support, ensuring that Spanish text—which is typically longer than English—does not break layouts.

For financial figures and currency units within tables, a tabular-nums approach is preferred. Large display styles are reserved for dashboard totals, while labels are often used in uppercase with slight tracking for secondary metadata or table headers.

## Layout & Spacing

The layout follows a **Fluid Grid** model with a maximum content width of 1440px. The spacing rhythm is based on a 4px baseline, ensuring all components align to a mathematical scale.

- **Grid:** 12-column layout for desktop dashboards.
- **Gutters:** Fixed at 24px to provide ample "breathing room" between complex data widgets.
- **Margins:** 40px for desktop views to frame the content elegantly; 16px for mobile views.
- **Padding:** Content cards utilize 24px internal padding (lg) to maintain a premium, airy feel typical of high-end SaaS applications.

## Elevation & Depth

Visual hierarchy is established through **Ambient Shadows** and tonal layering rather than heavy borders.

- **Level 0 (Canvas):** #F4F5F7.
- **Level 1 (Cards/Surface):** Pure White (#FFFFFF) with a very soft, diffused shadow (0px 2px 4px rgba(0, 0, 0, 0.05)).
- **Level 2 (Modals/Dropdowns):** Pure White with a more pronounced shadow (0px 10px 20px rgba(0, 0, 0, 0.08)) to create a floating effect.
- **In-Set (Inputs):** Subtle 1px border in #DFE1E6. On focus, a 2px blue ring with 20% opacity is applied to indicate activity.

This approach creates a flat but multi-dimensional environment that feels organized and modern.

## Shapes

The shape language is sophisticated and friendly, moving away from sharp industrial corners toward a more approachable **Rounded** aesthetic.

- **Standard Elements (Buttons, Inputs):** 8px (0.5rem) radius.
- **Containers (Cards, Widgets):** 12px (0.75rem) - 16px (1rem) radius.
- **Badges/Chips:** Full pill-shape for status indicators to distinguish them from interactive buttons.

This consistent use of soft radii helps soften the "coldness" of financial data and makes the platform feel more like a modern service tool.

## Components

### Buttons
- **Primary:** Solid #0052CC with white text. 8px border radius.
- **Secondary:** White background with #0052CC text and a subtle #DFE1E6 border.
- **Ghost:** No background, #42526E text, for less prominent actions.

### Elegant Tables
Tables are the heart of the system. They feature:
- No vertical borders; only subtle 1px horizontal dividers.
- Ample cell padding (16px vertical).
- Alternating row highlights on hover for better tracking.
- Sticky headers for long financial ledgers.

### Status Chips
High-contrast text on low-opacity backgrounds. For example:
- **Paid:** Dark green text on #E3FCEF background.
- **Overdue:** Dark red text on #FFEBE6 background.

### Input Fields
Clean fields with 1px borders. Labels are always positioned above the input in `body-sm` weight 600. Helper text is provided in `body-sm` for bilingual clarity.

### Modern Icons
Use **Lucide** icons with a 2px stroke width. Icons should always be accompanied by text labels in navigation to assist with the bilingual translation context.