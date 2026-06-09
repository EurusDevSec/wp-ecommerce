<?php
/**
 * Logo element override for HKT Fashion (Flatsome Child Theme).
 * This forces a clean, minimalist typography logo using Montserrat.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

$logo_link = get_theme_mod( 'logo_link' );
$logo_link = $logo_link ? $logo_link : home_url( '/' );
?>

<!-- Header logo -->
<a href="<?php echo esc_url( $logo_link ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home" class="custom-text-logo-link">
    <span class="logo-text" style="font-size: 24px; font-weight: 800; letter-spacing: 2.5px; text-transform: uppercase; color: #1a1a1a; font-family: 'Montserrat', sans-serif; transition: opacity 0.3s ease;">
        HKT FASHION
    </span>
</a>

<style>
.custom-text-logo-link:hover .logo-text {
    opacity: 0.8;
}
</style>
