---
name: Xcolnet Core
colors:
  surface: '#faf8ff'
  surface-dim: '#d8d9e6'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f3ff'
  surface-container: '#ecedfa'
  surface-container-high: '#e6e7f4'
  surface-container-highest: '#e1e2ee'
  on-surface: '#191b24'
  on-surface-variant: '#424656'
  inverse-surface: '#2e303a'
  inverse-on-surface: '#eff0fd'
  outline: '#727687'
  outline-variant: '#c2c6d8'
  surface-tint: '#0054d6'
  primary: '#0050cb'
  on-primary: '#ffffff'
  primary-container: '#0066ff'
  on-primary-container: '#f8f7ff'
  inverse-primary: '#b3c5ff'
  secondary: '#575e70'
  on-secondary: '#ffffff'
  secondary-container: '#d9dff5'
  on-secondary-container: '#5c6274'
  tertiary: '#a33200'
  on-tertiary: '#ffffff'
  tertiary-container: '#cc4204'
  on-tertiary-container: '#fff6f4'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae1ff'
  primary-fixed-dim: '#b3c5ff'
  on-primary-fixed: '#001849'
  on-primary-fixed-variant: '#003fa4'
  secondary-fixed: '#dce2f7'
  secondary-fixed-dim: '#c0c6db'
  on-secondary-fixed: '#141b2b'
  on-secondary-fixed-variant: '#404758'
  tertiary-fixed: '#ffdbd0'
  tertiary-fixed-dim: '#ffb59d'
  on-tertiary-fixed: '#390c00'
  on-tertiary-fixed-variant: '#832600'
  background: '#faf8ff'
  on-background: '#191b24'
  surface-variant: '#e1e2ee'
typography:
  display:
    fontFamily: Geist
    fontSize: 48px
    fontWeight: '600'
    lineHeight: '1.1'
    letterSpacing: -0.04em
  headline-lg:
    fontFamily: Geist
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: -0.03em
  headline-lg-mobile:
    fontFamily: Geist
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Geist
    fontSize: 24px
    fontWeight: '500'
    lineHeight: '1.3'
    letterSpacing: -0.02em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
    letterSpacing: -0.01em
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.5'
    letterSpacing: 0em
  label-md:
    fontFamily: Geist
    fontSize: 12px
    fontWeight: '500'
    lineHeight: '1'
    letterSpacing: 0.02em
  mono:
    fontFamily: Geist
    fontSize: 13px
    fontWeight: '400'
    lineHeight: '1.6'
    letterSpacing: 0em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  container-max: 1280px
  gutter: 24px
  margin-desktop: 64px
  margin-mobile: 20px
  rail-narrow: 280px
  rail-wide: auto
---

## Brand & Style
The brand personality is engineered, precise, and high-performance. It targets developers, engineers, and tech-forward enterprises who value efficiency and technical clarity. The UI should feel like a high-end tool—unobtrusive yet powerful, with an emotional response of focus and reliability.

The design style is **Modern Minimalist with Technical Depth**. It draws heavily from the "Software-as-Infrastructure" aesthetic, utilizing a restrained color palette, high-precision geometry, and subtle glassmorphism to create a layered, multi-dimensional workspace. Visual interest is generated through light physics (backlight, blurs) rather than decorative elements.

## Colors
The palette is rooted in "Pure White" to maximize perceived cleanliness and workspace airiness. 

- **Primary (Electric Blue):** Reserved for high-intent actions, active states, and critical paths. It should be used sparingly to maintain its impact.
- **Secondary (Deep Charcoal):** Used for primary text and core structural elements to provide a grounded, high-contrast anchor.
- **Accents (Grays):** A strict scale of cool grays handles the secondary UI infrastructure—borders, muted text, and subtle surface elevations.
- **Ambient Fog:** Soft, low-opacity gradients (5-10% opacity) using the primary blue or a soft violet can be used as background "blobs" to break the starkness of white without adding visual clutter.

## Typography
The typography system prioritizes legibility and a "monospaced feel" even within proportional fonts. 

**Geist** is used for headlines and labels to provide a technical, engineered character. **Inter** is utilized for body copy to ensure maximum readability in dense data environments. 

**Tight Tracking:** Headlines should use negative letter spacing to create a compact, "locked-in" look characteristic of premium software interfaces. For labels and small caps, slightly wider tracking is permitted to maintain clarity at small scales.

## Layout & Spacing
The layout uses a **Strict Fixed Grid** with an asymmetrical rail system. The primary container is capped at 1280px to prevent line lengths from becoming unreadable on ultra-wide monitors.

- **Asymmetry:** Use a narrow left rail (280px) for navigation or metadata, with a wider primary content area. This creates a functional hierarchy that feels "instrument-like."
- **Rhythm:** An 8px base scaling system is used for all internal component spacing (8, 16, 24, 32, 48, 64).
- **Whitespace:** Use generous top and bottom padding (min 96px) between major sections to emphasize the minimalist aesthetic and give content room to breathe.

## Elevation & Depth
Depth is communicated through **Glassmorphism and Precision Outlines** rather than traditional heavy shadows.

- **Surface Layers:** Use `backdrop-filter: blur(12px)` with a semi-transparent white fill (`rgba(255, 255, 255, 0.7)`) for floating panels, navigation bars, and dropdowns.
- **Borders:** All cards and containers feature a 0.5px or 1px border using `#E5E7EB`. For interactive elements, the border color transitions to the primary blue or a darker gray on hover.
- **Shadows:** Use a single, very soft "ambient" shadow for high-level modals only: `0 20px 50px rgba(0, 0, 0, 0.05)`. Avoid shadows on standard cards to maintain a flat, architectural feel.

## Shapes
The shape language is "Soft-Technical." We avoid sharp 90-degree corners to keep the UI approachable, but we also avoid overly bubbly or circular shapes to maintain a professional tone.

- **Default Radius:** 6px to 8px for buttons and inputs.
- **Large Radius:** 12px for cards and main content containers.
- **Exceptions:** Status indicators and specific avatars may use a full pill/circle shape.

## Components
- **Buttons:** Primary buttons use a solid `#111827` or `#0066FF` background with white text. Secondary buttons use a white background with the 0.5px border. No gradients on buttons; keep them flat and architectural.
- **Inputs:** Use a subtle `#F3F4F6` background. Focus states should trigger a 1px primary blue border with a 2px soft blue outer glow (30% opacity).
- **Cards:** White background, 0.5px border, no shadow. Use a subtle inner-glow (white 1px stroke) on glassmorphic elements to simulate light hitting the edge.
- **Chips:** Small, 12px Geist Mono text, light gray background (`#F3F4F6`), and 4px border-radius. Used for tagging and status.
- **Navigation:** Top-level nav should be sticky with a backdrop blur effect. Active links are indicated by a subtle weight change or a 2px bottom bar in primary blue.