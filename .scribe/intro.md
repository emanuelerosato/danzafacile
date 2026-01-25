# Introduction

REST API for DanzaFacile mobile application (Flutter). Provides endpoints for authentication, student courses, payments, lessons, and notifications.

<aside>
    <strong>Base URL</strong>: <code>http://localhost:8089</code>
</aside>

    Welcome to the DanzaFacile Mobile API documentation.

    This API is designed for the Flutter mobile app and provides endpoints for:
    - **Authentication** (login, register, logout)
    - **Student features** (courses, lessons, payments, profile)
    - **Admin features** (course management, student management)
    - **Push notifications** (FCM token registration, preferences)

    ## Base URL
    - **Production:** `https://www.danzafacile.it/api/v1`
    - **Local Dev:** `http://localhost:8089/api/v1`

    ## Authentication
    All authenticated endpoints require a Bearer token obtained from the `/auth/login` endpoint.

    <aside>Use the "Try it out" feature to test endpoints directly from this documentation.</aside>

