<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white/95 backdrop-blur-md border-r border-rose-100 shadow-lg lg:translate-x-0 transform transition-transform duration-300 ease-in-out"
       :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
       x-show="sidebarOpen || window.innerWidth >= 1024"
       x-transition:enter="transition ease-in-out duration-300"
       x-transition:enter-start="-translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in-out duration-300"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="-translate-x-full">
    
    <!-- Logo -->
    <div class="flex items-center justify-center p-6 border-b border-rose-100">
        <a href="{{ route('dashboard') }}" class="flex items-center">
            <div class="w-10 h-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-lg mr-3">
                {{ $appSettings['app_logo'] ?? 'SMS' }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $appSettings['app_name'] ?? 'School Management System' }}</h2>
                <p class="text-xs text-gray-600">{{ Auth::user()->role ?? 'Dashboard' }}</p>
            </div>
        </a>
    </div>
    
    <!-- Navigation -->
    <nav class="p-4 space-y-2 overflow-y-auto flex-1">
        @if(Auth::user()->role === 'super_admin')
            <!-- Super Admin Menu -->
            <x-nav-item href="{{ route('super-admin.dashboard') }}" :active="request()->routeIs('super-admin.dashboard')" icon="home">
                Dashboard
            </x-nav-item>
            
            <x-nav-group title="Gestione Dati" icon="database">
                <x-nav-item href="{{ route('super-admin.schools.index') }}" :active="request()->routeIs('super-admin.schools.*')" icon="academic-cap">
                    Scuole
                </x-nav-item>
                <x-nav-item href="{{ route('super-admin.users.index') }}" :active="request()->routeIs('super-admin.users.*')" icon="users">
                    Utenti
                </x-nav-item>
            </x-nav-group>
            
            <x-nav-group title="Sistema & Monitoring" icon="shield-check">
                <x-nav-item href="{{ route('super-admin.helpdesk.index') }}" :active="request()->routeIs('super-admin.helpdesk.*')" icon="chat">
                    Messaggi
                    @php
                        $unreadTickets = \App\Models\Ticket::where('status', '!=', 'closed')->count();
                    @endphp
                    @if($unreadTickets > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $unreadTickets }}</span>
                    @endif
                </x-nav-item>
                <x-nav-item href="{{ route('super-admin.reports') }}" :active="request()->routeIs('super-admin.reports')" icon="chart-bar">
                    Report & Analytics
                </x-nav-item>
                <x-nav-item href="{{ route('super-admin.logs') }}" :active="request()->routeIs('super-admin.logs')" icon="clipboard-list">
                    Log Sistema
                </x-nav-item>
                <x-nav-item href="{{ route('super-admin.settings') }}" :active="request()->routeIs('super-admin.settings')" icon="adjustments">
                    Impostazioni
                </x-nav-item>
            </x-nav-group>
            
            <!-- Help Section for Super Admin -->
            <div class="border-t border-rose-100 pt-4 mt-4">
                <x-nav-item href="{{ route('super-admin.help') }}" :active="request()->routeIs('super-admin.help')" icon="question-mark-circle">
                    Aiuto
                </x-nav-item>
            </div>
            
        @elseif(Auth::user()->role === 'admin')
            <!-- Admin Menu -->
            <x-nav-item href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" icon="home">
                Dashboard
            </x-nav-item>
            
            <x-nav-group title="Gestione Corsi" icon="academic-cap">
                <x-nav-item href="{{ route('admin.courses.index') }}" :active="request()->routeIs('admin.courses.*')" icon="book-open">
                    Corsi
                </x-nav-item>
                <x-nav-item href="{{ route('admin.schedules.index') }}" :active="request()->routeIs('admin.schedules.*')" icon="calendar">
                    Orari
                </x-nav-item>
                <x-nav-item href="{{ route('admin.staff.index') }}" :active="request()->routeIs('admin.staff.*')" icon="user-group">
                    Istruttori
                </x-nav-item>
            </x-nav-group>
            
            <x-nav-group title="Studenti" icon="users">
                <x-nav-item href="{{ route('admin.students.index') }}" :active="request()->routeIs('admin.students.*')" icon="user">
                    Lista Studenti
                </x-nav-item>
                <x-nav-item href="{{ route('admin.enrollments.index') }}" :active="request()->routeIs('admin.enrollments.*')" icon="clipboard-check">
                    Iscrizioni
                </x-nav-item>
                <x-nav-item href="{{ route('admin.attendance.index') }}" :active="request()->routeIs('admin.attendance.*')" icon="check-circle">
                    Presenze
                </x-nav-item>
            </x-nav-group>

            <x-nav-group title="Eventi" icon="calendar">
                <x-nav-item href="{{ route('admin.events.index') }}" :active="request()->routeIs('admin.events.*')" icon="calendar">
                    Lista Eventi
                </x-nav-item>
                <x-nav-item href="{{ route('admin.event-registrations.index') }}" :active="request()->routeIs('admin.event-registrations.*')" icon="user-plus">
                    Registrazioni
                </x-nav-item>
            </x-nav-group>
            
            <x-nav-group title="Staff" icon="user-group">
                <x-nav-item href="{{ route('admin.staff.index') }}" :active="request()->routeIs('admin.staff.*')" icon="users">
                    Gestione Staff
                </x-nav-item>
                <x-nav-item href="{{ route('admin.staff-schedules.index') }}" :active="request()->routeIs('admin.staff-schedules.*')" icon="calendar">
                    Orari & Turni
                </x-nav-item>
                <x-nav-item href="#" :active="request()->routeIs('admin.payroll.*')" icon="calculator">
                    Buste Paga
                </x-nav-item>
            </x-nav-group>

            <x-nav-group title="Gestione" icon="briefcase">
                <x-nav-item href="{{ route('admin.payments.index') }}" :active="request()->routeIs('admin.payments.*')" icon="credit-card">
                    Pagamenti
                </x-nav-item>
                <x-nav-item href="{{ route('admin.documents.index') }}" :active="request()->routeIs('admin.documents.*')" icon="document">
                    Documenti
                </x-nav-item>
                <x-nav-item href="{{ route('admin.galleries.index') }}" :active="request()->routeIs('admin.galleries.*')" icon="photograph">
                    Gallerie
                </x-nav-item>
            </x-nav-group>

            <x-nav-group title="Analytics" icon="chart-bar">
                <x-nav-item href="{{ route('admin.reports.index') }}" :active="request()->routeIs('admin.reports.*')" icon="chart-line">
                    Reports & Analytics
                </x-nav-item>
                <x-nav-item href="#" :active="request()->routeIs('admin.dashboards.*')" icon="presentation-chart-bar">
                    Dashboard Personalizzati
                </x-nav-item>
            </x-nav-group>
            
        @else
            <!-- Student Menu -->
            <x-nav-item href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home">
                Dashboard
            </x-nav-item>
            
            <x-nav-group title="I Miei Corsi" icon="academic-cap">
                <x-nav-item href="#" :active="request()->routeIs('student.courses.*')" icon="book-open">
                    Corsi Disponibili
                </x-nav-item>
                <x-nav-item href="#" :active="request()->routeIs('student.my-courses.*')" icon="clipboard-check">
                    Le Mie Iscrizioni
                </x-nav-item>
                <x-nav-item href="#" :active="request()->routeIs('student.schedule.*')" icon="calendar">
                    Il Mio Programma
                </x-nav-item>
            </x-nav-group>
            
            <x-nav-group title="Profilo" icon="user">
                <x-nav-item href="#" :active="request()->routeIs('student.payments.*')" icon="credit-card">
                    Pagamenti
                </x-nav-item>
                <x-nav-item href="#" :active="request()->routeIs('student.documents.*')" icon="document">
                    Documenti
                </x-nav-item>
                <x-nav-item href="#" :active="request()->routeIs('student.gallery.*')" icon="photograph">
                    Galleria
                </x-nav-item>
            </x-nav-group>
        @endif
        
        <!-- Common items for non-super-admin users only -->
        @if(Auth::user()->role !== 'super_admin')
        <div class="border-t border-rose-100 pt-4 mt-4">
            <x-nav-item href="#" :active="request()->routeIs('messages.*')" icon="chat">
                Messaggi
                <span class="ml-auto bg-rose-500 text-white text-xs px-2 py-1 rounded-full">2</span>
            </x-nav-item>
            
            <x-nav-item href="#" :active="request()->routeIs('help.*')" icon="question-mark-circle">
                Aiuto
            </x-nav-item>
        </div>
        @endif
    </nav>
    
    <!-- User Profile in Sidebar -->
    <div class="p-4 border-t border-rose-100 bg-gradient-to-r from-rose-50 to-purple-50">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>