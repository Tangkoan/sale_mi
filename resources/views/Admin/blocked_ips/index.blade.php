@extends('admin.dashboard')

@section('title', 'Blocked IP Management')

@section('content')
<div class="w-full h-full px-4 py-6" x-data="blockedIpManagement()">
    
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="ri-shield-keyhole-line text-red-500"></i> Blocked IP Management
        </h1>
        <button @click="fetchIps()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
            <i class="ri-refresh-line"></i> Refresh
        </button>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 font-bold">IP Address</th>
                    <th class="px-6 py-4 font-bold">Blocked At</th>
                    <th class="px-6 py-4 font-bold">Expires At</th>
                    <th class="px-6 py-4 font-bold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="item in ips" :key="item.id">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-red-600" x-text="item.ip_address"></td>
                        <td class="px-6 py-4 text-sm text-gray-600" x-text="new Date(item.created_at).toLocaleString()"></td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-800" x-text="new Date(item.expires_at).toLocaleString()"></td>
                        <td class="px-6 py-4 text-right">
                            <button @click="unblockIp(item.ip_address)" class="bg-green-100 text-green-700 hover:bg-green-200 px-4 py-2 rounded-lg text-sm font-bold transition flex inline-flex items-center gap-1">
                                <i class="ri-lock-unlock-line"></i> Unblock
                            </button>
                        </td>
                    </tr>
                </template>
                <tr x-show="ips.length === 0">
                    <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                        <i class="ri-shield-check-line text-4xl mb-2 inline-block text-green-400"></i>
                        <p>បច្ចុប្បន្នគ្មាន IP ណាជាប់ Block នោះទេ</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
    function blockedIpManagement() {
        return {
            ips: [],
            isLoading: false,

            init() {
                this.fetchIps();
            },

            async fetchIps() {
                this.isLoading = true;
                try {
                    const response = await fetch("{{ route('admin.blocked_ips.fetch') }}");
                    const data = await response.json();
                    this.ips = data.data;
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            async unblockIp(ip) {
                if(!confirm(`តើអ្នកពិតជាចង់ដោះ Block ឲ្យ IP: ${ip} មែនទេ?`)) return;

                try {
                    const response = await fetch(`/admin/blocked-ips/unblock/${ip}`, {
                        method: 'DELETE',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        }
                    });
                    
                    const data = await response.json();
                    if(response.ok) {
                        alert(data.message);
                        this.fetchIps(); // Refresh table
                    }
                } catch(e) {
                    console.error(e);
                    alert("មានបញ្ហាក្នុងការដោះ Block!");
                }
            }
        }
    }
</script>
@endsection