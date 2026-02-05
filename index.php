<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jupita Admin - User Management & Notes</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    
    <link rel="stylesheet" href="style.css">

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        // Extending Tailwind's default theme to match our brand colors
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        primary: { DEFAULT: '#6366f1', hover: '#4f46e5', light: '#e0e7ff' }, // Indigo Brand Color
                        surface: '#ffffff',
                        subtle: '#94a3b8', // Slate-400 for secondary text
                    },
                    boxShadow: {
                        'floating': '0 20px 40px -10px rgba(0,0,0,0.1)'
                    }
                }
            }
        }
    </script>
</head>
<body class="text-slate-800 antialiased selection:bg-primary selection:text-white">

<div id="app" class="h-screen flex overflow-hidden">

    <div v-if="currentView === 'login'" class="w-full h-full flex items-center justify-center p-4 bg-slate-50">
        <div class="glass-panel w-full max-w-[420px] rounded-3xl shadow-floating p-8 md:p-12 relative overflow-hidden">
            
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary/20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10">
                <div class="mb-8 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-primary text-white mb-4 shadow-lg shadow-primary/30">
                        <span class="material-symbols-rounded text-3xl">token</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Welcome Back</h1>
                    <p class="text-subtle mt-2">Sign in to manage & write</p>
                </div>

                <form @submit.prevent="handleLogin" class="space-y-5">
                    <div v-if="authError" class="p-3 rounded-xl bg-red-50 text-red-600 text-sm text-center font-bold animate-pulse">{{ authError }}</div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-subtle">Email</label>
                        <input v-model="loginForm.email" type="email" class="w-full rounded-xl border-slate-200 bg-white/50 px-4 py-3 focus:border-primary focus:ring-primary/20 transition-all" placeholder="name@company.com" required>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-subtle">Password</label>
                        <div class="relative">
                            <input v-model="loginForm.password" :type="showLoginPass ? 'text' : 'password'" class="w-full rounded-xl border-slate-200 bg-white/50 px-4 py-3 pr-10 focus:border-primary focus:ring-primary/20 transition-all" placeholder="••••••••" required>
                            <button type="button" @click="showLoginPass = !showLoginPass" class="absolute right-3 top-3 text-slate-400 hover:text-primary transition-colors">
                                <span class="material-symbols-rounded text-xl">{{ showLoginPass ? 'visibility' : 'visibility_off' }}</span>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full py-3.5 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold shadow-lg shadow-primary/30 transition-all transform active:scale-95">Sign In</button>
                </form>
                
                <div class="mt-6 text-center text-sm text-subtle">
                    New here? <button @click="currentView='register'" class="text-primary font-bold hover:underline">Create Account</button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="currentView === 'register'" class="w-full h-full flex items-center justify-center p-4 bg-slate-50">
        <div class="glass-panel w-full max-w-[420px] rounded-3xl shadow-floating p-8 relative">
             <div class="text-center mb-6">
                <h1 class="text-2xl font-bold">Join Us</h1>
                <p class="text-subtle">Create your admin account</p>
            </div>
            
            <form @submit.prevent="handleRegister" class="space-y-4">
                 <div v-if="successMessage" class="p-3 rounded-xl bg-green-50 text-green-700 text-sm text-center font-medium">{{ successMessage }}</div>
                 <div v-if="authError" class="p-3 rounded-xl bg-red-50 text-red-600 text-sm text-center font-medium">{{ authError }}</div>
                
                <div class="flex justify-center mb-4">
                    <div class="relative group cursor-pointer" @click="$refs.regFile.click()">
                        <div class="w-24 h-24 rounded-full bg-slate-100 flex items-center justify-center border-2 border-dashed border-slate-300 overflow-hidden shadow-inner hover:border-primary transition-colors">
                            <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover">
                            <div v-else class="text-center">
                                <span class="material-symbols-rounded text-slate-400 block text-3xl">add_a_photo</span>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Upload</span>
                            </div>
                        </div>
                        <div class="absolute bottom-0 right-0 bg-primary text-white p-1.5 rounded-full shadow-md">
                            <span class="material-symbols-rounded text-[16px]">edit</span>
                        </div>
                    </div>
                    <input type="file" ref="regFile" class="hidden" @change="handleFileUpload($event, 'register')" accept="image/*">
                </div>

                <input v-model="registerForm.name" type="text" class="w-full rounded-xl border-slate-200 bg-white/50 px-4 py-3" placeholder="Full Name" required>
                <input v-model="registerForm.email" type="email" class="w-full rounded-xl border-slate-200 bg-white/50 px-4 py-3" placeholder="Email Address" required>
                
                <div class="relative">
                    <input v-model="registerForm.password" :type="showRegPass ? 'text' : 'password'" class="w-full rounded-xl border-slate-200 bg-white/50 px-4 py-3 pr-10" placeholder="Password" required>
                    <button type="button" @click="showRegPass = !showRegPass" class="absolute right-3 top-3 text-slate-400 hover:text-primary transition-colors">
                        <span class="material-symbols-rounded text-xl">{{ showRegPass ? 'visibility' : 'visibility_off' }}</span>
                    </button>
                </div>
                
                <button type="submit" class="w-full py-3.5 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold shadow-lg shadow-primary/30 transition-all">Create Account</button>
            </form>
            
             <div class="mt-6 text-center text-sm text-subtle">
                Already have an account? <button @click="currentView='login'" class="text-primary font-bold hover:underline">Sign In</button>
            </div>
        </div>
    </div>

    <div v-if="currentView === 'dashboard' || currentView === 'details' || currentView === 'notes'" class="flex w-full h-full bg-white/80 backdrop-blur-3xl rounded-none md:rounded-3xl md:m-4 md:h-[calc(100vh-2rem)] shadow-2xl overflow-hidden border border-white/40">
        
        <aside class="w-72 hidden md:flex flex-col glass-sidebar z-20">
            <div class="p-8 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary to-purple-500 flex items-center justify-center text-white shadow-lg">
                        <span class="material-symbols-rounded">sticky_note_2</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-lg leading-tight">Jupita</h2>
                        <p class="text-xs text-subtle uppercase tracking-wider">Notes {{ currentUser?.role }}</p>
                    </div>
                </div>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="#" @click="currentView='dashboard'" :class="currentView === 'dashboard' ? 'bg-primary text-white shadow-lg shadow-primary/25' : 'text-slate-500 hover:bg-slate-100'" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all font-medium">
                    <span class="material-symbols-rounded">group</span> Users
                </a>
                <a href="#" @click.prevent="showNotesView" :class="currentView === 'notes' ? 'bg-primary text-white shadow-lg shadow-primary/25' : 'text-slate-500 hover:bg-slate-100'" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl transition-all font-medium">
                    <span class="material-symbols-rounded">description</span> Shared Notes
                </a>
            </nav>
            
            <div class="p-4 border-t border-slate-100">
                <button @click="logout" class="flex items-center gap-3 w-full p-3 rounded-2xl hover:bg-red-50 text-slate-600 hover:text-red-600 transition-all">
                    <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden">
                        <img v-if="currentUser?.avatar" :src="currentUser.avatar" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full flex items-center justify-center bg-primary text-white font-bold">{{ currentUser?.name?.charAt(0) }}</div>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold">{{ currentUser?.name }}</p>
                        <p class="text-xs text-subtle uppercase font-bold text-primary">{{ currentUser?.role || 'User' }}</p>
                    </div>
                </button>
            </div>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 bg-white/40 relative">
            
            <div v-if="currentView === 'dashboard'" class="flex-1 flex flex-col h-full relative">
                <header class="h-20 px-8 flex items-center justify-between border-b border-slate-200/60 bg-white/50 backdrop-blur-sm gap-4">
                    <h1 class="text-2xl font-bold whitespace-nowrap">User Database</h1>
                    
                    <div class="flex-1 max-w-md relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-rounded text-slate-400 text-lg">search</span>
                        </div>
                        <input v-model="searchQuery" type="text" class="block w-full pl-10 pr-3 py-2.5 rounded-xl border-slate-200 bg-white/50 text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm shadow-sm" placeholder="Search users by name or email...">
                    </div>

                    <button v-if="currentUser?.role === 'admin'" @click="openUserModal" class="bg-primary text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all flex items-center gap-2 whitespace-nowrap">
                        <span class="material-symbols-rounded text-lg">add</span> New User
                    </button>
                </header>

                <div class="flex-1 overflow-auto p-8 pb-24">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/50">
                                    <th class="p-5 w-10"><input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="rounded border-slate-300 text-primary focus:ring-primary/50 cursor-pointer"></th>
                                    <th class="p-5 text-xs font-bold uppercase text-subtle tracking-wider">User</th>
                                    <th class="p-5 text-xs font-bold uppercase text-subtle tracking-wider">Contact</th>
                                    <th class="p-5 text-xs font-bold uppercase text-subtle tracking-wider">Status</th>
                                    <th class="p-5 text-xs font-bold uppercase text-subtle tracking-wider">Joined</th>
                                    <th class="p-5 text-xs font-bold uppercase text-subtle tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50/80 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(user.id)}">
                                    <td class="p-5"><input type="checkbox" :value="user.id" v-model="selectedIds" class="rounded border-slate-300 text-primary focus:ring-primary/50 cursor-pointer"></td>
                                    <td class="p-5">
                                        <div class="flex items-center gap-4">
                                            <div class="relative w-10 h-10 rounded-full bg-slate-100 overflow-hidden border border-slate-200">
                                                <img v-if="user.avatar" :src="user.avatar" class="w-full h-full object-cover">
                                                <div v-else class="w-full h-full flex items-center justify-center text-slate-400 font-bold">{{ user.name?.charAt(0) }}</div>
                                                <div v-if="user.role === 'admin'" class="absolute -bottom-1 -right-1 bg-yellow-400 text-[8px] font-bold px-1 rounded shadow text-white">ADM</div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800">{{ user.name }}</div>
                                                <div class="text-xs text-subtle">ID: #{{ user.id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-5 text-sm text-slate-600">{{ user.email }}</td>
                                    <td class="p-5">
                                        <button :disabled="currentUser?.role !== 'admin'" @click="toggleStatus(user)" 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold transition-colors shadow-sm"
                                            :class="[user.status === 'banned' ? 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200' : 'bg-green-50 text-green-700 hover:bg-green-100 border border-green-200', currentUser?.role !== 'admin' ? 'opacity-60 cursor-not-allowed' : '']">
                                            <span class="w-2 h-2 rounded-full mr-2" :class="user.status === 'banned' ? 'bg-red-500' : 'bg-green-500'"></span>
                                            {{ user.status ? user.status.toUpperCase() : 'ACTIVE' }}
                                        </button>
                                    </td>
                                    <td class="p-5 text-sm text-slate-500 font-mono">{{ formatDate(user.created_at) }}</td>
                                    <td class="p-5 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button @click="viewUserDetails(user)" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-primary hover:bg-primary-light transition-colors" title="View Details">
                                                <span class="material-symbols-rounded text-lg">visibility</span>
                                            </button>
                                            
                                            <button v-if="currentUser?.role === 'admin'" @click="editUser(user)" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-primary hover:bg-slate-50 transition-colors" title="Edit User">
                                                <span class="material-symbols-rounded text-lg">edit</span>
                                            </button>
                                            <button v-if="currentUser?.role === 'admin'" @click="deleteUser(user.id)" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                                                <span class="material-symbols-rounded text-lg">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="filteredUsers.length === 0" class="p-12 text-center text-slate-400">
                            <span class="material-symbols-rounded text-4xl mb-2">search_off</span>
                            <p>No users found matching "{{ searchQuery }}"</p>
                        </div>
                    </div>
                </div>

                <div v-if="selectedIds.length > 0 && currentUser?.role === 'admin'" class="absolute bottom-6 left-1/2 transform -translate-x-1/2 bg-slate-900 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-6 z-50 animate-bounce-in border border-slate-700">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <div class="bg-primary text-white w-6 h-6 rounded-full flex items-center justify-center text-xs">{{ selectedIds.length }}</div>
                        <span>Selected</span>
                    </div>
                    <div class="h-4 w-px bg-white/20"></div>
                    <button @click="batchDelete" class="flex items-center gap-2 text-sm font-bold text-red-400 hover:text-red-300 transition-colors">
                        <span class="material-symbols-rounded text-lg">delete</span> Delete Selected
                    </button>
                    <button @click="selectedIds = []" class="text-xs text-slate-400 hover:text-white transition-colors">Cancel</button>
                </div>
            </div>

            <div v-if="currentView === 'notes'" class="flex-1 flex flex-col h-full relative">
                <header class="h-20 px-8 flex items-center justify-between border-b border-slate-200/60 bg-white/50 backdrop-blur-sm">
                    <h1 class="text-2xl font-bold">Shared Notes</h1>
                    <button @click="openNoteModal" class="bg-primary text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all flex items-center gap-2 whitespace-nowrap">
                        <span class="material-symbols-rounded text-lg">edit_square</span> Write Note
                    </button>
                </header>

                <div class="flex-1 overflow-auto p-8">
                    <div v-if="notes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="note in notes" :key="note.id" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-glass transition-all group flex flex-col h-full">
                            <div class="flex items-center gap-3 mb-4">
                                <img :src="note.author_avatar || 'https://ui-avatars.com/api/?name=' + note.author_name" class="w-8 h-8 rounded-full bg-slate-100 object-cover border border-slate-200">
                                <div>
                                    <p class="text-xs font-bold text-slate-900">{{ note.author_name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ formatDate(note.created_at) }}</p>
                                </div>
                            </div>
                            <h3 class="font-bold text-slate-800 mb-2 text-lg">{{ note.title }}</h3>
                            <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed flex-1">{{ note.content }}</p>
                            
                            <div class="mt-4 pt-4 border-t border-slate-50 flex justify-between items-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="viewNote(note)" class="text-xs font-bold text-primary hover:underline">Read Full Note</button>
                                <div class="flex gap-2">
                                    <button v-if="currentUser?.id === note.user_id" @click="editNote(note)" class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-slate-50 transition-colors" title="Edit">
                                        <span class="material-symbols-rounded text-base">edit</span>
                                    </button>
                                    <button v-if="currentUser?.id === note.user_id || currentUser?.role === 'admin'" @click="deleteNote(note)" class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors" title="Delete">
                                        <span class="material-symbols-rounded text-base">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex-1 flex flex-col items-center justify-center text-center p-20 opacity-60">
                        <span class="material-symbols-rounded text-6xl text-slate-300 mb-4">description</span>
                        <p class="text-slate-500 font-medium">No notes have been shared yet.</p>
                    </div>
                </div>
            </div>

            <div v-if="currentView === 'details'" class="flex-1 overflow-auto p-8">
                <button @click="currentView='dashboard'" class="mb-6 flex items-center gap-2 text-sm font-bold text-subtle hover:text-primary transition-colors">
                    <span class="material-symbols-rounded">arrow_back</span> Back to Dashboard
                </button>

                <div class="glass-panel rounded-3xl p-8 relative overflow-hidden bg-white">
                    <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-r from-primary to-purple-500 opacity-90"></div>
                    <div class="relative mt-10 flex flex-col md:flex-row gap-6 items-end">
                        <div class="w-32 h-32 rounded-3xl border-4 border-white shadow-xl overflow-hidden bg-white">
                            <img v-if="selectedUser?.avatar" :src="selectedUser.avatar" class="w-full h-full object-cover">
                            <div v-else class="w-full h-full flex items-center justify-center bg-slate-100 text-4xl text-slate-300 font-bold">{{ selectedUser?.name?.charAt(0) }}</div>
                        </div>
                        <div class="mb-2">
                            <h1 class="text-3xl font-bold text-slate-800">{{ selectedUser?.name }}</h1>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border" 
                                      :class="selectedUser?.status === 'banned' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-green-600 border-green-100'">
                                    {{ selectedUser?.status || 'Active' }}
                                </span>
                                <span class="text-slate-500 text-sm">User ID: #{{ selectedUser?.id }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50/50">
                            <h3 class="text-xs font-bold uppercase text-subtle mb-1">Contact Email</h3>
                            <p class="text-lg font-medium text-slate-800">{{ selectedUser?.email }}</p>
                        </div>
                        <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50/50">
                            <h3 class="text-xs font-bold uppercase text-subtle mb-1">Account Created</h3>
                            <p class="text-lg font-medium text-slate-800">{{ formatDate(selectedUser?.created_at) }}</p>
                        </div>
                    </div>

                    <div class="mt-10 pt-8 border-t border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                            <span class="material-symbols-rounded text-primary">description</span> Published Notes
                        </h3>
                        <div v-if="notes.filter(n => n.user_id === selectedUser.id).length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="note in notes.filter(n => n.user_id === selectedUser.id)" :key="note.id" class="p-5 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:shadow-sm transition-all">
                                <h4 class="font-bold text-slate-800 mb-1">{{ note.title }}</h4>
                                <p class="text-xs text-slate-400 mb-3">{{ formatDate(note.created_at) }}</p>
                                <p class="text-sm text-slate-600 line-clamp-3">{{ note.content }}</p>
                            </div>
                        </div>
                        <div v-else class="text-center py-10 rounded-2xl border-2 border-dashed border-slate-100">
                            <p class="text-slate-400 text-sm">This user hasn't published any notes yet.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showUserModal" class="absolute inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 transform scale-100 transition-all">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold">{{ isUserEditing ? 'Edit User' : 'New User' }}</h2>
                        <button @click="showUserModal=false" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-rounded">close</span></button>
                    </div>
                    <form @submit.prevent="saveUser" class="space-y-4">
                        <div class="flex items-center gap-4 mb-2">
                            <div class="w-16 h-16 rounded-full bg-slate-100 overflow-hidden border border-slate-200">
                                <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover">
                            </div>
                            <button type="button" @click="$refs.modalFile.click()" class="text-sm font-bold text-primary hover:underline">Upload Photo</button>
                            <input type="file" ref="modalFile" class="hidden" @change="handleFileUpload($event, 'modal')" accept="image/*">
                        </div>
                        <input v-model="form.name" type="text" class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm bg-slate-50" placeholder="Full Name" required>
                        <input v-model="form.email" type="email" class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm bg-slate-50" placeholder="Email Address" required>
                        
                        <div class="relative">
                            <p v-if="isUserEditing" class="text-[10px] text-slate-400 mb-1 ml-1 uppercase font-bold">Leave blank to keep current password</p>
                            <input v-model="form.password" :type="showModalPass ? 'text' : 'password'" class="w-full rounded-xl border-slate-200 px-4 py-3 pr-10 text-sm bg-slate-50" :placeholder="isUserEditing ? 'New Password (Optional)' : 'Password'" :required="!isUserEditing">
                            <button type="button" @click="showModalPass = !showModalPass" class="absolute right-3 top-3 text-slate-400 hover:text-primary transition-colors" :class="{'top-8': isUserEditing}">
                                <span class="material-symbols-rounded text-xl">{{ showModalPass ? 'visibility' : 'visibility_off' }}</span>
                            </button>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button @click="showUserModal=false" type="button" class="flex-1 py-3 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
                            <button type="submit" class="flex-1 py-3 rounded-xl bg-primary text-white font-bold hover:bg-primary-hover shadow-lg shadow-primary/25">
                                {{ isUserEditing ? 'Update User' : 'Create User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showNoteModal" class="absolute inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 transform scale-100 transition-all">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold">{{ isEditing ? 'Edit Note' : 'Write a Note' }}</h2>
                        <button @click="showNoteModal=false" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-rounded">close</span></button>
                    </div>
                    <form @submit.prevent="saveNote" class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-subtle uppercase tracking-wider mb-1 block">Title</label>
                            <input v-model="noteForm.title" type="text" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:bg-white transition-all" placeholder="What's on your mind?" required>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-subtle uppercase tracking-wider mb-1 block">Content</label>
                            <textarea v-model="noteForm.content" rows="5" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:bg-white transition-all" placeholder="Start typing here..." required></textarea>
                        </div>
                        <div class="pt-4 flex gap-3">
                            <button @click="showNoteModal=false" type="button" class="flex-1 py-3 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
                            <button type="submit" class="flex-1 py-3 rounded-xl bg-primary text-white font-bold hover:bg-primary-hover shadow-lg shadow-primary/25">
                                {{ isEditing ? 'Update Note' : 'Publish Note' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showViewNoteModal" class="absolute inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl p-8 transform scale-100 transition-all max-h-[85vh] flex flex-col">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center gap-4">
                            <img :src="selectedNote?.author_avatar || 'https://ui-avatars.com/api/?name=' + selectedNote?.author_name" class="w-12 h-12 rounded-full border border-slate-200">
                            <div>
                                <h2 class="text-xl font-bold text-slate-800">{{ selectedNote?.title }}</h2>
                                <p class="text-xs text-slate-500">Posted by <span class="font-bold text-primary">{{ selectedNote?.author_name }}</span> on {{ formatDate(selectedNote?.created_at) }}</p>
                            </div>
                        </div>
                        <button @click="showViewNoteModal=false" class="text-slate-400 hover:text-slate-600 bg-slate-100 rounded-full p-2"><span class="material-symbols-rounded block">close</span></button>
                    </div>
                    <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                        <p class="text-slate-600 leading-relaxed whitespace-pre-wrap text-base">{{ selectedNote?.content }}</p>
                    </div>
                    <div class="pt-6 mt-6 border-t border-slate-100 text-right">
                        <button @click="showViewNoteModal=false" class="px-6 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold hover:bg-slate-200 transition-colors">Close</button>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="app.js"></script>
</body>
</html>