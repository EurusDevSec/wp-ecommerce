# Emerald Tech Hub - Design System & Specification

This document contains the extracted design system specifications, color palette, typography guidelines, and component tokens for the **Emerald Tech Hub** (`projects/1716349995721271745`) on Stitch.

---

## 1. Project Information
- **Project Name:** `projects/1716349995721271745`
- **Title:** Emerald Tech Hub
- **Device Type:** `DESKTOP`
- **Visibility:** `PRIVATE`
- **Created Time:** `2026-05-29T07:36:29.401463Z`
- **Last Updated:** `2026-05-29T08:04:47.651994Z`

---

## 2. Brand & Style Guidelines

The design system is engineered for a **high-end consumer electronics environment**, emphasizing precision, performance, and luxury. 

- **Aesthetic:** Roots in **Minimalism** with subtle **Glassmorphism** accents to convey a "cutting-edge" technological feel.
- **Objective:** Create a "gallery-like" experience where products (laptops, smartphones) are the focal points, supported by a vast amount of whitespace and a sophisticated, cool-toned palette.
- **Feel:** Editorial tech-luxury space utilizing subtle emerald gradients and high-fidelity shadows to differentiate the interface from standard corporate platforms.

---

## 3. Color Palette

The color strategy uses a tiered light mode system anchored by a vibrant **Emerald** highlight.

### Core Theme Colors
| Token | Hex Value | Role |
| :--- | :--- | :--- |
| **Primary** | `#006d36` | Brand identifier, main actions |
| **On-Primary** | `#ffffff` | Text/icons on primary color |
| **Primary Container** | `#50c878` | Highlighted elements (Emerald) |
| **On-Primary Container** | `#005025` | Text/icons on primary container |
| **Secondary** | `#1b6b51` | Secondary actions / branding |
| **Secondary Container** | `#a6f2d1` | Soft secondary accents |
| **Tertiary** | `#416656` | Tertiary actions / specs indicators |
| **Tertiary Container** | `#94bba8` | Soft tertiary accents |

### Backgrounds & Surfaces
| Token | Hex Value | Role |
| :--- | :--- | :--- |
| **Background** | `#f7f9fb` | Root background |
| **On-Background** | `#191c1e` | Body text on root background |
| **Surface** | `#f7f9fb` | Standard container backgrounds |
| **On-Surface** | `#191c1e` | Text on standard containers |
| **Surface Dim** | `#d8dadc` | Muted surfaces / overlays |
| **Surface Bright** | `#f7f9fb` | Highlight surfaces |
| **Surface Container (Lowest)** | `#ffffff` | Card background (clean white) |
| **Surface Container (Low)** | `#f2f4f6` | Default section block backgrounds |
| **Surface Container** | `#eceef0` | Component backgrounds |
| **Surface Container (High)** | `#e6e8ea` | Hover state container backgrounds |
| **Surface Container (Highest)**| `#e0e3e5` | Active state container backgrounds |

### Borders & Outlines
| Token | Hex Value | Role |
| :--- | :--- | :--- |
| **Outline** | `#6e7a6e` | High-contrast borders |
| **Outline Variant** | `#bdcabc` | Soft dividers and subtle borders |

---

## 4. Typography

The design system utilizes **Inter** for all typographic roles to align with the high-tech, systematic subject matter.

| Token | Font Family | Size | Weight | Line Height | Letter Spacing |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **display-lg** | Inter | `64px` | `700` (Bold) | `1.1` | `-0.02em` |
| **headline-lg** | Inter | `48px` | `600` (Semi-Bold) | `1.2` | `-0.01em` |
| **headline-lg-mobile** | Inter | `32px` | `600` (Semi-Bold) | `1.2` | — |
| **headline-md** | Inter | `32px` | `600` (Semi-Bold) | `1.3` | — |
| **headline-sm** | Inter | `24px` | `600` (Semi-Bold) | `1.4` | — |
| **body-lg** | Inter | `18px` | `400` (Regular) | `1.6` | — |
| **body-md** | Inter | `16px` | `400` (Regular) | `1.6` | — |
| **label-md** | Inter | `14px` | `600` (Semi-Bold) | `1.4` | `0.02em` |

---

## 5. Spacing & Geometry

The design system follows a **Fixed Grid** model for desktop to ensure product photography is presented within a controlled, premium frame.

- **Desktop Container Max Width:** `1280px`
- **Desktop Outer Margin:** `64px`
- **Mobile Grid Outer Margin:** `20px` (Transitions to a fluid 4-column grid)
- **Gutter:** `24px`

### Spacing Scale (8px Linear Scale)
- **unit-xs:** `4px`
- **unit-sm:** `8px`
- **unit-md:** `16px`
- **unit-lg:** `32px`
- **unit-xl:** `64px`

### Shapes & Roundness (`ROUND_EIGHT`)
- **Base Corner Radius (Buttons, Inputs):** `0.5rem` (`8px`)
- **Medium Corner Radius (Cards):** `16px` (`rounded-lg`)
- **Large Corner Radius (Hero sections):** `24px` (`rounded-xl`)

---

## 6. Elevation & Depth

Visual hierarchy is achieved through **Ambient Shadows** and **Tonal Layers** rather than heavy borders.

- **Ambient Shadows:** Diffused shadows with a large blur radius (`0 20px 40px rgba(0,0,0,0.05)`) are used to lift product cards.
- **Glassmorphism:** For interactive panels/modals, use a backdrop-filter blur (`12px` to `20px`) with a semi-transparent white fill (`80%` opacity).

---

## 7. Component Tokens

### 1. Buttons
- **Primary Buttons:** Features a gradient from Emerald (`#50C878`) to Deep Emerald (`#3DAE64`) with a soft shadow that intensifies on hover. Font weight is semi-bold.
- **Ghost Buttons:** 1px border of primary emerald (`#50C878`) with a transparent background.

### 2. Cards
- **Product Cards:** Clean white background (`#ffffff`), no visible border, and high-fidelity ambient shadow. 
- **Padding:** Minimum `32px` of inner padding around product images to maintain the gallery aesthetic.

### 3. Input Fields
- **Default State:** Minimalist light gray background (`#F1F5F9`), no border.
- **Focus State:** White background (`#ffffff`) with a `2px` emerald stroke (`#50C878`) and a soft outer glow.

### 4. Chips & Badges
- **Technical Specs Badges:** Very light emerald background (`#D1FAE5`) and deep emerald text (`#065F46`). High legibility without visual weight.

### 5. Progress & Selection
- **Radio Buttons / Checkboxes:** Deep emerald accents.
- **Active Selection State:** Thick emerald border around the entire selected component.

---


## 8. Screen Instances (Design Layouts)
The project includes the following design screens:

1. **Screen 1 (Home/Gallery):** `8e95064f23be4581a97978587bf9c4ce` (`1280 x 2098`)
2. **Screen 2:** `94e97e8f306a4bc6ba45fde420376baa` (`1280 x 1024`)
3. **Screen 3:** `eb9240c43a6644b6bd713a164197076a` (`1280 x 1421`)
4. **Screen 4:** `ef02ecc87bbd4a19bc7f446518d706ea` (`1280 x 1799`)
5. **Screen 5:** `ef19ad6a5aa54f0780b11941cb692f81` (`1280 x 1024`)


Test function  by Hoang
