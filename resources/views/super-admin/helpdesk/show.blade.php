@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id . ' - ' . $ticket->title)

@section('content')
<div class="p-8" x-data="ticketDetail()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('super-admin.helpdesk.index') }}" class="text-gray-600 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Ticket #{{ $ticket->id }}</h1>
                    <p class="text-gray-600">{{ $ticket->title }}</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <!-- Status Badge -->
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $ticket->status_color }}">
                    {{ ucfirst($ticket->status) }}
                </span>
                
                <!-- Priority Badge -->
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $ticket->priority_color }}">
                    {{ ucfirst($ticket->priority) }}
                </span>
                
                @if($ticket->is_overdue)
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                    In ritardo
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Ticket Details Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="border-b border-gray-200 pb-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold">{{ substr($ticket->user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $ticket->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $ticket->user->email }}</p>
                            </div>
                        </div>
                        <div class="text-right text-sm text-gray-500">
                            <p>Creato {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                            @if($ticket->closed_at)
                            <p>Chiuso {{ $ticket->closed_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="prose max-w-none">
                    <h3 class="text-lg font-semibold mb-2">Descrizione del problema</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</p>
                </div>
            </div>

            <!-- Response Timeline -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                    </svg>
                    Timeline Risposte ({{ $ticket->responses->count() }})
                </h3>

                <div class="space-y-6">
                    @forelse($ticket->responses as $response)
                    <div class="relative">
                        <!-- Timeline Line -->
                        @if(!$loop->last)
                        <div class="absolute left-5 top-12 w-px h-full bg-gray-200"></div>
                        @endif
                        
                        <div class="flex space-x-4">
                            <!-- Avatar -->
                            <div class="w-10 h-10 {{ $response->user->role === 'super_admin' ? 'bg-red-500' : 'bg-blue-500' }} rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white font-semibold text-sm">{{ substr($response->user->name, 0, 1) }}</span>
                            </div>
                            
                            <!-- Message Content -->
                            <div class="flex-1 min-w-0">
                                <div class="bg-gray-50 rounded-lg p-4 {{ $response->is_internal ? 'border-l-4 border-yellow-400' : '' }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-semibold text-gray-800">{{ $response->user->name }}</span>
                                            @if($response->user->role === 'super_admin')
                                            <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Super Admin</span>
                                            @endif
                                            @if($response->is_internal)
                                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">Nota interna</span>
                                            @endif
                                        </div>
                                        <span class="text-sm text-gray-500">{{ $response->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    
                                    <div class="prose max-w-none">
                                        <p class="text-gray-700 whitespace-pre-wrap">{{ $response->message }}</p>
                                    </div>
                                    
                                    @if($response->attachments)
                                    <div class="mt-3">
                                        <p class="text-sm font-semibold text-gray-600 mb-2">Allegati:</p>
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                            @foreach($response->attachments as $attachment)
                                            <div class="relative group">
                                                <img src="{{ asset('storage/' . $attachment) }}" 
                                                     alt="Allegato" 
                                                     class="w-full h-20 object-cover rounded-md cursor-pointer hover:opacity-90"
                                                     @click="openImageModal('{{ asset('storage/' . $attachment) }}')">
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-md transition-opacity"></div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                        </svg>
                        <p class="text-gray-500">Nessuna risposta ancora</p>
                    </div>
                    @endforelse
                </div>

                <!-- Reply Form (only if not closed) -->
                @if($ticket->status !== 'closed')
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h4 class="font-semibold mb-4">Rispondi al ticket</h4>
                    
                    <form action="{{ route('super-admin.helpdesk.reply', $ticket) }}" method="POST" enctype="multipart/form-data" x-data="replyForm()">
                        @csrf
                        
                        <div class="mb-4">
                            <textarea name="message" 
                                    placeholder="Scrivi la tua risposta..."
                                    rows="4"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    required>{{ old('message') }}</textarea>
                            @error('message')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Image Upload -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Allegati immagini (Solo Super Admin)</label>
                                <input type="file" 
                                       name="attachments[]" 
                                       multiple 
                                       accept="image/*,.pdf"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       x-on:change="previewImages($event)">
                                <p class="text-xs text-gray-500 mt-1">Max 5MB per file. Formati: JPG, PNG, GIF, PDF</p>
                                @error('attachments.*')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Internal Note Checkbox -->
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="is_internal" 
                                       id="is_internal"
                                       value="1"
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="is_internal" class="ml-2 text-sm text-gray-700">
                                    Nota interna (visibile solo ai Super Admin)
                                </label>
                            </div>
                        </div>
                        
                        <!-- Image Preview -->
                        <div x-show="imagesPreviews.length > 0" class="mb-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Anteprima allegati:</p>
                            <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                                <template x-for="preview in imagesPreviews" :key="preview.name">
                                    <div class="relative">
                                        <img :src="preview.url" :alt="preview.name" class="w-full h-16 object-cover rounded-md">
                                        <button type="button" 
                                                @click="removePreview(preview.name)"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs hover:bg-red-600">×</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div></div>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Invia risposta
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="font-semibold mb-4">Azioni rapide</h3>
                
                <div class="space-y-3">
                    @if($ticket->status !== 'closed')
                    <!-- Close Ticket -->
                    <button @click="showCloseModal = true" 
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                        Chiudi ticket
                    </button>
                    @else
                    <!-- Reopen Ticket -->
                    <form action="{{ route('super-admin.helpdesk.reopen', $ticket) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                            Riapri ticket
                        </button>
                    </form>
                    @endif
                    
                    <!-- Delete Ticket -->
                    <button @click="showDeleteModal = true" 
                            class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                        Elimina ticket
                    </button>
                </div>
            </div>

            <!-- Ticket Info -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="font-semibold mb-4">Informazioni ticket</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">ID:</span>
                        <span class="font-medium">#{{ $ticket->id }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Utente:</span>
                        <span class="font-medium">{{ $ticket->user->name }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Scuola:</span>
                        <span class="font-medium">{{ $ticket->user->school->name ?? 'N/A' }}</span>
                    </div>
                    
                    @if($ticket->category)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Categoria:</span>
                        <span class="font-medium">{{ $ticket->category }}</span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Creato:</span>
                        <span class="font-medium">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    
                    @if($ticket->closed_at)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Chiuso:</span>
                        <span class="font-medium">{{ $ticket->closed_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Risposte:</span>
                        <span class="font-medium">{{ $ticket->responses->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Update Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="font-semibold mb-4">Modifica ticket</h3>
                
                <form action="{{ route('super-admin.helpdesk.update', $ticket) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Aperto</option>
                                <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>In attesa</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Chiuso</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priorità</label>
                            <select name="priority" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Bassa</option>
                                <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>Alta</option>
                                <option value="critical" {{ $ticket->priority === 'critical' ? 'selected' : '' }}>Critica</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assegna a</label>
                            <select name="assigned_to" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">Nessuno</option>
                                @foreach($assignableUsers as $user)
                                <option value="{{ $user->id }}" {{ $ticket->assigned_to == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                            <input type="text" 
                                   name="category" 
                                   value="{{ $ticket->category }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                                   placeholder="Es: Tecnico, Supporto...">
                        </div>
                        
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            Aggiorna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Close Modal -->
    <div x-show="showCloseModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md" @click.away="showCloseModal = false">
            <h3 class="text-lg font-semibold mb-4">Chiudi ticket</h3>
            
            <form action="{{ route('super-admin.helpdesk.close', $ticket) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Messaggio finale (opzionale)</label>
                    <textarea name="final_message" 
                            rows="3" 
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            placeholder="Messaggio di chiusura per l'utente..."></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" 
                            @click="showCloseModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                        Annulla
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Chiudi ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div x-show="showDeleteModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md" @click.away="showDeleteModal = false">
            <h3 class="text-lg font-semibold mb-4">Elimina ticket</h3>
            <p class="text-gray-600 mb-6">Sei sicuro di voler eliminare questo ticket? Questa azione non può essere annullata.</p>
            
            <form action="{{ route('super-admin.helpdesk.destroy', $ticket) }}" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="flex space-x-3">
                    <button type="button" 
                            @click="showDeleteModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                        Annulla
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Elimina
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Image Modal -->
    <div x-show="imageModal.show" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="relative max-w-4xl max-h-full" @click.away="imageModal.show = false">
            <button @click="imageModal.show = false" 
                    class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 z-10">×</button>
            <img :src="imageModal.src" class="max-w-full max-h-full object-contain">
        </div>
    </div>
</div>

<script>
function ticketDetail() {
    return {
        showCloseModal: false,
        showDeleteModal: false,
        imageModal: {
            show: false,
            src: ''
        },
        
        openImageModal(src) {
            this.imageModal.src = src;
            this.imageModal.show = true;
        }
    }
}

function replyForm() {
    return {
        imagesPreviews: [],
        
        previewImages(event) {
            this.imagesPreviews = [];
            const files = event.target.files;
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imagesPreviews.push({
                            name: file.name,
                            url: e.target.result
                        });
                    };
                    reader.readAsDataURL(file);
                }
            }
        },
        
        removePreview(fileName) {
            this.imagesPreviews = this.imagesPreviews.filter(preview => preview.name !== fileName);
            // Reset file input if no previews left
            if (this.imagesPreviews.length === 0) {
                const fileInput = document.querySelector('input[name="attachments[]"]');
                fileInput.value = '';
            }
        }
    }
}
</script>

@endsection