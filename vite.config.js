import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin/courses/course-edit.js',
                'resources/css/admin/courses/course-edit.css',
                'resources/js/admin/rooms/room-manager.js',
                'resources/js/admin/enrollments/enrollment-manager.js',
                'resources/js/admin/attendance/attendance-manager.js',
                'resources/js/admin/events/events-manager.js'
            ],
            refresh: true,
        }),
    ],
});
