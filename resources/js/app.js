import './bootstrap';
import Alpine from 'alpinejs';

// Only boot Alpine on non-manager pages.
// The file manager boots its own Alpine instance with registered components.
if (!window.PAGE_SLUG) {
    window.Alpine = Alpine;
    Alpine.start();
}
