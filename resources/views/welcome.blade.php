<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria</title>
    <!-- Fonte Inter Obrigatória -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style> 
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#faf3ed] text-[#250082] min-h-screen flex flex-col justify-between" x-data="galleryApp()" x-init="">

    <!-- 1. Cabeçalho da Aplicação -->
    <header class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
        <!-- 1.1 Seção Usuário -->
        <div class="flex items-center gap-3 w-1/3">
            <template x-if="user">
                <div class="flex items-center gap-3">
                    <img :src="user.profile_img ? '/storage/profile' + user.profile_img : 'storage/images/default-profile_img.png'" class="w-10 h-10 rounded-full border border-[#250082] object-cover">
                    <span class="font-semibold" x-text="'@' + user.name"></span>
                    <button 
                        @click="openProfileModal = true" 
                        :disabled="user.role !== 'fotografo'"
                        class="bg-[#250082] text-white px-3 py-1 text-sm font-semibold rounded disabled:opacity-50 disabled:cursor-not-allowed">
                        Ver Perfil
                    </button>
                </div>
            </template>
        </div>

        <!-- 1.2 Seção Empresa -->
        <div class="w-1/3 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-[#250082]">Galeria</h1>
        </div>

        <!-- 1.3 Seção de Login / Logout -->
        <div class="w-1/3 flex justify-end">
            <template x-if="!user">
                <button @click="showLoginModal = true; authMode = 'login';" class="rounded-full bg-[#250082] text-white px-5 py-2 rounded font-semibold hover:bg-gray-800 transition">
                    Login
                </button>
            </template>
            <template x-if="user">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-[#250082] text-white px-5 py-2 rounded font-semibold hover:bg-red transition">
                        Sair
                    </button>
                </form>
            </template>
        </div>
    </header>

    <!-- Menu Lateral (Perfil do Fotógrafo) -->
    <div x-show="openProfileModal" class="fixed inset-y-0 left-0 w-80 bg-gray-100 shadow-2xl p-6 z-40 border-r border-gray-300">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Perfil do Fotógrafo</h2>
            <button @click="openProfileModal = false" class="text-[#250082] font-bold text-xl">&times;</button>
        </div>
        <div class="space-y-4">
            <p class="text-lg"><span class="font-bold" x-text="stats.likes"></span> Likes</p>
            <p class="text-lg border-t border-b border-[#250082] py-2"><span class="font-bold" x-text="stats.publications"></span> Publicações</p>
        </div>
    </div>

    <!-- 2. Corpo da Aplicação -->
    <main class="max-w-6xl w-full mx-auto py-8 px-4 flex-growl">

        <!-- Seção Exclusiva para Fotógrafos: Formulário de Nova Foto -->
        <template x-if="user && user.role === 'fotografo'">
            <div class="mb-8 p-6 bg-gray-50 border border-[#250082] rounded-lg max-w-xl mx-auto shadow-sm">
                <h2 class="text-lg font-bold mb-4">Nova Publicação</h2>

                <form @submit.prevent="uploadPhoto()" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="block text-sm font-semibold mb-1">Título da Foto</label>
                        <input type="text" x-model="photoForm.title" placeholder="Ex: Pôr do sol na praia" required class="w-full border border-[#250082] rounded p-2 text-sm">
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-semibold mb-1">Localização</label>
                        <input type="text" x-model="photoForm.location" placeholder="Ex: Rio de Janeiro, RJ" required class="w-full border border-[#250082] rounded p-2 text-sm">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1">Imagem</label>
                        <input type="file" @change="photoForm.image = $event.target.files[0]" accept="image/*" required class="w-full border border-[#250082] rounded p-2 text-sm bg-white">
                    </div>

                    <button type="submit" class="w-full bg-[#250082] text-white font-semibold py-2 rounded hover:bg-gray-800 transition">
                        Publicar Foto
                    </button>
                </form>
            </div>
        </template>

        <!-- 2.1 Campo de Busca -->
        <div class="relative mb-8 max-w-xl mx-auto">
            <input 
                type="text" 
                x-model="searchQuery" 
                @input.debounce.300ms="fetchPhotos()"
                placeholder="Pesquisar Fotografo..." 
                class="w-full border border-[#250082] rounded-lg pr-12 focus:outline-none focus:ring-1 focus:ring-[#250082]">
            <!-- <img src="/icons/lupa.svg" class="w-5 h-5 absolute right-4 top-4 opacity-70"> -->
        </div>

        <!-- Mensagem de Erro na Busca -->
        <template x-if="searchError">
            <p class="text-center text-red font-semibold mb-6" x-text="searchError"></p>
        </template>

        <!-- 2.2 Galeria de Fotos (3 colunas por linha) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <template x-for="photo in photos" :key="photo.id">
                <div class="relative group border border-gray-300 rounded overflow-hidden shadow-sm hover:shadow-md transition">
                    <!-- Tooltip no Hover com Informações da Foto -->
                    <div class="absolute inset-0 bg-[#250082]/75 opacity-0 group-hover:opacity-100 transition-opacity p-4 text-white flex flex-col justify-end pointer-events-none">
                        <p class="text-sm font-semibold" x-text="'Foto tirada por: ' + photo.photographer"></p>
                        <p class="text-sm" x-text="'Local: ' + photo.location"></p>
                    </div>

                    <!-- Imagem -->
                    <img :src="photo.image_url" class="w-full h-64 object-cover">

                    <!-- Informações e Curtidas abaixo da foto -->
                    <div class="p-4 flex justify-between items-center bg-[#fcf7f0] border-t border-gray-100">
                        <span class="font-semibold text-gray-800" x-text="photo.title"></span>
                        <div class="flex items-center gap-1.5">
                            <button @click="toggleLike(photo)" class="focus:outline-none">
                                <svg class="w-6 h-6 transition-colors" :class="photo.is_liked ? 'text-red fill-current' : 'text-[#250082] fill-none'" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                            <span class="font-bold text-sm" x-text="photo.likes_count"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </main>

    <!-- Modal de Autenticação (Login / Registro) com Breeze -->
    <div 
        x-show="showLoginModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-[#250082]/60 backdrop-blur-sm"
    >
        <div class="bg-[#fcf7f0] rounded-lg shadow-xl w-full max-w-md p-6 relative border border-[#250082]">
            
            <!-- Botão Fechar (X) -->
            <button 
                @click="showLoginModal = false" 
                class="absolute top-4 right-4 text-[#250082] font-bold text-xl hover:opacity-70"
            >
                &times;
            </button>

            <!-- Abas do Modal -->
            <div class="flex border-b border-gray-300 mb-6">
                <button 
                    type="button"
                    @click="authMode = 'login'" 
                    :class="authMode === 'login' ? 'border-b-2 border-[#250082] font-bold text-[#250082]' : 'text-gray-500'" 
                    class="flex-1 py-2 text-center text-lg"
                >
                    Login
                </button>
                <button 
                    type="button"
                    @click="authMode = 'register'" 
                    :class="authMode === 'register' ? 'border-b-2 border-[#250082] font-bold text-[#250082]' : 'text-gray-500'" 
                    class="flex-1 py-2 text-center text-lg"
                >
                    Cadastre-se
                </button>
            </div>

            <!-- FORMULÁRIO DE LOGIN -->
            <form x-show="authMode === 'login'" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-semibold mb-1">E-mail</label>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="Digite seu e-mail" 
                        required 
                        autofocus 
                        class="w-full border border-[#250082] rounded p-2 focus:outline-none focus:ring-1 focus:ring-[#250082]"
                    >
                    @if ($errors->has('email'))
                        <span class="text-red text-xs mt-1 block">{{ $errors->first('email') }}</span>
                    @endif
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-semibold mb-1">Senha</label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        placeholder="Digite sua senha" 
                        required 
                        class="w-full border border-[#250082] rounded p-2 focus:outline-none focus:ring-1 focus:ring-[#250082]"
                    >
                    @if ($errors->has('password'))
                        <span class="text-red text-xs mt-1 block">{{ $errors->first('password') }}</span>
                    @endif
                </div>

                <div class="flex justify-end gap-3">
                    <button 
                        type="button" 
                        @click="showLoginModal = false" 
                        class="border border-[#250082] px-4 py-2 rounded text-[#250082] bg-[#fcf7f0] font-semibold hover:bg-gray-100"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="bg-[#250082] text-[#fcf7f0] px-6 py-2 rounded font-semibold hover:bg-gray-800"
                    >
                        Login
                    </button>
                </div>
            </form>

            <!-- FORMULÁRIO DE REGISTRO -->
            <form x-show="authMode === 'register'" method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label class="block text-sm font-semibold mb-1">Nome Completo</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Ex: João Silva" required class="w-full border border-[#250082] rounded p-2 text-sm">
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-semibold mb-1">Nome de Usuário</label>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="Ex: joaosilva" required class="w-full border border-[#250082] rounded p-2 text-sm">
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-semibold mb-1">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="exemplo@dominio.com" required class="w-full border border-[#250082] rounded p-2 text-sm">
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-semibold mb-1">Tipo de Perfil</label>
                    <select name="role" class="w-full border border-[#250082] rounded p-2 text-sm bg-[#fcf7f0]" required>
                        <option value="usuario">Usuário Comum</option>
                        <option value="fotografo">Fotógrafo</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-semibold mb-1">Senha</label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres" required class="w-full border border-[#250082] rounded p-2 text-sm">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Confirmar Senha</label>
                    <input type="password" name="password_confirmation" placeholder="Repita a senha" required class="w-full border border-[#250082] rounded p-2 text-sm">
                </div>

                <div class="mb-4">
                    <x-input-label for="profile_img" value="Foto de Perfil" />
                    <input 
                        type="file" 
                        name="profile_img" 
                        id="profile_img" 
                        accept="image/png, image/jpeg, image/jpg, image/webp"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#250082]-50 file:text-[#250082]-700 hover:file:bg-[#250082]-100" 
                    />
                    <x-input-error :messages="$errors->get('profile_img')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="showLoginModal = false" class="border border-[#250082] px-4 py-2 rounded text-[#250082] font-semibold">Cancelar</button>
                    <button type="submit" class="bg-[#250082] text-white px-6 py-2 rounded font-semibold">Cadastrar</button>
                </div>
            </form>

        </div>
    </div>

    <!-- 3. Rodapé da Aplicação -->
    <footer class="bg-[#250082] text-white px-10 py-3 grid grid-cols-3 items-center mt-12">
    <span class="font-bold text-lg justify-self-start"> Galeria </span>
    <div class="flex gap-6 justify-self-center items-center">
        <img src="/icons/tiktok.svg" class="w-5 h-5 invert">
        <img src="/icons/youtube.svg" class="w-5 h-5 invert">
        <img src="/icons/instagram.svg" class="w-5 h-5 invert">
        <img src="/icons/linkedIn.svg" class="w-5 h-5 invert">
    </div>
    <span class="text-sm justify-self-end">
        @Copyright 2026-2027
    </span>
    </footer>

    <!-- Controle de Estado Reativo com Alpine.js -->
    <script>
        function galleryApp() {
            return {
                photos: [],
                searchQuery: '',
                searchError: '',
                user: @json(auth()->user()),
                
                showLoginModal: {{ $errors->any() ? 'true' : 'false' }},
                authMode: '{{ old("name") || old("username") ? "register" : "login" }}',
                openProfileModal: false,
                stats: { likes: 0, publications: 0 },

                photoForm: {
                    title: '',
                    location: '',
                    image: null
                },

                async uploadPhoto() {
                    if (!this.photoForm.image) return;

                    let formData = new FormData();
                    formData.append('title', this.photoForm.title);
                    formData.append('location', this.photoForm.location);
                    formData.append('image', this.photoForm.image);

                    let res = await fetch('/photos', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if (res.ok) {
                        this.photoForm = { title: '', location: '', image: null };
                        this.fetchPhotos();
                    } else {
                        let data = await res.json();
                        alert(data.message || 'Erro ao publicar foto.');
                    }
                }
            /////////////// parte que já estava comentada

                // async fetchPhotos() {
                //     this.searchError = '';
                    
                //     try {
                //         let res = await fetch(`/photos?search=${encodeURIComponent(this.searchQuery)}`);
                //         let data = await res.json();

                //         if (data.length === 0 && this.searchQuery !== '') {
                //             this.searchError = 'Fotógrafo não encontrado';
                //             this.photos = [];
                //         } else {
                //             this.photos = data;
                //         }
                //     } catch (error) {
                //         console.error("Erro ao carregar o feed de fotos:", error);
                //     }
                // },

                // async toggleLike(photo) {
                //     if (!this.user) {
                //         this.showLoginModal = true;
                //         this.authMode = 'login';
                //         return;
                //     }
                    
                //     let res = await fetch(`/photos/${photo.id}/like`, {
                //         method: 'POST',
                //         headers: {
                //             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                //             'Content-Type': 'application/json'
                //         }
                //     });
                    
                //     if (res.ok) {
                //         let data = await res.json();
                //         photo.is_liked = data.is_liked;
                //         photo.likes_count = data.likes_count;
                //     }
                // }
                
                /////////////////////////
            }
        }
    </script>
</body>
</html>