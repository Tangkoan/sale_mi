@extends('admin.dashboard')

@section('title', 'Blocked IP Management')

@section('content')
<div class="w-full h-full px-4 py-6" x-data="blockedIpManagement()">

    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="ri-shield-keyhole-line text-red-500"></i> Blocked IP / Device Management
        </h1>
        <button @click="fetchIps()" class="bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 px-4 py-2 rounded-xl font-medium transition flex items-center gap-2 shadow-sm cursor-pointer">
            <i class="ri-refresh-line text-primary" :class="{'animate-spin': isLoading}"></i> Refresh
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative">
        
        <div x-show="isLoading && ips.length === 0" class="absolute inset-0 z-10 bg-white/50 backdrop-blur-sm flex items-center justify-center" style="display: none;">
            <i class="ri-loader-4-line animate-spin text-4xl text-primary"></i>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-bold">IP Address</th>
                        <th class="px-6 py-4 font-bold">Session (Device)</th>
                        <th class="px-6 py-4 font-bold">Blocked At</th>
                        <th class="px-6 py-4 font-bold">Expires At</th>
                        <th class="px-6 py-4 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="item in ips" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors group">
                            
                            <td class="px-6 py-4 font-bold text-red-600">
                                <div class="flex items-center gap-2">
                                    <i class="ri-global-line text-gray-400"></i>
                                    <span x-text="item.ip_address.split('|')[0] || item.ip_address"></span>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                                <span class="bg-gray-100 border border-gray-200 px-2 py-1 rounded text-xs" 
                                      x-text="(item.ip_address.split('|')[1] || 'Unknown').substring(0, 8) + '...'">
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-600" x-text="new Date(item.created_at).toLocaleString('en-GB')"></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800" x-text="new Date(item.expires_at).toLocaleString('en-GB')"></td>
                            
                            <td class="px-6 py-4 text-right">
                                <button @click="unblockIp(item.id)" :disabled="isProcessing === item.id" 
                                        class="bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 disabled:opacity-50 px-4 py-2 rounded-lg text-sm font-bold transition inline-flex items-center gap-1 cursor-pointer">
                                    <i class="ri-lock-unlock-line" x-show="isProcessing !== item.id"></i>
                                    <i class="ri-loader-4-line animate-spin" x-show="isProcessing === item.id"></i>
                                    <span x-text="isProcessing === item.id ? 'Processing...' : 'Unblock'"></span>
                                </button>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="ips.length === 0 && !isLoading">
                        <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                            <i class="ri-shield-check-fill text-6xl mb-4 inline-block text-green-400 opacity-90 drop-shadow-sm"></i>
                            <h3 class="text-xl font-bold text-gray-800 mb-1">គ្មានការ Block ឡើយ</h3>
                            <p class="text-sm mt-1 opacity-70">បច្ចុប្បន្នគ្មាន IP ឬឧបករណ៍ណាមួយត្រូវបានរឹតត្បិតពីប្រព័ន្ធនោះទេ។</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function blockedIpManagement() {
        return {
            ips: [],
            isLoading: false,
            isProcessing: null, // ទុក ID ដែលកំពុង Processing ពេលចុច Unblock

            init() {
                this.fetchIps();
            },

            async fetchIps() {
                this.isLoading = true;
                try {
                    // ប្រើប្រាស់ Route name ដែលត្រូវនឹង web.php របស់អ្នក
                    const response = await fetch("{{ route('admin.blocked_ips.fetch') }}");
                    const data = await response.json();
                    
                    // ដោយសារ Controller យើង Return ជា paginate() ទើបយើងហៅ data.data
                    this.ips = data.data || data; 
                } catch (error) {
                    console.error('Error fetching Blocked IPs:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            async unblockIp(id) {
                if(!confirm(`តើអ្នកពិតជាចង់ដោះ Block នេះមែនទេ?`)) return;

                this.isProcessing = id;
                try {
                    const response = await fetch(`/admin/blocked-ips/unblock/${id}`, {
                        method: 'DELETE',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        }
                    });
                    
                    const data = await response.json();
                    
                    if(response.ok) {
                        // ប្រសិនបើអ្នកមាន CustomEvent សម្រាប់ Notification (ផ្អែកលើ user_list.blade.php) 
                        if(typeof window.dispatchEvent === 'function') {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        } else {
                            alert(data.message);
                        }
                        this.fetchIps(); // Refresh ទិន្នន័យថ្មី
                    } else {
                        alert(data.message || "មានបញ្ហាក្នុងការដោះ Block!");
                    }
                } catch(e) {
                    console.error(e);
                    alert("មានបញ្ហាក្នុងការតភ្ជាប់ទៅកាន់ Server!");
                } finally {
                    this.isProcessing = null;
                }
            }
        }
    }
</script>
@endsection